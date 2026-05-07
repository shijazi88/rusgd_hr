<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Role\AssignRoleRequest;
use App\Models\Employee;
use App\Models\Role;
use App\Services\RoleService;
use App\Support\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class AssignRoleController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly RoleService $roleService) {}

    public function __invoke(AssignRoleRequest $request, int $employeeId): JsonResponse
    {
        $this->authorize('create', Role::class);

        $employee = Employee::findOrFail($employeeId);
        $user     = $employee->user;

        if (!$user) {
            return $this->error('هذا الموظف لا يملك حساب مستخدم.', 422);
        }

        $this->roleService->assignToUser($user, $request->validated('role_ids'));

        return $this->success(message: 'تم تعيين الأدوار للموظف بنجاح.');
    }
}
