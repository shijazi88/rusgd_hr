<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApprovalResource;
use App\Services\ApprovalsService;
use App\Support\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApprovalsController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly ApprovalsService $approvalsService) {}

    public function index(Request $request): JsonResponse
    {
        $this->requireApproverPermission($request);

        $request->validate([
            'status'   => 'sometimes|string|in:pending,approved,rejected,all',
            'per_page' => 'sometimes|integer|min:1|max:100',
        ]);

        $status  = $request->input('status', 'pending');
        $perPage = max(1, min((int) $request->input('per_page', 20), 100));
        $page    = max(1, (int) $request->input('page', 1));

        $paginator = $this->approvalsService->getUnified(
            $request->user(), $status, $perPage, $page
        );

        return $this->paginated(ApprovalResource::collection($paginator));
    }

    public function pendingCount(Request $request): JsonResponse
    {
        $this->requireApproverPermission($request);

        return $this->success($this->approvalsService->getPendingCount($request->user()));
    }

    private function requireApproverPermission(Request $request): void
    {
        $user = $request->user();

        if (!$user->hasPermissionTo('approve_leaves') && !$user->hasPermissionTo('approve_purchases')) {
            abort(403, 'غير مصرح بالوصول إلى مركز الموافقات.');
        }
    }
}
