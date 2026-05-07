<?php

namespace App\Policies;

use App\Models\JobTitle;
use App\Models\User;

class JobTitlePolicy
{
    /**
     * Reading job titles is open — needed by employee form dropdowns
     * (cascading from selected department).
     */
    public function viewAny(User $_user): bool
    {
        return true;
    }

    public function view(User $_user, JobTitle $_jobTitle): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('manage_departments');
    }

    public function update(User $user, JobTitle $_jobTitle): bool
    {
        return $user->hasPermissionTo('manage_departments');
    }

    public function delete(User $user, JobTitle $_jobTitle): bool
    {
        return $user->hasPermissionTo('manage_departments');
    }
}
