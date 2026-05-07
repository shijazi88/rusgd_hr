<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Attendance\StoreAttendanceRequest;
use App\Http\Requests\Attendance\UpdateAttendanceRequest;
use App\Http\Resources\AttendanceResource;
use App\Models\Attendance;
use App\Models\Employee;
use App\Services\AttendanceService;
use App\Support\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AttendanceController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly AttendanceService $attendanceService) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Attendance::class);

        $perPage = max(1, min((int) $request->input('per_page', 20), 100));

        // If a single date is requested, return non-paginated collection
        if ($request->has('date') && !$request->has('paginate')) {
            $records = $this->attendanceService->getForDate(
                Carbon::parse($request->input('date')),
                $request->only(['status', 'department_id'])
            );

            return $this->success(AttendanceResource::collection($records));
        }

        $records = $this->attendanceService->getAllPaginated(
            $request->only(['employee_id', 'status', 'date', 'department_id']),
            $perPage
        );

        return $this->paginated(AttendanceResource::collection($records));
    }

    public function show(int $id): JsonResponse
    {
        $attendance = Attendance::with(['employee.department', 'shift'])->findOrFail($id);
        $this->authorize('view', $attendance);

        return $this->success(AttendanceResource::make($attendance));
    }

    public function store(StoreAttendanceRequest $request): JsonResponse
    {
        $this->authorize('create', Attendance::class);

        $employee   = Employee::findOrFail($request->employee_id);
        $attendance = $this->attendanceService->manualRecord($employee, $request->validated());

        return $this->created(
            AttendanceResource::make($attendance->load(['employee.department', 'shift'])),
            'تم تسجيل الحضور بنجاح.'
        );
    }

    public function update(UpdateAttendanceRequest $request, int $id): JsonResponse
    {
        $attendance = Attendance::findOrFail($id);
        $this->authorize('update', $attendance);

        $updated = $this->attendanceService->updateRecord($attendance, $request->validated());

        return $this->success(
            AttendanceResource::make($updated),
            'تم تحديث سجل الحضور بنجاح.'
        );
    }

    public function bulkCheckIn(Request $request): JsonResponse
    {
        $this->authorize('create', Attendance::class);

        $request->validate([
            'employee_ids'   => 'required|array|min:1',
            'employee_ids.*' => 'integer|exists:employees,id',
            'time'           => 'sometimes|date_format:H:i',
        ]);

        $time    = $request->has('time') ? Carbon::parse(today()->toDateString() . ' ' . $request->time) : null;
        $results = $this->attendanceService->bulkCheckIn($request->employee_ids, $time);

        return $this->success($results, 'تم معالجة تسجيل الحضور الجماعي.');
    }

    public function monthlySummary(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Attendance::class);

        $request->validate([
            'employee_id' => 'required|integer|exists:employees,id',
            'month'       => 'required|integer|between:1,12',
            'year'        => 'required|integer|min:2020',
        ]);

        $employee = Employee::findOrFail($request->employee_id);
        $summary  = $this->attendanceService->getMonthlySummary(
            $employee,
            (int) $request->month,
            (int) $request->year
        );

        return $this->success([
            'employee'       => \App\Http\Resources\EmployeeResource::make($employee),
            'month'          => $request->month,
            'year'           => $request->year,
            'present_days'   => $summary->presentDays,
            'absent_days'    => $summary->absentDays,
            'late_days'      => $summary->lateDays,
            'on_leave_days'  => $summary->onLeaveDays,
            'total_hours'    => $summary->totalHours,
            'attendance_rate' => $summary->attendanceRate,
        ]);
    }
}
