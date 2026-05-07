<?php

namespace App\Repositories\Eloquent;

use App\Models\Department;
use App\Repositories\Contracts\IDepartmentRepository;
use Illuminate\Database\Eloquent\Collection;

class EloquentDepartmentRepository implements IDepartmentRepository
{
    public function getAll(): Collection
    {
        return Department::withCount('employees')
            ->with('parent')
            ->orderBy('name')
            ->get();
    }

    public function findById(int $id): ?Department
    {
        return Department::with(['parent', 'children'])->find($id);
    }

    public function create(array $data): Department
    {
        return Department::create($data);
    }

    public function update(Department $department, array $data): Department
    {
        $department->update($data);
        return $department->fresh();
    }

    public function delete(Department $department): bool
    {
        return $department->delete();
    }
}
