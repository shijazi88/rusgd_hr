<?php

namespace App\Services;

use App\DTOs\AttendanceSummaryDTO;
use App\Enums\AttendanceStatus;
use App\Enums\LeaveStatus;
use App\Exceptions\AttendanceAlreadyRecordedException;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Repositories\Contracts\IAttendanceRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

class AttendanceService
{
    public function __construct(
        private readonly IAttendanceRepository $attendanceRepo,
        private readonly ShiftService          $shiftService,
    ) {}

    public function getAllPaginated(array $filters, int $perPage): LengthAwarePaginator
    {
        return $this->attendanceRepo->findAllPaginated($filters, $perPage);
    }

    public function getForDate(Carbon $date, array $filters): Collection
    {
        return $this->attendanceRepo->findForDate($date, $filters);
    }

    public function recordCheckIn(Employee $employee, ?Carbon $time = null): Attendance
    {
        $time = $time ?? now();
        $date = $time->toDateString();

        $existing = $this->attendanceRepo->findByEmployeeAndDate($employee->id, $date);

        if ($existing && $existing->check_in) {
            throw new AttendanceAlreadyRecordedException('تم تسجيل الحضور لهذا الموظف مسبقاً اليوم.');
        }

        $shift        = $this->shiftService->getActiveShiftForEmployee($employee, $time);
        $lateMinutes  = $this->calculateLateMinutes($shift, $time);
        $status       = $lateMinutes > 0 ? AttendanceStatus::Late : AttendanceStatus::Present;

        $data = [
            'employee_id'  => $employee->id,
            'shift_id'     => $shift?->id,
            'date'         => $date,
            'check_in'     => $time->format('H:i'),
            'late_minutes' => $lateMinutes,
            'status'       => $status->value,
        ];

        if ($existing) {
            return $this->attendanceRepo->update($existing, $data);
        }

        return $this->attendanceRepo->create($data);
    }

    public function recordCheckOut(Attendance $attendance, ?Carbon $time = null): Attendance
    {
        $time      = $time ?? now();
        $checkOut  = $time->format('H:i');
        $workHours = $this->calculateWorkHours($attendance->check_in, $checkOut);

        return $this->attendanceRepo->update($attendance, [
            'check_out'  => $checkOut,
            'work_hours' => $workHours,
        ]);
    }

    public function bulkCheckIn(array $employeeIds, ?Carbon $time = null): array
    {
        $time    = $time ?? now();
        $results = [];

        foreach ($employeeIds as $employeeId) {
            $employee = Employee::find($employeeId);

            if (!$employee) {
                $results[] = ['employee_id' => $employeeId, 'success' => false, 'message' => 'الموظف غير موجود.'];
                continue;
            }

            try {
                $record    = $this->recordCheckIn($employee, $time);
                $results[] = ['employee_id' => $employeeId, 'success' => true, 'attendance_id' => $record->id];
            } catch (AttendanceAlreadyRecordedException $e) {
                $results[] = ['employee_id' => $employeeId, 'success' => false, 'message' => $e->getMessage()];
            }
        }

        return $results;
    }

    public function manualRecord(Employee $employee, array $data): Attendance
    {
        $existing = $this->attendanceRepo->findByEmployeeAndDate($employee->id, $data['date']);

        if ($existing) {
            return $this->attendanceRepo->update($existing, $data);
        }

        $shift = $this->shiftService->getActiveShiftForEmployee(
            $employee,
            Carbon::parse($data['date'])
        );

        return $this->attendanceRepo->create(array_merge([
            'employee_id' => $employee->id,
            'shift_id'    => $shift?->id,
        ], $data));
    }

    public function updateRecord(Attendance $attendance, array $data): Attendance
    {
        if (isset($data['check_in'], $data['check_out'])) {
            $data['work_hours'] = $this->calculateWorkHours($data['check_in'], $data['check_out']);
        } elseif (isset($data['check_out']) && $attendance->check_in) {
            $data['work_hours'] = $this->calculateWorkHours($attendance->check_in, $data['check_out']);
        }

        return $this->attendanceRepo->update($attendance, $data);
    }

    public function getMonthlySummary(Employee $employee, int $month, int $year): AttendanceSummaryDTO
    {
        $records = $this->attendanceRepo->findForEmployee($employee->id, $month, $year);

        $presentDays  = $records->where('status', AttendanceStatus::Present)->count();
        $lateDays     = $records->where('status', AttendanceStatus::Late)->count();
        $absentDays   = $records->where('status', AttendanceStatus::Absent)->count();
        $onLeaveDays  = $records->where('status', AttendanceStatus::OnLeave)->count();
        $totalHours   = round((float) $records->sum('work_hours'), 2);

        $workedDays      = $presentDays + $lateDays;
        $totalRecorded   = $records->count();
        $attendanceRate  = $totalRecorded > 0 ? round(($workedDays / $totalRecorded) * 100, 1) : 0.0;

        return new AttendanceSummaryDTO(
            presentDays:    $presentDays,
            absentDays:     $absentDays,
            lateDays:       $lateDays,
            onLeaveDays:    $onLeaveDays,
            totalHours:     $totalHours,
            attendanceRate: $attendanceRate,
        );
    }

    public function autoCreateAbsentRecords(Carbon $date): int
    {
        $employees = Employee::active()->get();
        $created   = 0;

        foreach ($employees as $employee) {
            $existing = $this->attendanceRepo->findByEmployeeAndDate($employee->id, $date->toDateString());

            if ($existing) {
                continue;
            }

            // Check for an approved leave covering this date
            $onLeave = LeaveRequest::where('employee_id', $employee->id)
                ->where('status', LeaveStatus::Approved->value)
                ->whereDate('from_date', '<=', $date)
                ->whereDate('to_date', '>=', $date)
                ->exists();

            $this->attendanceRepo->create([
                'employee_id'  => $employee->id,
                'date'         => $date->toDateString(),
                'status'       => $onLeave ? AttendanceStatus::OnLeave->value : AttendanceStatus::Absent->value,
                'late_minutes' => 0,
            ]);

            $created++;
        }

        return $created;
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function calculateLateMinutes(?object $shift, Carbon $checkIn): int
    {
        if (!$shift) {
            return 0;
        }

        $deadline = Carbon::parse($checkIn->toDateString() . ' ' . $shift->start_time)
            ->addMinutes($shift->grace_minutes);

        return max(0, (int) $deadline->diffInMinutes($checkIn, false));
    }

    private function calculateWorkHours(string $checkIn, string $checkOut): float
    {
        $in  = Carbon::parse($checkIn);
        $out = Carbon::parse($checkOut);

        // Handle overnight shifts (e.g. 22:00 → 06:00)
        if ($out->lt($in)) {
            $out->addDay();
        }

        return round($in->diffInMinutes($out) / 60, 2);
    }
}
