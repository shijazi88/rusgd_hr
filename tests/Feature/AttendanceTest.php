<?php

use App\Models\Employee;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;

describe('Attendance', function () {

    // ── Index ─────────────────────────────────────────────────────────────

    it('lists attendance records', function () {
        asUser(['view_employees']);
        getJson('/api/v1/attendance')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data', 'meta']);
    });

    it('returns 401 when unauthenticated', function () {
        getJson('/api/v1/attendance')->assertUnauthorized();
    });

    // ── Check-in (store) ──────────────────────────────────────────────────
    // Requires manage_shifts permission. date=Y-m-d, check_in=H:i

    it('records a check-in', function () {
        $user = userWith(['manage_shifts']);
        $emp  = $user->employee;
        Sanctum::actingAs($user);

        postJson('/api/v1/attendance', [
            'employee_id' => $emp->id,
            'date'        => today()->toDateString(),
            'check_in'    => '08:00',
            'status'      => 'present',
        ])
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'present');

        expect(DB::table('attendance')->where('employee_id', $emp->id)->exists())->toBeTrue();
    });

    it('upserts when a record already exists for the same employee and date', function () {
        // manualRecord() updates the existing row rather than rejecting — this is the designed behaviour
        $user = userWith(['manage_shifts']);
        $emp  = $user->employee;
        Sanctum::actingAs($user);

        $payload = [
            'employee_id' => $emp->id,
            'date'        => today()->toDateString(),
            'check_in'    => '08:00',
            'status'      => 'present',
        ];

        postJson('/api/v1/attendance', $payload)->assertCreated();

        // Second call updates the existing record — still returns 201 with new status
        postJson('/api/v1/attendance', array_merge($payload, ['check_in' => '09:00', 'status' => 'late']))
            ->assertCreated()
            ->assertJsonPath('data.status', 'late');
    });

    // ── Update (check-out) ────────────────────────────────────────────────
    // Requires manage_shifts. check_out format = H:i

    it('records a check-out and calculates work hours', function () {
        $user = userWith(['manage_shifts']);
        $emp  = $user->employee;
        Sanctum::actingAs($user);

        $id = DB::table('attendance')->insertGetId([
            'employee_id'  => $emp->id,
            'date'         => today()->toDateString(),
            'check_in'     => '08:00:00',
            'check_out'    => null,
            'status'       => 'present',
            'late_minutes' => 0,
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        putJson("/api/v1/attendance/{$id}", ['check_out' => '17:00'])
            ->assertOk()
            ->assertJsonPath('success', true);

        $record = DB::table('attendance')->find($id);
        expect((float) $record->work_hours)->toBeGreaterThan(0);
    });

    // ── Monthly Summary ───────────────────────────────────────────────────

    it('returns monthly summary for an employee', function () {
        $emp = Employee::factory()->create();
        asUser(['view_employees']);

        getJson("/api/v1/attendance/monthly-summary?employee_id={$emp->id}&month=1&year=2024")
            ->assertOk()
            ->assertJsonPath('success', true);
    });
});
