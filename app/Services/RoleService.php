<?php

namespace App\Services;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class RoleService
{
    public function getAll(): Collection
    {
        return Role::with('permissions')->get();
    }

    public function getById(int $id): Role
    {
        $role = Role::with('permissions')->find($id);

        if (!$role) {
            abort(404, 'الدور غير موجود.');
        }

        return $role;
    }

    public function create(array $data): Role
    {
        $role = Role::create($data);

        if (!empty($data['permission_ids'])) {
            $role->permissions()->sync($data['permission_ids']);
        }

        return $role->load('permissions');
    }

    public function update(Role $role, array $data): Role
    {
        $role->update($data);

        if (\array_key_exists('permission_ids', $data)) {
            $role->permissions()->sync($data['permission_ids']);
            // Bust permission cache for all users that hold this role
            User::whereHas('roleModels', fn ($q) => $q->where('roles.id', $role->id))
                ->each(fn (User $user) => $user->clearPermissionCache());
        }

        return $role->fresh('permissions');
    }

    public function syncPermissions(Role $role, array $permissionIds): Role
    {
        $role->permissions()->sync($permissionIds);

        User::whereHas('roleModels', fn ($q) => $q->where('roles.id', $role->id))
            ->each(fn (User $user) => $user->clearPermissionCache());

        return $role->fresh('permissions');
    }

    public function delete(Role $role): void
    {
        // Bust cache for all affected users before detaching
        User::whereHas('roleModels', fn ($q) => $q->where('roles.id', $role->id))
            ->each(fn (User $user) => $user->clearPermissionCache());

        $role->delete();
    }

    public function assignToUser(User $user, array $roleIds): void
    {
        $user->roleModels()->sync($roleIds);
        $user->clearPermissionCache();
    }
}
