<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use App\Support\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly DashboardService $dashboardService) {}

    public function __invoke(Request $request): JsonResponse
    {
        // Dashboard is accessible to any authenticated user with view access
        if (!$request->user()->hasPermissionTo('view_employees')) {
            abort(403, 'غير مصرح.');
        }

        return $this->success($this->dashboardService->getStats());
    }
}
