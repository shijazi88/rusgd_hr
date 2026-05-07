<?php

namespace App\Services;

use App\DTOs\CreateEmployeeDTO;
use App\DTOs\UpdateEmployeeDTO;
use App\Models\Employee;
use App\Repositories\Contracts\IEmployeeRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EmployeeService
{
    public function __construct(
        private readonly IEmployeeRepository $employeeRepo,
    ) {}

    public function getAll(array $filters, int $perPage = 20): LengthAwarePaginator
    {
        return $this->employeeRepo->findAllPaginated($filters, $perPage);
    }

    public function getById(int $id): Employee
    {
        $employee = $this->employeeRepo->findById($id);

        if (!$employee) {
            abort(404, 'الموظف غير موجود.');
        }

        return $employee;
    }

    public function create(CreateEmployeeDTO $dto): Employee
    {
        return $this->employeeRepo->create($dto->toArray());
    }

    public function update(Employee $employee, UpdateEmployeeDTO $dto): Employee
    {
        return $this->employeeRepo->update($employee, $dto->toArray());
    }

    public function archive(Employee $employee): void
    {
        $this->employeeRepo->update($employee, ['status' => 'terminated']);
        $this->employeeRepo->softDelete($employee);
    }

    public function getOrgChart(): array
    {
        $roots = $this->employeeRepo->getOrgChartRoots();
        return $roots->map(fn (Employee $emp) => $this->buildNode($emp))->toArray();
    }

    private function buildNode(Employee $employee): array
    {
        return [
            'id'          => $employee->id,
            'name'        => $employee->name,
            'job_title'   => $employee->jobTitle?->name,
            'department'  => $employee->department?->name,
            'status'      => $employee->status->value,
            'children'    => $employee->directReports
                ->map(fn (Employee $child) => $this->buildNode($child))
                ->toArray(),
        ];
    }
}
