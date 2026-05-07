<?php

namespace App\Policies;

use App\Models\ApprovalRule;
use App\Models\User;

class ApprovalRulePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('manage_rules');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('manage_rules');
    }

    public function update(User $user, ApprovalRule $rule): bool
    {
        return $user->hasPermissionTo('manage_rules');
    }

    public function delete(User $user, ApprovalRule $rule): bool
    {
        return $user->hasPermissionTo('manage_rules');
    }
}
