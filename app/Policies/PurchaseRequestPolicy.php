<?php

namespace App\Policies;

use App\Enums\PurchaseStatus;
use App\Models\PurchaseRequest;
use App\Models\User;

class PurchaseRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('approve_purchases')
            || $user->hasPermissionTo('view_employees');
    }

    public function view(User $user, PurchaseRequest $purchase): bool
    {
        return $user->employee_id === $purchase->employee_id
            || $user->hasPermissionTo('approve_purchases')
            || $user->hasPermissionTo('view_employees');
    }

    public function create(User $_user): bool
    {
        return true;
    }

    public function delete(User $user, PurchaseRequest $purchase): bool
    {
        return $user->employee_id === $purchase->employee_id
            && $purchase->status === PurchaseStatus::Pending;
    }

    public function approve(User $user, PurchaseRequest $purchase): bool
    {
        if (!$user->hasPermissionTo('approve_purchases')) {
            return false;
        }

        // Level 1: Direct manager approves pending requests
        if ($purchase->status === PurchaseStatus::Pending) {
            $managerUserId = $purchase->employee?->manager?->user?->id;

            if ($managerUserId === null) {
                return $user->hasRole('ceo') || $user->hasRole('director') || $user->hasRole('hr');
            }

            return $managerUserId === $user->id;
        }

        // Level 2: CEO/director/HR approves — each can act only once
        if ($purchase->status === PurchaseStatus::InitialApproval) {
            if (!($user->hasRole('ceo') || $user->hasRole('director') || $user->hasRole('hr'))) {
                return false;
            }

            $alreadyActed = $purchase->approvalSteps()
                ->where('user_id', $user->id)
                ->where('level', 2)
                ->exists();

            return !$alreadyActed;
        }

        return false;
    }

    public function reject(User $user, PurchaseRequest $purchase): bool
    {
        return $this->approve($user, $purchase);
    }
}
