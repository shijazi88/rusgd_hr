<?php

namespace App\Repositories\Eloquent;

use App\Models\Employee;
use App\Repositories\Contracts\IEmployeeRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class EloquentEmployeeRepository implements IEmployeeRepository
{
    private const WITH = ['department', 'jobTitle', 'contractType', 'manager.jobTitle', 'user'];

    public function findById(int $id): ?Employee
    {
        return Employee::with(self::WITH)->find($id);
    }

    public function findAllPaginated(array $filters, int $perPage = 20): LengthAwarePaginator
    {
        $query = Employee::with(self::WITH)->orderBy('name');

        if (!empty($filters['search'])) {
            $query->search($filters['search']);
        }

        if (!empty($filters['department_id'])) {
            $query->byDepartment((int) $filters['department_id']);
        }

        if (!empty($filters['status'])) {
            $query->byStatus($filters['status']);
        }

        if (!empty($filters['contract_type_id'])) {
            $query->where('contract_type_id', (int) $filters['contract_type_id']);
        }

        return $query->paginate($perPage);
    }

    public function getOrgChartRoots(): Collection
    {
        return Employee::with([
                'jobTitle',
                'department',
                'directReports.jobTitle',
                'directReports.department',
                'directReports.directReports.jobTitle',
                'directReports.directReports.department',
                'directReports.directReports.directReports.jobTitle',
                'directReports.directReports.directReports.department',
            ])
            ->whereNull('manager_id')
            ->orderBy('name')
            ->get();
    }

    public function create(array $data): Employee
    {
        $employee = Employee::create($data);
        return $employee->load(self::WITH);
    }

    public function update(Employee $employee, array $data): Employee
    {
        $employee->update($data);
        return $employee->fresh(self::WITH);
    }

    public function softDelete(Employee $employee): bool
    {
        return $employee->delete();
    }
}
