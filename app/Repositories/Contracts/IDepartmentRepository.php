<?php

namespace App\Repositories\Contracts;

use App\Models\Department;
use Illuminate\Database\Eloquent\Collection;

interface IDepartmentRepository
{
    public function getAll(): Collection;

    public function findById(int $id): ?Department;

    public function create(array $data): Department;

    public function update(Department $department, array $data): Department;

    public function delete(Department $department): bool;
}
