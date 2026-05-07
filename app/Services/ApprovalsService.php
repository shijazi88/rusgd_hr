<?php

namespace App\Services;

use App\Enums\LeaveStatus;
use App\Enums\PurchaseStatus;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\PurchaseRequest;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ApprovalsService
{
    // ── Public API ────────────────────────────────────────────────────────────

    public function getUnified(User $user, string $status, int $perPage, int $page): LengthAwarePaginator
    {
        $canLeaves    = $user->hasPermissionTo('approve_leaves');
        $canPurchases = $user->hasPermissionTo('approve_purchases');
        $isManagement = $this->isManagement($user);
        $subordinates = $this->subordinateIds($user);

        $tuples = collect();

        if ($canLeaves) {
            $query = LeaveRequest::with(['employee.department', 'employee.manager.user', 'leaveType', 'approvedBy', 'approvalSteps.user']);
            $this->applyVisibilityFilter($query, $status, $isManagement, $subordinates, 'leave');
            $tuples = $tuples->concat(
                $query->get()->map(fn ($l) => ['type' => 'leave', 'model' => $l, '_ts' => $l->created_at])
            );
        }

        if ($canPurchases) {
            $query = PurchaseRequest::with(['employee.department', 'employee.manager.user', 'approvedBy', 'approvalSteps.user']);
            $this->applyVisibilityFilter($query, $status, $isManagement, $subordinates, 'purchase');
            $tuples = $tuples->concat(
                $query->get()->map(fn ($p) => ['type' => 'purchase', 'model' => $p, '_ts' => $p->created_at])
            );
        }

        $sorted = $tuples->sortByDesc('_ts')->values();
        $total  = $sorted->count();

        $sliced = $sorted->forPage($page, $perPage)
            ->map(fn ($t) => $t['type'] === 'leave'
                ? $this->formatLeave($t['model'], $user)
                : $this->formatPurchase($t['model'], $user)
            )
            ->values();

        return new LengthAwarePaginator($sliced, $total, $perPage, $page, [
            'path'  => LengthAwarePaginator::resolveCurrentPath(),
            'query' => request()->except('page'),
        ]);
    }

    public function getPendingCount(User $user): array
    {
        $isManagement = $this->isManagement($user);
        $subordinates = $this->subordinateIds($user);

        $leaves    = 0;
        $purchases = 0;

        if ($user->hasPermissionTo('approve_leaves')) {
            // Level 1: pending requests from direct reports
            if ($subordinates->isNotEmpty()) {
                $leaves += LeaveRequest::where('status', LeaveStatus::Pending->value)
                    ->whereIn('employee_id', $subordinates)
                    ->count();
            }
            // Level 2: initial_approval requests the user hasn't acted on yet
            if ($isManagement) {
                $leaves += LeaveRequest::where('status', LeaveStatus::InitialApproval->value)
                    ->whereDoesntHave('approvalSteps', fn ($q) =>
                        $q->where('user_id', $user->id)->where('level', 2)
                    )
                    ->count();
            }
        }

        if ($user->hasPermissionTo('approve_purchases')) {
            if ($subordinates->isNotEmpty()) {
                $purchases += PurchaseRequest::where('status', PurchaseStatus::Pending->value)
                    ->whereIn('employee_id', $subordinates)
                    ->count();
            }
            if ($isManagement) {
                $purchases += PurchaseRequest::where('status', PurchaseStatus::InitialApproval->value)
                    ->whereDoesntHave('approvalSteps', fn ($q) =>
                        $q->where('user_id', $user->id)->where('level', 2)
                    )
                    ->count();
            }
        }

        return [
            'leaves'    => $leaves,
            'purchases' => $purchases,
            'total'     => $leaves + $purchases,
        ];
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function isManagement(User $user): bool
    {
        return $user->hasRole('ceo') || $user->hasRole('director') || $user->hasRole('hr');
    }

    private function subordinateIds(User $user): Collection
    {
        if (!$user->employee_id) {
            return collect();
        }
        return Employee::where('manager_id', $user->employee_id)->pluck('id');
    }

    private function applyVisibilityFilter(
        $query,
        string $status,
        bool $isManagement,
        Collection $subordinates,
        string $type
    ): void {
        $pendingVal  = $type === 'leave' ? LeaveStatus::Pending->value  : PurchaseStatus::Pending->value;
        $initialVal  = $type === 'leave' ? LeaveStatus::InitialApproval->value : PurchaseStatus::InitialApproval->value;
        $approvedVal = $type === 'leave' ? LeaveStatus::Approved->value : PurchaseStatus::Approved->value;
        $rejectedVal = $type === 'leave' ? LeaveStatus::Rejected->value : PurchaseStatus::Rejected->value;

        if ($status === 'pending') {
            // Show level-1 (pending) for subordinates AND level-2 (initial_approval) for management
            $hasSubordinates = $subordinates->isNotEmpty();

            if (!$hasSubordinates && !$isManagement) {
                $query->whereRaw('1 = 0');
                return;
            }

            $query->where(function ($q) use ($pendingVal, $initialVal, $isManagement, $subordinates, $hasSubordinates) {
                if ($hasSubordinates) {
                    $q->orWhere(fn ($sq) => $sq->where('status', $pendingVal)->whereIn('employee_id', $subordinates));
                }
                if ($isManagement) {
                    $q->orWhere('status', $initialVal);
                }
            });

        } elseif ($status === 'all') {
            // Management sees everything; direct managers see their team
            if (!$isManagement) {
                if ($subordinates->isEmpty()) {
                    $query->whereRaw('1 = 0');
                } else {
                    $query->whereIn('employee_id', $subordinates);
                }
            }

        } else {
            // approved or rejected — filter by status first
            $statusVal = $status === 'approved' ? $approvedVal : $rejectedVal;
            $query->where('status', $statusVal);

            if (!$isManagement) {
                if ($subordinates->isEmpty()) {
                    $query->whereRaw('1 = 0');
                } else {
                    $query->whereIn('employee_id', $subordinates);
                }
            }
        }
    }

    // ── Formatters ────────────────────────────────────────────────────────────

    private function formatLeave(LeaveRequest $leave, User $viewer): array
    {
        $steps        = $leave->approvalSteps;
        $level2       = $steps->where('level', 2);
        $isInitial    = $leave->status === LeaveStatus::InitialApproval;

        $ceoApproved  = $level2->where('action', 'approved')->contains(
            fn ($s) => $s->user?->hasRole('ceo') || $s->user?->hasRole('director')
        );
        $hrApproved   = $level2->where('action', 'approved')->contains(
            fn ($s) => $s->user?->hasRole('hr')
        );

        $alreadyActed = $level2->contains('user_id', $viewer->id);
        $canAct       = !$alreadyActed && $isInitial && $this->isManagement($viewer);

        return [
            'id'               => $leave->id,
            'type'             => 'leave',
            'reference'        => $leave->reference,
            'requester'        => [
                'id'         => $leave->employee?->id,
                'name'       => $leave->employee?->name,
                'department' => $leave->employee?->department?->name,
            ],
            'details'          => ($leave->leaveType?->name ?? 'إجازة') . ' · ' . $leave->days . ' أيام',
            'amount'           => null,
            'status'           => $leave->status?->value,
            'rejection_reason' => $leave->rejection_reason,
            'approved_by'      => $leave->approvedBy?->name,
            'approved_at'      => $leave->approved_at?->format('Y-m-d H:i'),
            'created_at'       => $leave->created_at?->format('Y-m-d H:i'),
            'can_act'          => $leave->status === LeaveStatus::Pending
                ? $leave->employee?->manager?->user?->id === $viewer->id || $leave->employee?->manager_id === null && $this->isManagement($viewer)
                : $canAct,
            'management_status' => $isInitial ? [
                'ceo_approved' => $ceoApproved,
                'hr_approved'  => $hrApproved,
                'already_acted' => $alreadyActed,
            ] : null,
            'approval_steps'   => $steps->map(fn ($s) => [
                'name'   => $s->user?->name,
                'level'  => $s->level,
                'action' => $s->action,
                'notes'  => $s->notes,
                'at'     => $s->created_at?->format('Y-m-d H:i'),
            ])->values(),
        ];
    }

    private function formatPurchase(PurchaseRequest $purchase, User $viewer): array
    {
        $steps     = $purchase->approvalSteps;
        $level2    = $steps->where('level', 2);
        $isInitial = $purchase->status === PurchaseStatus::InitialApproval;

        $ceoApproved = $level2->where('action', 'approved')->contains(
            fn ($s) => $s->user?->hasRole('ceo') || $s->user?->hasRole('director')
        );
        $hrApproved  = $level2->where('action', 'approved')->contains(
            fn ($s) => $s->user?->hasRole('hr')
        );

        $alreadyActed = $level2->contains('user_id', $viewer->id);
        $canAct       = !$alreadyActed && $isInitial && $this->isManagement($viewer);

        $details = $purchase->item_name . ($purchase->vendor ? " · {$purchase->vendor}" : '');

        return [
            'id'               => $purchase->id,
            'type'             => 'purchase',
            'reference'        => $purchase->reference,
            'requester'        => [
                'id'         => $purchase->employee?->id,
                'name'       => $purchase->employee?->name,
                'department' => $purchase->employee?->department?->name,
            ],
            'details'          => $details,
            'amount'           => (float) $purchase->amount,
            'status'           => $purchase->status?->value,
            'rejection_reason' => $purchase->rejection_reason,
            'approved_by'      => $purchase->approvedBy?->name,
            'approved_at'      => $purchase->approved_at?->format('Y-m-d H:i'),
            'created_at'       => $purchase->created_at?->format('Y-m-d H:i'),
            'can_act'          => $purchase->status === PurchaseStatus::Pending
                ? $purchase->employee?->manager?->user?->id === $viewer->id || $purchase->employee?->manager_id === null && $this->isManagement($viewer)
                : $canAct,
            'management_status' => $isInitial ? [
                'ceo_approved'  => $ceoApproved,
                'hr_approved'   => $hrApproved,
                'already_acted' => $alreadyActed,
            ] : null,
            'approval_steps'   => $steps->map(fn ($s) => [
                'name'   => $s->user?->name,
                'level'  => $s->level,
                'action' => $s->action,
                'notes'  => $s->notes,
                'at'     => $s->created_at?->format('Y-m-d H:i'),
            ])->values(),
        ];
    }
}
