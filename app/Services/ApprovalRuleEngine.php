<?php

namespace App\Services;

use App\Models\ApprovalRule;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ApprovalRuleEngine
{
    public function resolveForDays(int $days): ?ApprovalRule
    {
        return $this->getRules('leave')->first(
            fn (ApprovalRule $rule) =>
                $rule->min_value <= $days &&
                ($rule->max_value == 0 || $rule->max_value >= $days)
        );
    }

    public function resolveForAmount(float $amount): ?ApprovalRule
    {
        return $this->getRules('financial')->first(
            fn (ApprovalRule $rule) =>
                $rule->min_value <= $amount &&
                ($rule->max_value == 0 || $rule->max_value >= $amount)
        );
    }

    public function resolveApproverUser(ApprovalRule $rule, Employee $requester): ?User
    {
        if ($rule->approver_role === 'manager') {
            return $requester->manager?->user;
        }

        // Find any active user who holds the required role
        $userId = DB::table('users')
            ->join('user_roles', 'user_roles.user_id', '=', 'users.id')
            ->join('roles', 'roles.id', '=', 'user_roles.role_id')
            ->where('roles.slug', $rule->approver_role)
            ->value('users.id');

        return $userId ? User::find($userId) : null;
    }

    private function getRules(string $type): \Illuminate\Support\Collection
    {
        return Cache::remember(
            "approval_rules.{$type}",
            now()->addHours(24),
            fn () => ApprovalRule::where('type', $type)
                ->where('is_active', true)
                ->orderBy('min_value')
                ->get()
        );
    }

    public function clearCache(): void
    {
        Cache::forget('approval_rules.leave');
        Cache::forget('approval_rules.financial');
    }
}
