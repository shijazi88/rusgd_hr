<?php

namespace App\Policies;

use App\Models\Employee;
use App\Models\User;

class EmployeePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view_employees');
    }

    public function view(User $user, Employee $employee): bool
    {
        return $user->hasPermissionTo('view_employees');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('edit_employees');
    }

    public function update(User $user, Employee $employee): bool
    {
        return $user->hasPermissionTo('edit_employees');
    }

    public function delete(User $user, Employee $employee): bool
    {
        return $user->hasPermissionTo('delete_employees');
    }
}
