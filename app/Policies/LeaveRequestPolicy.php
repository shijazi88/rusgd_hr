<?php

namespace App\Policies;

use App\Enums\LeaveStatus;
use App\Models\LeaveRequest;
use App\Models\User;

class LeaveRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view_employees');
    }

    public function view(User $user, LeaveRequest $leave): bool
    {
        return $user->employee_id === $leave->employee_id
            || $user->hasPermissionTo('view_employees');
    }

    public function create(User $_user): bool
    {
        return true;
    }

    public function delete(User $user, LeaveRequest $leave): bool
    {
        // Employee can cancel only while still at level 1 (pending)
        return $user->employee_id === $leave->employee_id
            && $leave->status === LeaveStatus::Pending;
    }

    public function approve(User $user, LeaveRequest $leave): bool
    {
        if (!$user->hasPermissionTo('approve_leaves')) {
            return false;
        }

        // Level 1: The employee's direct manager approves pending requests
        if ($leave->status === LeaveStatus::Pending) {
            $managerUserId = $leave->employee?->manager?->user?->id;

            // No direct manager → any management user can approve at level 1
            if ($managerUserId === null) {
                return $user->hasRole('ceo') || $user->hasRole('director') || $user->hasRole('hr');
            }

            return $managerUserId === $user->id;
        }

        // Level 2: CEO/director/HR approves — each can act only once
        if ($leave->status === LeaveStatus::InitialApproval) {
            if (!($user->hasRole('ceo') || $user->hasRole('director') || $user->hasRole('hr'))) {
                return false;
            }

            $alreadyActed = $leave->approvalSteps()
                ->where('user_id', $user->id)
                ->where('level', 2)
                ->exists();

            return !$alreadyActed;
        }

        return false;
    }

    public function reject(User $user, LeaveRequest $leave): bool
    {
        return $this->approve($user, $leave);
    }
}
