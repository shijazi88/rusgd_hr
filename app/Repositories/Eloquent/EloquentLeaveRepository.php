<?php

namespace App\Repositories\Eloquent;

use App\Models\LeaveRequest;
use App\Repositories\Contracts\ILeaveRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class EloquentLeaveRepository implements ILeaveRepository
{
    private const WITH = ['employee.department', 'employee.manager.user', 'leaveType', 'approvedBy'];

    public function findById(int $id): ?LeaveRequest
    {
        return LeaveRequest::with(self::WITH)->find($id);
    }

    public function findAllPaginated(array $filters, int $perPage = 20): LengthAwarePaginator
    {
        $query = LeaveRequest::with(self::WITH)
            ->orderByDesc('created_at');

        if (!empty($filters['status'])) {
            $query->byStatus($filters['status']);
        }

        if (!empty($filters['employee_id'])) {
            $query->byEmployee((int) $filters['employee_id']);
        }

        if (!empty($filters['from_date'])) {
            $query->whereDate('from_date', '>=', $filters['from_date']);
        }

        if (!empty($filters['to_date'])) {
            $query->whereDate('to_date', '<=', $filters['to_date']);
        }

        return $query->paginate($perPage);
    }

    public function findPendingForApprover(int $_approverUserId): Collection
    {
        // Multi-level approver routing is deferred to Phase 7 (Approvals Center).
        // Until then, returns all pending requests visible to the approver role.
        return LeaveRequest::with(self::WITH)
            ->pending()
            ->orderByDesc('created_at')
            ->get();
    }

    public function create(array $data): LeaveRequest
    {
        return LeaveRequest::create($data);
    }

    public function update(LeaveRequest $leave, array $data): LeaveRequest
    {
        $leave->update($data);
        return $leave->fresh(self::WITH);
    }
}
