<?php

use App\Http\Controllers\Api\V1\ActivityLogController;
use App\Http\Controllers\Api\V1\ApprovalRuleController;
use App\Http\Controllers\Api\V1\ApproveLeaveController;
use App\Http\Controllers\Api\V1\ApprovePurchaseController;
use App\Http\Controllers\Api\V1\ApprovalsController;
use App\Http\Controllers\Api\V1\AssignRoleController;
use App\Http\Controllers\Api\V1\AttendanceController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BudgetStatsController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\DepartmentController;
use App\Http\Controllers\Api\V1\EmployeeController;
use App\Http\Controllers\Api\V1\LeaveBalanceController;
use App\Http\Controllers\Api\V1\LeaveRequestController;
use App\Http\Controllers\Api\V1\LeaveTypeController;
use App\Http\Controllers\Api\V1\OrgChartController;
use App\Http\Controllers\Api\V1\PermissionController;
use App\Http\Controllers\Api\V1\PurchaseRequestController;
use App\Http\Controllers\Api\V1\RejectLeaveController;
use App\Http\Controllers\Api\V1\RejectPurchaseController;
use App\Http\Controllers\Api\V1\RoleController;
use App\Http\Controllers\Api\V1\ShiftAssignmentController;
use App\Http\Controllers\Api\V1\ShiftController;
use App\Http\Controllers\Api\V1\PayrollController;
use App\Http\Controllers\Api\V1\SyncPermissionsController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    // ── Auth (public) ────────────────────────────────────────────────────────
    Route::prefix('auth')->group(function () {
        Route::post('/login', [AuthController::class, 'login'])
            ->middleware('throttle:5,1');
    });

    // ── Auth (protected) ─────────────────────────────────────────────────────
    Route::prefix('auth')->middleware('auth:sanctum')->group(function () {
        Route::post('/logout',     [AuthController::class, 'logout']);
        Route::post('/logout-all', [AuthController::class, 'logoutAll']);
        Route::get('/me',          [AuthController::class, 'me']);
    });

    // ── Protected routes ─────────────────────────────────────────────────────
    Route::middleware('auth:sanctum')->group(function () {

        // Roles & Permissions
        Route::apiResource('roles', RoleController::class);
        Route::post('roles/{role}/sync-permissions', SyncPermissionsController::class);
        Route::get('permissions', PermissionController::class);

        // Approval Rules
        Route::apiResource('approval-rules', ApprovalRuleController::class)
            ->only(['index', 'store', 'update', 'destroy']);

        // Employees
        Route::apiResource('employees', EmployeeController::class);
        Route::get('org-chart', OrgChartController::class);
        Route::post('employees/{employeeId}/roles', AssignRoleController::class);

        // Departments
        Route::get('departments',         [DepartmentController::class, 'index']);
        Route::post('departments',        [DepartmentController::class, 'store']);
        Route::put('departments/{id}',    [DepartmentController::class, 'update']);
        Route::delete('departments/{id}', [DepartmentController::class, 'destroy']);

        // Leave Types
        Route::get('leave-types', LeaveTypeController::class);

        // Leave Balances
        Route::get('employees/{employeeId}/leave-balances', LeaveBalanceController::class);

        // Leave Requests
        Route::apiResource('leave-requests', LeaveRequestController::class)
            ->only(['index', 'show', 'store', 'destroy']);

        Route::post('leave-requests/{id}/approve', ApproveLeaveController::class);
        Route::post('leave-requests/{id}/reject',  RejectLeaveController::class);

        // Shifts — specific routes before apiResource to avoid {shift} swallowing named segments
        Route::get('shifts/weekly-schedule', [ShiftController::class, 'weeklySchedule']);
        Route::apiResource('shifts', ShiftController::class);

        // Shift Assignments
        Route::apiResource('shift-assignments', ShiftAssignmentController::class)
            ->only(['index', 'store', 'destroy']);

        // Attendance — specific routes before apiResource
        Route::get('attendance/monthly-summary',   [AttendanceController::class, 'monthlySummary']);
        Route::post('attendance/bulk-checkin',      [AttendanceController::class, 'bulkCheckIn']);
        Route::apiResource('attendance', AttendanceController::class)
            ->only(['index', 'show', 'store', 'update']);

        // Purchase Requests — budget-stats before {id} to avoid routing collision
        Route::get('purchase-requests/budget-stats',      BudgetStatsController::class);
        Route::apiResource('purchase-requests', PurchaseRequestController::class)
            ->only(['index', 'show', 'store', 'destroy']);

        Route::post('purchase-requests/{id}/approve', ApprovePurchaseController::class);
        Route::post('purchase-requests/{id}/reject',  RejectPurchaseController::class);

        // Payroll
        Route::apiResource('payroll-runs', PayrollController::class)
            ->only(['index', 'show', 'store']);

        // Approvals Center — pending-count before index to avoid segment collision
        Route::get('approvals/pending-count', [ApprovalsController::class, 'pendingCount']);
        Route::get('approvals',               [ApprovalsController::class, 'index']);

        // Dashboard
        Route::get('dashboard', DashboardController::class);

        // Activity Log
        Route::get('activity-log', ActivityLogController::class);
    });
});
