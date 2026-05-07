<?php

namespace App\Policies;

use App\Models\Department;
use App\Models\User;

class DepartmentPolicy
{
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('manage_departments');
    }

    public function update(User $user, Department $department): bool
    {
        return $user->hasPermissionTo('manage_departments');
    }

    public function delete(User $user, Department $department): bool
    {
        return $user->hasPermissionTo('manage_departments');
    }
}
