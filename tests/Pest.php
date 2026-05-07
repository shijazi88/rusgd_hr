<?php

use App\Models\Department;
use App\Models\Employee;
use App\Models\LeaveBalance;
use App\Models\LeaveType;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

/*
|--------------------------------------------------------------------------
| Test Case & Traits
|--------------------------------------------------------------------------
*/

uses(Tests\TestCase::class, RefreshDatabase::class)->in('Feature');
uses(Tests\TestCase::class)->in('Unit');

/*
|--------------------------------------------------------------------------
| Global Helpers
|--------------------------------------------------------------------------
*/

/**
 * Create an Employee + User with the given permission slugs and act as that user.
 */
function userWith(array $permissionSlugs = []): User
{
    $dept = Department::factory()->create();
    $emp  = Employee::factory()->create(['department_id' => $dept->id]);
    $user = User::factory()->create([
        'employee_id' => $emp->id,
        'name'        => 'Test User',
        'email'       => 'test_' . uniqid() . '@rushdai.com',
    ]);

    if (!empty($permissionSlugs)) {
        $role = Role::create([
            'name'             => 'Test Role',
            'slug'             => 'test_' . uniqid(),
            'financial_limit'  => 999999,
            'leave_limit_days' => 365,
        ]);

        foreach ($permissionSlugs as $slug) {
            $perm = Permission::firstOrCreate(['slug' => $slug], ['name' => $slug]);
            $role->permissions()->attach($perm->id);
        }

        $user->roleModels()->attach($role->id);
    }

    return $user;
}

/**
 * Create a user with permissions and set it as the active Sanctum actor.
 */
function asUser(array $permissionSlugs = []): User
{
    $user = userWith($permissionSlugs);
    Sanctum::actingAs($user);
    return $user;
}

/**
 * Seed a LeaveBalance so that approve/deduct operations succeed.
 */
function seedLeaveBalance(Employee $employee, LeaveType $leaveType, int $totalDays = 21): LeaveBalance
{
    return LeaveBalance::create([
        'employee_id'   => $employee->id,
        'leave_type_id' => $leaveType->id,
        'year'          => now()->year,
        'total_days'    => $totalDays,
        'used_days'     => 0,
    ]);
}

/*
|--------------------------------------------------------------------------
| HTTP helpers — forward to Pest\Laravel namespace functions
| (pest-plugin-laravel defines these in Pest\Laravel, not global scope)
|--------------------------------------------------------------------------
*/

function getJson(string $uri, array $headers = []): Illuminate\Testing\TestResponse
{
    return \Pest\Laravel\getJson($uri, $headers);
}

function postJson(string $uri, array $data = [], array $headers = []): Illuminate\Testing\TestResponse
{
    return \Pest\Laravel\postJson($uri, $data, $headers);
}

function putJson(string $uri, array $data = [], array $headers = []): Illuminate\Testing\TestResponse
{
    return \Pest\Laravel\putJson($uri, $data, $headers);
}

function patchJson(string $uri, array $data = [], array $headers = []): Illuminate\Testing\TestResponse
{
    return \Pest\Laravel\patchJson($uri, $data, $headers);
}

function deleteJson(string $uri, array $data = [], array $headers = []): Illuminate\Testing\TestResponse
{
    return \Pest\Laravel\deleteJson($uri, $data, $headers);
}
