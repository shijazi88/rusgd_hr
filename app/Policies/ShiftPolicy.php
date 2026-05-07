<?php

namespace App\Policies;

use App\Models\Shift;
use App\Models\User;

class ShiftPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('manage_shifts');
    }

    public function view(User $user, Shift $shift): bool
    {
        return $user->hasPermissionTo('manage_shifts');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('manage_shifts');
    }

    public function update(User $user, Shift $shift): bool
    {
        return $user->hasPermissionTo('manage_shifts');
    }

    public function delete(User $user, Shift $shift): bool
    {
        return $user->hasPermissionTo('manage_shifts');
    }
}
