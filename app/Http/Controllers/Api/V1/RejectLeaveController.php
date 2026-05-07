<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Leave\RejectLeaveRequestRequest;
use App\Http\Resources\LeaveRequestResource;
use App\Services\LeaveService;
use App\Support\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class RejectLeaveController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly LeaveService $leaveService) {}

    public function __invoke(RejectLeaveRequestRequest $request, int $id): JsonResponse
    {
        $leave = $this->leaveService->getById($id);
        $this->authorize('reject', $leave);

        $updated = $this->leaveService->reject(
            $leave,
            $request->user(),
            $request->validated('reason')
        );

        return $this->success(
            LeaveRequestResource::make($updated),
            'تم رفض طلب الإجازة.'
        );
    }
}
