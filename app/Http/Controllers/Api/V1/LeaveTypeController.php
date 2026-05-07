<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\LeaveTypeResource;
use App\Models\LeaveType;
use App\Support\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class LeaveTypeController extends Controller
{
    use ApiResponse;

    public function __invoke(): JsonResponse
    {
        return $this->success(
            LeaveTypeResource::collection(LeaveType::all())
        );
    }
}
