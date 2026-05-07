<?php

namespace App\Repositories\Eloquent;

use App\Models\PurchaseRequest;
use App\Repositories\Contracts\IPurchaseRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentPurchaseRepository implements IPurchaseRepository
{
    private const WITH = ['employee.department', 'employee.manager.user', 'approvedBy'];

    public function findById(int $id): ?PurchaseRequest
    {
        return PurchaseRequest::with(self::WITH)->find($id);
    }

    public function findAllPaginated(array $filters, int $perPage): LengthAwarePaginator
    {
        $query = PurchaseRequest::with(self::WITH)->orderByDesc('created_at');

        if (!empty($filters['status'])) {
            $query->byStatus($filters['status']);
        }

        if (!empty($filters['employee_id'])) {
            $query->byEmployee((int) $filters['employee_id']);
        }

        if (!empty($filters['month']) && !empty($filters['year'])) {
            $query->forMonth((int) $filters['month'], (int) $filters['year']);
        }

        return $query->paginate($perPage);
    }

    public function create(array $data): PurchaseRequest
    {
        return PurchaseRequest::create($data);
    }

    public function update(PurchaseRequest $purchase, array $data): PurchaseRequest
    {
        $purchase->update($data);
        return $purchase->fresh(self::WITH);
    }

    public function getMonthlyStats(int $month, int $year): array
    {
        return PurchaseRequest::forMonth($month, $year)
            ->selectRaw('status, COUNT(*) as count, COALESCE(SUM(amount), 0) as total')
            ->groupBy('status')
            ->get()
            ->keyBy('status')
            ->toArray();
    }
}
