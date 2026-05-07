<?php

use App\Models\Employee;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

describe('Roles & Permissions', function () {

    // ── Roles Index ───────────────────────────────────────────────────────

    it('lists roles', function () {
        Role::factory()->count(3)->create();
        asUser(['manage_roles']);

        getJson('/api/v1/roles')
            ->assertOk()
            ->assertJsonPath('success', true);
    });

    it('returns 401 when unauthenticated', function () {
        getJson('/api/v1/roles')->assertUnauthorized();
    });

    it('returns 403 without manage_roles', function () {
        asUser([]);
        getJson('/api/v1/roles')->assertForbidden();
    });

    // ── Create Role ───────────────────────────────────────────────────────

    it('creates a role with permissions', function () {
        $perm = Permission::create(['slug' => 'view_employees', 'name' => 'عرض الموظفين']);
        asUser(['manage_roles']);

        postJson('/api/v1/roles', [
            'name'           => 'مدير اختبار',
            'slug'           => 'test_manager',
            'permission_ids' => [$perm->id],
        ])
            ->assertCreated()
            ->assertJsonPath('data.name', 'مدير اختبار');

        $role = Role::where('slug', 'test_manager')->first();
        expect($role->permissions()->where('slug', 'view_employees')->exists())->toBeTrue();
    });

    it('validates required slug', function () {
        asUser(['manage_roles']);
        postJson('/api/v1/roles', ['name' => 'بدون معرف'])->assertUnprocessable();
    });

    it('rejects duplicate slug', function () {
        Role::create(['name' => 'Existing', 'slug' => 'existing_slug']);
        asUser(['manage_roles']);

        postJson('/api/v1/roles', ['name' => 'New', 'slug' => 'existing_slug'])
            ->assertUnprocessable();
    });

    // ── Update Role ───────────────────────────────────────────────────────

    it('updates a role', function () {
        $role = Role::factory()->create();
        asUser(['manage_roles']);

        putJson("/api/v1/roles/{$role->id}", ['name' => 'اسم محدث'])
            ->assertOk()
            ->assertJsonPath('data.name', 'اسم محدث');
    });

    // ── Sync Permissions ──────────────────────────────────────────────────

    it('syncs permissions on a role', function () {
        $role  = Role::factory()->create();
        $perm1 = Permission::create(['slug' => 'view_employees', 'name' => 'عرض']);
        $perm2 = Permission::create(['slug' => 'edit_employees', 'name' => 'تعديل']);
        asUser(['manage_roles']);

        postJson("/api/v1/roles/{$role->id}/sync-permissions", [
            'permission_ids' => [$perm1->id, $perm2->id],
        ])->assertOk();

        expect($role->permissions()->count())->toBe(2);
    });

    // ── Permissions list ──────────────────────────────────────────────────

    it('lists all permissions', function () {
        Permission::create(['slug' => 'view_employees', 'name' => 'عرض الموظفين']);
        asUser(['manage_roles']);

        getJson('/api/v1/permissions')
            ->assertOk()
            ->assertJsonPath('success', true);
    });

    // ── Assign Role to Employee ───────────────────────────────────────────

    it('assigns a role to an employee', function () {
        $emp  = Employee::factory()->create();
        $user = User::factory()->create(['employee_id' => $emp->id]);
        $role = Role::factory()->create();

        asUser(['manage_roles']);

        postJson("/api/v1/employees/{$emp->id}/roles", ['role_ids' => [$role->id]])
            ->assertOk();

        expect($user->roleModels()->where('roles.id', $role->id)->exists())->toBeTrue();
    });

    // ── Delete Role ───────────────────────────────────────────────────────

    it('deletes a role', function () {
        $role = Role::factory()->create();
        asUser(['manage_roles']);

        deleteJson("/api/v1/roles/{$role->id}")->assertOk();
        expect(Role::find($role->id))->toBeNull();
    });
});
