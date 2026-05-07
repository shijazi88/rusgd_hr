<?php

namespace App\Repositories\Contracts;

use App\Models\LeaveRequest;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface ILeaveRepository
{
    public function findById(int $id): ?LeaveRequest;

    public function findAllPaginated(array $filters, int $perPage = 20): LengthAwarePaginator;

    public function findPendingForApprover(int $approverUserId): Collection;

    public function create(array $data): LeaveRequest;

    public function update(LeaveRequest $leave, array $data): LeaveRequest;
}
