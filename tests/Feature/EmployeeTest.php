<?php

use App\Models\Department;
use App\Models\Employee;

describe('Employees', function () {

    // ── Index ────────────────────────────────────────────────────────────

    it('lists employees for users with view_employees permission', function () {
        Employee::factory()->count(3)->create();
        asUser(['view_employees']);

        getJson('/api/v1/employees')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data', 'meta']);
    });

    it('returns 401 when unauthenticated on employee list', function () {
        getJson('/api/v1/employees')->assertUnauthorized();
    });

    it('returns 403 when user lacks view_employees', function () {
        asUser([]);  // no permissions
        getJson('/api/v1/employees')->assertForbidden();
    });

    it('filters employees by status', function () {
        Employee::factory()->active()->create();
        Employee::factory()->terminated()->create();
        asUser(['view_employees']);

        $res = getJson('/api/v1/employees?status=active')->assertOk();
        expect(collect($res->json('data'))->every(fn ($e) => $e['status'] === 'active'))->toBeTrue();
    });

    // ── Show ─────────────────────────────────────────────────────────────

    it('shows a single employee', function () {
        $emp = Employee::factory()->create();
        asUser(['view_employees']);

        getJson("/api/v1/employees/{$emp->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $emp->id);
    });

    it('returns 404 for non-existent employee', function () {
        asUser(['view_employees']);
        getJson('/api/v1/employees/999999')->assertNotFound();
    });

    // ── Store ─────────────────────────────────────────────────────────────

    it('creates an employee with valid data', function () {
        $dept = Department::factory()->create();
        asUser(['edit_employees']);

        postJson('/api/v1/employees', [
            'name'          => 'محمد اختبار',
            'email'         => 'test_new@rushdai.com',
            'department_id' => $dept->id,
            'job_title'     => 'Developer',
            'contract_type' => 'permanent',
            'hire_date'     => '2024-01-01',
            'base_salary'   => 12000,
        ])
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'محمد اختبار');

        expect(Employee::where('email', 'test_new@rushdai.com')->exists())->toBeTrue();
    });

    it('validates required fields on create', function () {
        asUser(['edit_employees']);
        postJson('/api/v1/employees', [])->assertUnprocessable();
    });

    it('rejects duplicate email', function () {
        $existing = Employee::factory()->create(['email' => 'dup@rushdai.com']);
        $dept     = Department::factory()->create();
        asUser(['edit_employees']);

        postJson('/api/v1/employees', [
            'name'          => 'Dup',
            'email'         => 'dup@rushdai.com',
            'department_id' => $dept->id,
            'job_title'     => 'Dev',
            'contract_type' => 'permanent',
            'hire_date'     => '2024-01-01',
        ])->assertUnprocessable();
    });

    it('returns 403 when creating without edit_employees', function () {
        $dept = Department::factory()->create();
        asUser(['view_employees']);

        postJson('/api/v1/employees', [
            'name'          => 'X',
            'email'         => 'x@x.com',
            'department_id' => $dept->id,
            'job_title'     => 'X',
            'contract_type' => 'permanent',
            'hire_date'     => '2024-01-01',
        ])->assertForbidden();
    });

    // ── Update ────────────────────────────────────────────────────────────

    it('updates an employee', function () {
        $emp = Employee::factory()->create();
        asUser(['edit_employees']);

        putJson("/api/v1/employees/{$emp->id}", ['job_title' => 'Senior Dev'])
            ->assertOk()
            ->assertJsonPath('data.job_title', 'Senior Dev');
    });

    // ── Destroy ───────────────────────────────────────────────────────────

    it('soft-deletes an employee', function () {
        $emp = Employee::factory()->create();
        asUser(['delete_employees']);

        deleteJson("/api/v1/employees/{$emp->id}")->assertOk();

        expect(Employee::find($emp->id))->toBeNull();
        expect(Employee::withTrashed()->find($emp->id))->not->toBeNull();
    });

    it('returns 403 when deleting without delete_employees', function () {
        $emp = Employee::factory()->create();
        asUser(['view_employees']);

        deleteJson("/api/v1/employees/{$emp->id}")->assertForbidden();
    });
});
