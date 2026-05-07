<?php

namespace App\Policies;

use App\Models\Attendance;
use App\Models\User;

class AttendancePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('manage_shifts') || $user->hasPermissionTo('view_employees');
    }

    public function view(User $user, Attendance $attendance): bool
    {
        // FK is users.employee_id → employees.id, so check through the user relation
        return $attendance->employee?->user?->is($user)
            || $user->hasPermissionTo('manage_shifts')
            || $user->hasPermissionTo('view_employees');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('manage_shifts');
    }

    public function update(User $user, Attendance $attendance): bool
    {
        return $user->hasPermissionTo('manage_shifts');
    }
}
