<?php

namespace App\Repositories\Eloquent;

use App\Models\Attendance;
use App\Repositories\Contracts\IAttendanceRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

class EloquentAttendanceRepository implements IAttendanceRepository
{
    private const WITH = ['employee.department', 'shift'];

    public function findById(int $id): ?Attendance
    {
        return Attendance::with(self::WITH)->find($id);
    }

    public function findAllPaginated(array $filters, int $perPage): LengthAwarePaginator
    {
        $query = Attendance::with(self::WITH)->orderByDesc('date');

        if (!empty($filters['employee_id'])) {
            $query->byEmployee((int) $filters['employee_id']);
        }

        if (!empty($filters['status'])) {
            $query->byStatus($filters['status']);
        }

        if (!empty($filters['date'])) {
            $query->whereDate('date', $filters['date']);
        }

        if (!empty($filters['department_id'])) {
            $query->whereHas('employee', fn ($q) => $q->where('department_id', $filters['department_id']));
        }

        return $query->paginate($perPage);
    }

    public function findForDate(Carbon $date, array $filters): Collection
    {
        $query = Attendance::with(self::WITH)->forDate($date);

        if (!empty($filters['status'])) {
            $query->byStatus($filters['status']);
        }

        if (!empty($filters['department_id'])) {
            $query->whereHas('employee', fn ($q) => $q->where('department_id', $filters['department_id']));
        }

        return $query->get();
    }

    public function findForEmployee(int $employeeId, int $month, int $year): Collection
    {
        return Attendance::with(['shift'])
            ->byEmployee($employeeId)
            ->forMonth($month, $year)
            ->orderBy('date')
            ->get();
    }

    public function findByEmployeeAndDate(int $employeeId, string $date): ?Attendance
    {
        return Attendance::where('employee_id', $employeeId)
            ->whereDate('date', $date)
            ->first();
    }

    public function create(array $data): Attendance
    {
        return Attendance::create($data);
    }

    public function update(Attendance $attendance, array $data): Attendance
    {
        $attendance->update($data);
        return $attendance->fresh(self::WITH);
    }
}
