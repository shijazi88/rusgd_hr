<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\LeaveBalanceResource;
use App\Models\Employee;
use App\Services\LeaveBalanceService;
use App\Support\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LeaveBalanceController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly LeaveBalanceService $balanceService) {}

    public function __invoke(Request $request, int $employeeId): JsonResponse
    {
        $employee = Employee::findOrFail($employeeId);
        $user     = $request->user();

        // Authorize: own balance, OR has staff-level visibility into balances
        $isOwn       = $user->employee_id === $employee->id;
        $isPrivileged = $user->hasPermissionTo('approve_leaves')
                     || $user->hasPermissionTo('view_employees');

        if (!$isOwn && !$isPrivileged) {
            abort(403, 'غير مصرح بعرض رصيد الإجازات لهذا الموظف.');
        }

        $year = (int) $request->input('year', now()->year);

        $balances = $this->balanceService->getForEmployee($employee, $year);

        return $this->success(
            LeaveBalanceResource::collection($balances)
        );
    }
}
