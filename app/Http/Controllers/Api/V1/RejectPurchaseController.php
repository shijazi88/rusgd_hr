<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Purchase\RejectPurchaseRequestRequest;
use App\Http\Resources\PurchaseRequestResource;
use App\Services\PurchaseService;
use App\Support\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class RejectPurchaseController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly PurchaseService $purchaseService) {}

    public function __invoke(RejectPurchaseRequestRequest $request, int $id): JsonResponse
    {
        $purchase = $this->purchaseService->getById($id);
        $this->authorize('reject', $purchase);

        $updated = $this->purchaseService->reject(
            $purchase,
            $request->user(),
            $request->validated('reason')
        );

        return $this->success(
            PurchaseRequestResource::make($updated),
            'تم رفض طلب الشراء.'
        );
    }
}
