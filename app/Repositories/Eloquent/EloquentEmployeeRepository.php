<?php

namespace App\Repositories\Eloquent;

use App\Models\Employee;
use App\Repositories\Contracts\IEmployeeRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class EloquentEmployeeRepository implements IEmployeeRepository
{
    public function findById(int $id): ?Employee
    {
        return Employee::with(['department', 'manager', 'user'])->find($id);
    }

    public function findAllPaginated(array $filters, int $perPage = 20): LengthAwarePaginator
    {
        $query = Employee::with(['department', 'manager'])
            ->orderBy('name');

        if (!empty($filters['search'])) {
            $query->search($filters['search']);
        }

        if (!empty($filters['department_id'])) {
            $query->byDepartment((int) $filters['department_id']);
        }

        if (!empty($filters['status'])) {
            $query->byStatus($filters['status']);
        }

        return $query->paginate($perPage);
    }

    public function getOrgChartRoots(): Collection
    {
        return Employee::with(['directReports.directReports.directReports', 'department'])
            ->whereNull('manager_id')
            ->orderBy('name')
            ->get();
    }

    public function create(array $data): Employee
    {
        return Employee::create($data);
    }

    public function update(Employee $employee, array $data): Employee
    {
        $employee->update($data);
        return $employee->fresh(['department', 'manager']);
    }

    public function softDelete(Employee $employee): bool
    {
        return $employee->delete();
    }
}
