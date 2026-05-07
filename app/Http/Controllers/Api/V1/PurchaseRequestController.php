<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Purchase\StorePurchaseRequestRequest;
use App\Http\Resources\PurchaseRequestResource;
use App\Models\Employee;
use App\Models\PurchaseRequest;
use App\Services\PurchaseService;
use App\Support\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
class PurchaseRequestController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly PurchaseService $purchaseService) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', PurchaseRequest::class);

        $perPage = max(1, min((int) $request->input('per_page', 20), 100));

        $filters = $request->only(['status', 'employee_id', 'month', 'year']);

        // Regular employees see only their own requests. Approvers/admins see all.
        $user = $request->user();
        if (!$user->hasPermissionTo('approve_purchases') && !$user->hasPermissionTo('edit_employees')) {
            $filters['employee_id'] = $user->employee_id;
        }

        $purchases = $this->purchaseService->getAll($filters, $perPage);

        return $this->paginated(PurchaseRequestResource::collection($purchases));
    }

    public function show(int $id): JsonResponse
    {
        $purchase = $this->purchaseService->getById($id);
        $this->authorize('view', $purchase);

        return $this->success(PurchaseRequestResource::make($purchase));
    }

    public function store(StorePurchaseRequestRequest $request): JsonResponse
    {
        $this->authorize('create', PurchaseRequest::class);

        $user     = $request->user();
        $targetId = (int) $request->employee_id;

        // Only HR/admin (edit_employees) may submit on behalf of another employee.
        if ($targetId !== $user->employee_id && !$user->hasPermissionTo('edit_employees')) {
            abort(403, 'لا يمكنك تقديم طلب شراء نيابةً عن موظف آخر.');
        }

        $employee = Employee::findOrFail($targetId);

        $purchase = $this->purchaseService->create($employee, $request->validated());

        return $this->created(
            PurchaseRequestResource::make($purchase->load(['employee.department', 'approvedBy'])),
            'تم إرسال طلب الشراء بنجاح.'
        );
    }

    public function destroy(int $id): JsonResponse
    {
        $purchase = $this->purchaseService->getById($id);
        $this->authorize('delete', $purchase);

        $this->purchaseService->cancel($purchase);

        return $this->success(message: 'تم إلغاء طلب الشراء بنجاح.');
    }
}
