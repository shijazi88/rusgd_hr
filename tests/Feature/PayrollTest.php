<?php

use App\Enums\PayrollStatus;
use App\Models\Employee;

describe('Payroll', function () {

    // ── Index ─────────────────────────────────────────────────────────────

    it('lists payroll run history', function () {
        asUser(['view_payroll']);
        getJson('/api/v1/payroll-runs')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data', 'meta']);
    });

    it('returns 401 when unauthenticated', function () {
        getJson('/api/v1/payroll-runs')->assertUnauthorized();
    });

    it('returns 403 without view_payroll', function () {
        asUser([]);
        getJson('/api/v1/payroll-runs')->assertForbidden();
    });

    // ── Store (run payroll) ───────────────────────────────────────────────

    it('runs payroll and creates items for active employees only', function () {
        Employee::factory()->active()->count(3)->create();
        Employee::factory()->terminated()->create();
        asUser(['run_payroll']); // creates 1 more active employee via userWith()

        // Count ALL active employees after setup (including the one from asUser)
        $expectedCount = Employee::active()->count();

        $res = postJson('/api/v1/payroll-runs', [
            'month' => now()->month,
            'year'  => now()->year,
        ])
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', PayrollStatus::Completed->value);

        expect($res->json('data.items_count'))->toBe($expectedCount);
    });

    it('prevents duplicate payroll run for the same month/year', function () {
        Employee::factory()->active()->create();
        asUser(['run_payroll']);

        $month = now()->month;
        $year  = now()->year;

        postJson('/api/v1/payroll-runs', compact('month', 'year'))->assertCreated();
        postJson('/api/v1/payroll-runs', compact('month', 'year'))->assertStatus(422);
    });

    it('validates month range', function () {
        asUser(['run_payroll']);

        postJson('/api/v1/payroll-runs', ['month' => 13, 'year' => 2025])
            ->assertUnprocessable();

        postJson('/api/v1/payroll-runs', ['month' => 0, 'year' => 2025])
            ->assertUnprocessable();
    });

    it('returns 403 without run_payroll permission', function () {
        asUser(['view_payroll']);
        postJson('/api/v1/payroll-runs', ['month' => 1, 'year' => 2025])->assertForbidden();
    });

    // ── Show ──────────────────────────────────────────────────────────────

    it('shows a payroll run with items', function () {
        Employee::factory()->active()->create();
        asUser(['run_payroll', 'view_payroll']);

        $res = postJson('/api/v1/payroll-runs', ['month' => 1, 'year' => 2024])->assertCreated();
        $id  = $res->json('data.id');

        getJson("/api/v1/payroll-runs/{$id}")
            ->assertOk()
            ->assertJsonPath('data.id', $id)
            ->assertJsonStructure(['data' => ['items']]);
    });

    it('returns 404 for non-existent run', function () {
        asUser(['view_payroll']);
        getJson('/api/v1/payroll-runs/999999')->assertNotFound();
    });

    // ── Total amount accuracy ─────────────────────────────────────────────

    it('calculates the correct total amount across all active employees', function () {
        // Create one extra active employee with known salary
        Employee::factory()->create([
            'base_salary'         => 10000,
            'housing_allowance'   => 3000,
            'transport_allowance' => 800,
            'status'              => 'active',
        ]);
        asUser(['run_payroll']); // also creates an active employee via userWith()

        // Calculate expected total from ALL active employees
        $expected = (float) \Illuminate\Support\Facades\DB::table('employees')
            ->where('status', 'active')
            ->whereNull('deleted_at')
            ->selectRaw('SUM(base_salary + housing_allowance + transport_allowance) as total')
            ->value('total');

        $res = postJson('/api/v1/payroll-runs', ['month' => 3, 'year' => 2024])
            ->assertCreated();

        expect((float) $res->json('data.total_amount'))->toBe(round($expected, 2));
    });
});
