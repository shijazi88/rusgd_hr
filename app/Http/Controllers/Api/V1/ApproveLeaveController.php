<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\LeaveRequestResource;
use App\Services\LeaveService;
use App\Support\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApproveLeaveController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly LeaveService $leaveService) {}

    public function __invoke(Request $request, int $id): JsonResponse
    {
        $leave = $this->leaveService->getById($id);
        $this->authorize('approve', $leave);

        $updated = $this->leaveService->approve($leave, $request->user());

        return $this->success(
            LeaveRequestResource::make($updated),
            'تمت الموافقة على طلب الإجازة.'
        );
    }
}
