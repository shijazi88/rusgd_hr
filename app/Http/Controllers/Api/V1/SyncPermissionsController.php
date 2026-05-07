<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Role\SyncPermissionsRequest;
use App\Http\Resources\RoleResource;
use App\Services\RoleService;
use App\Support\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class SyncPermissionsController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly RoleService $roleService) {}

    public function __invoke(SyncPermissionsRequest $request, int $id): JsonResponse
    {
        $role = $this->roleService->getById($id);
        $this->authorize('update', $role);

        $updated = $this->roleService->syncPermissions($role, $request->validated('permission_ids'));

        return $this->success(RoleResource::make($updated), 'تم تحديث صلاحيات الدور بنجاح.');
    }
}
