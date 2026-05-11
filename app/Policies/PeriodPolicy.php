<?php

namespace App\Policies;

use App\Models\Period;
use App\Models\User;

class PeriodPolicy
{
    /**
     * Periods are referenced by shift_days (used in shift builder dropdowns),
     * so any authenticated user with shift-management context can READ them.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('manage_periods')
            || $user->hasPermissionTo('manage_shifts');
    }

    public function view(User $user, Period $_period): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('manage_periods');
    }

    public function update(User $user, Period $_period): bool
    {
        return $user->hasPermissionTo('manage_periods');
    }

    public function delete(User $user, Period $_period): bool
    {
        return $user->hasPermissionTo('manage_periods');
    }
}
