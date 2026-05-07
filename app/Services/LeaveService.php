<?php

namespace App\Services;

use App\Enums\LeaveStatus;
use App\Exceptions\ApprovalNotAuthorizedException;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\User;
use App\Repositories\Contracts\ILeaveRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class LeaveService
{
    public function __construct(
        private readonly ILeaveRepository    $leaveRepo,
        private readonly LeaveBalanceService $balanceService,
        private readonly ApprovalRuleEngine  $ruleEngine,
    ) {}

    public function getAll(array $filters, int $perPage = 20): LengthAwarePaginator
    {
        return $this->leaveRepo->findAllPaginated($filters, $perPage);
    }

    public function getById(int $id): LeaveRequest
    {
        $leave = $this->leaveRepo->findById($id);

        if (!$leave) {
            abort(404, 'طلب الإجازة غير موجود.');
        }

        return $leave;
    }

    public function create(
        Employee  $employee,
        LeaveType $leaveType,
        string    $fromDate,
        string    $toDate,
        ?string   $reason
    ): LeaveRequest {
        $days = $this->calculateDays($fromDate, $toDate);

        $this->balanceService->validate($employee, $leaveType, $days);

        $rule          = $this->ruleEngine->resolveForDays($days);
        $approvalLevel = $rule?->approver_label ?? 'المدير المباشر';

        return $this->leaveRepo->create([
            'reference'      => $this->generateReference(),
            'employee_id'    => $employee->id,
            'leave_type_id'  => $leaveType->id,
            'from_date'      => $fromDate,
            'to_date'        => $toDate,
            'days'           => $days,
            'reason'         => $reason,
            'status'         => LeaveStatus::Pending->value,
            'approval_level' => $approvalLevel,
        ]);
    }

    public function approve(LeaveRequest $leave, User $approver): LeaveRequest
    {
        return DB::transaction(function () use ($leave, $approver) {

            // ── Level 1: Direct manager approves pending request ─────────────
            if ($leave->status === LeaveStatus::Pending) {
                $leave->approvalSteps()->create([
                    'user_id' => $approver->id,
                    'level'   => 1,
                    'action'  => 'approved',
                ]);

                return $this->leaveRepo->update($leave, [
                    'status'      => LeaveStatus::InitialApproval->value,
                    'approved_by' => $approver->id,
                    'approved_at' => now(),
                ]);
            }

            // ── Level 2: Management (CEO/HR) approves ────────────────────────
            if ($leave->status === LeaveStatus::InitialApproval) {
                $leave->approvalSteps()->create([
                    'user_id' => $approver->id,
                    'level'   => 2,
                    'action'  => 'approved',
                ]);

                // Reload steps and check if both CEO/director AND hr have approved
                $approvedSteps = $leave->approvalSteps()
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
                    $updated = $this->leaveRepo->update($leave, [
                        'status' => LeaveStatus::Approved->value,
                    ]);

                    $this->balanceService->deduct($leave->employee, $leave->leaveType, $leave->days);

                    return $updated;
                }

                // Waiting for the other management role
                return $leave->fresh(['employee.department', 'employee.manager.user', 'leaveType', 'approvedBy']);
            }

            throw new ApprovalNotAuthorizedException('لا يمكن الموافقة على هذا الطلب.');
        });
    }

    public function reject(LeaveRequest $leave, User $approver, string $reason): LeaveRequest
    {
        return DB::transaction(function () use ($leave, $approver, $reason) {
            $level = $leave->status === LeaveStatus::Pending ? 1 : 2;

            $leave->approvalSteps()->create([
                'user_id' => $approver->id,
                'level'   => $level,
                'action'  => 'rejected',
                'notes'   => $reason,
            ]);

            return $this->leaveRepo->update($leave, [
                'status'           => LeaveStatus::Rejected->value,
                'approved_by'      => $approver->id,
                'approved_at'      => now(),
                'rejection_reason' => $reason,
            ]);
        });
    }

    public function cancel(LeaveRequest $leave): void
    {
        if ($leave->status === LeaveStatus::Approved) {
            $this->balanceService->restore(
                $leave->employee,
                $leave->leaveType,
                $leave->days
            );
        }

        $leave->delete();
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function calculateDays(string $from, string $to): int
    {
        return (int) Carbon::parse($from)->diffInDays(Carbon::parse($to)) + 1;
    }

    private function generateReference(): string
    {
        $year  = now()->year;
        $count = LeaveRequest::withTrashed()->whereYear('created_at', $year)->count() + 1;
        return "LV-{$year}-" . str_pad($count, 4, '0', STR_PAD_LEFT);
    }
}
