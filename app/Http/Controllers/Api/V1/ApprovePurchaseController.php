<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\PurchaseRequestResource;
use App\Services\PurchaseService;
use App\Support\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApprovePurchaseController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly PurchaseService $purchaseService) {}

    public function __invoke(Request $request, int $id): JsonResponse
    {
        $purchase = $this->purchaseService->getById($id);
        $this->authorize('approve', $purchase);

        $updated = $this->purchaseService->approve($purchase, $request->user());

        return $this->success(
            PurchaseRequestResource::make($updated),
            'تمت الموافقة على طلب الشراء.'
        );
    }
}
