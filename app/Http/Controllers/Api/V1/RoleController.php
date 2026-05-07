<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Role\StoreRoleRequest;
use App\Http\Requests\Role\UpdateRoleRequest;
use App\Http\Resources\RoleResource;
use App\Models\Role;
use App\Services\RoleService;
use App\Support\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class RoleController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly RoleService $roleService) {}

    public function index(): JsonResponse
    {
        $this->authorize('viewAny', Role::class);

        return $this->success(RoleResource::collection($this->roleService->getAll()));
    }

    public function show(int $id): JsonResponse
    {
        $role = $this->roleService->getById($id);
        $this->authorize('view', $role);

        return $this->success(RoleResource::make($role));
    }

    public function store(StoreRoleRequest $request): JsonResponse
    {
        $this->authorize('create', Role::class);

        $role = $this->roleService->create($request->validated());

        return $this->created(RoleResource::make($role), 'تم إنشاء الدور بنجاح.');
    }

    public function update(UpdateRoleRequest $request, int $id): JsonResponse
    {
        $role = $this->roleService->getById($id);
        $this->authorize('update', $role);

        $updated = $this->roleService->update($role, $request->validated());

        return $this->success(RoleResource::make($updated), 'تم تحديث الدور بنجاح.');
    }

    public function destroy(int $id): JsonResponse
    {
        $role = $this->roleService->getById($id);
        $this->authorize('delete', $role);

        $this->roleService->delete($role);

        return $this->success(message: 'تم حذف الدور بنجاح.');
    }
}
