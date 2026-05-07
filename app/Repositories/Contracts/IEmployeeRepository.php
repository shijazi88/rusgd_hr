<?php

namespace App\Repositories\Contracts;

use App\Models\Employee;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface IEmployeeRepository
{
    public function findById(int $id): ?Employee;

    public function findAllPaginated(array $filters, int $perPage = 20): LengthAwarePaginator;

    public function getOrgChartRoots(): Collection;

    public function create(array $data): Employee;

    public function update(Employee $employee, array $data): Employee;

    public function softDelete(Employee $employee): bool;
}
