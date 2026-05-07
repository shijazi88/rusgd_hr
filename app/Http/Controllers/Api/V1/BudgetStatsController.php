<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\PurchaseRequest;
use App\Services\PurchaseService;
use App\Support\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BudgetStatsController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly PurchaseService $purchaseService) {}

    public function __invoke(Request $request): JsonResponse
    {
        $this->authorize('viewAny', PurchaseRequest::class);

        $request->validate([
            'month' => 'sometimes|integer|between:1,12',
            'year'  => 'sometimes|integer|min:2020',
        ]);

        $month = (int) $request->input('month', now()->month);
        $year  = (int) $request->input('year',  now()->year);

        $stats = $this->purchaseService->getMonthlyBudgetStats($month, $year);

        return $this->success([
            'month'          => $month,
            'year'           => $year,
            'approved'       => ['count' => $stats->approvedCount, 'total' => $stats->totalApproved],
            'pending'        => ['count' => $stats->pendingCount,  'total' => $stats->totalPending],
            'rejected'       => ['count' => $stats->rejectedCount, 'total' => $stats->totalRejected],
            'total_processed' => $stats->totalApproved + $stats->totalPending,
        ]);
    }
}
