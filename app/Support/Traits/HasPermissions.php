<?php

namespace App\Support\Traits;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

trait HasPermissions
{
    public function permissions(): Collection
    {
        return Cache::remember(
            "user.{$this->id}.permissions",
            now()->addHours(24),
            fn () => DB::table('role_permissions')
                ->join('user_roles', 'user_roles.role_id', '=', 'role_permissions.role_id')
                ->join('permissions', 'permissions.id', '=', 'role_permissions.permission_id')
                ->where('user_roles.user_id', $this->id)
                ->pluck('permissions.slug')
        );
    }

    public function roles(): Collection
    {
        return Cache::remember(
            "user.{$this->id}.roles",
            now()->addHours(24),
            fn () => DB::table('roles')
                ->join('user_roles', 'user_roles.role_id', '=', 'roles.id')
                ->where('user_roles.user_id', $this->id)
                ->select('roles.*')
                ->get()
        );
    }

    public function hasPermissionTo(string $slug): bool
    {
        return $this->permissions()->contains($slug);
    }

    public function hasRole(string $slug): bool
    {
        return $this->roles()->pluck('slug')->contains($slug);
    }

    public function clearPermissionCache(): void
    {
        Cache::forget("user.{$this->id}.permissions");
        Cache::forget("user.{$this->id}.roles");
    }
}
