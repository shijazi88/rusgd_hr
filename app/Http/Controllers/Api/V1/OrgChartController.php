<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\EmployeeService;
use App\Support\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class OrgChartController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly EmployeeService $employeeService) {}

    public function __invoke(): JsonResponse
    {
        return $this->success($this->employeeService->getOrgChart());
    }
}
