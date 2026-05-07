<?php

namespace App\Repositories\Contracts;

use App\Models\PurchaseRequest;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface IPurchaseRepository
{
    public function findById(int $id): ?PurchaseRequest;

    public function findAllPaginated(array $filters, int $perPage): LengthAwarePaginator;

    public function create(array $data): PurchaseRequest;

    public function update(PurchaseRequest $purchase, array $data): PurchaseRequest;

    public function getMonthlyStats(int $month, int $year): array;
}
