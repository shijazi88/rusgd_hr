<?php

namespace App\Services;

use App\DTOs\BudgetStatsDTO;
use App\Enums\PurchaseStatus;
use App\Exceptions\ApprovalNotAuthorizedException;
use App\Models\Employee;
use App\Models\PurchaseRequest;
use App\Models\User;
use App\Repositories\Contracts\IPurchaseRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class PurchaseService
{
    public function __construct(
        private readonly IPurchaseRepository $purchaseRepo,
        private readonly ApprovalRuleEngine  $ruleEngine,
    ) {}

    public function getAll(array $filters, int $perPage): LengthAwarePaginator
    {
        return $this->purchaseRepo->findAllPaginated($filters, $perPage);
    }

    public function getById(int $id): PurchaseRequest
    {
        $purchase = $this->purchaseRepo->findById($id);

        if (!$purchase) {
            abort(404, 'طلب الشراء غير موجود.');
        }

        return $purchase;
    }

    public function create(Employee $employee, array $data): PurchaseRequest
    {
        $rule          = $this->ruleEngine->resolveForAmount((float) $data['amount']);
        $approvalLevel = $rule?->approver_label ?? 'المدير المباشر';

        return $this->purchaseRepo->create([
            'reference'      => $this->generateReference(),
            'employee_id'    => $employee->id,
            'item_name'      => $data['item_name'],
            'vendor'         => $data['vendor'] ?? null,
            'quantity'       => $data['quantity'] ?? 1,
            'amount'         => $data['amount'],
            'reason'         => $data['reason'],
            'status'         => PurchaseStatus::Pending->value,
            'approval_level' => $approvalLevel,
        ]);
    }

    public function approve(PurchaseRequest $purchase, User $approver): PurchaseRequest
    {
        return DB::transaction(function () use ($purchase, $approver) {

            // ── Level 1: Direct manager approves pending request ─────────────
            if ($purchase->status === PurchaseStatus::Pending) {
                $purchase->approvalSteps()->create([
                    'user_id' => $approver->id,
                    'level'   => 1,
                    'action'  => 'approved',
                ]);

                return $this->purchaseRepo->update($purchase, [
                    'status'      => PurchaseStatus::InitialApproval->value,
                    'approved_by' => $approver->id,
                    'approved_at' => now(),
                ]);
            }

            // ── Level 2: Management (CEO/HR) approves ────────────────────────
            if ($purchase->status === PurchaseStatus::InitialApproval) {
                $purchase->approvalSteps()->create([
                    'user_id' => $approver->id,
                    'level'   => 2,
                    'action'  => 'approved',
                ]);

                $approvedSteps = $purchase->approvalSteps()
                    ->where('level', 2)
                    ->where('action', 'approved')
                    ->with('user')
                    ->get();

                $ceoApproved = $approvedSteps->contains(
                    fn ($s) => $s->user?->hasRole('ceo') || $s->user?->hasRole('director')
                );
                $hrApproved = $approvedSteps->contains(
                    fn ($s) => $s->user?->hasRole('hr')
                );

                if ($ceoApproved && $hrApproved) {
                    return $this->purchaseRepo->update($purchase, [
                        'status' => PurchaseStatus::Approved->value,
                    ]);
                }

                return $purchase->fresh(['employee.department', 'employee.manager.user', 'approvedBy']);
            }

            throw new ApprovalNotAuthorizedException('لا يمكن الموافقة على هذا الطلب.');
        });
    }

    public function reject(PurchaseRequest $purchase, User $approver, string $reason): PurchaseRequest
    {
        return DB::transaction(function () use ($purchase, $approver, $reason) {
            $level = $purchase->status === PurchaseStatus::Pending ? 1 : 2;

            $purchase->approvalSteps()->create([
                'user_id' => $approver->id,
                'level'   => $level,
                'action'  => 'rejected',
                'notes'   => $reason,
            ]);

            return $this->purchaseRepo->update($purchase, [
                'status'           => PurchaseStatus::Rejected->value,
                'approved_by'      => $approver->id,
                'approved_at'      => now(),
                'rejection_reason' => $reason,
            ]);
        });
    }

    public function cancel(PurchaseRequest $purchase): void
    {
        $purchase->delete();
    }

    public function getMonthlyBudgetStats(int $month, int $year): BudgetStatsDTO
    {
        $stats = $this->purchaseRepo->getMonthlyStats($month, $year);

        $pending = $stats[PurchaseStatus::Pending->value]         ?? [];
        $initial = $stats[PurchaseStatus::InitialApproval->value] ?? [];

        return new BudgetStatsDTO(
            totalApproved:  (float) ($stats[PurchaseStatus::Approved->value]['total'] ?? 0),
            totalPending:   (float) (($pending['total'] ?? 0) + ($initial['total'] ?? 0)),
            totalRejected:  (float) ($stats[PurchaseStatus::Rejected->value]['total'] ?? 0),
            approvedCount:  (int)   ($stats[PurchaseStatus::Approved->value]['count'] ?? 0),
            pendingCount:   (int)   (($pending['count'] ?? 0) + ($initial['count'] ?? 0)),
            rejectedCount:  (int)   ($stats[PurchaseStatus::Rejected->value]['count'] ?? 0),
        );
    }

    private function generateReference(): string
    {
        $year  = now()->year;
        $count = PurchaseRequest::withTrashed()->whereYear('created_at', $year)->count() + 1;
        return "PR-{$year}-" . str_pad($count, 4, '0', STR_PAD_LEFT);
    }
}
