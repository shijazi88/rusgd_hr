<?php

use App\Models\Employee;

describe('Dashboard', function () {

    it('returns dashboard stats for authorised user', function () {
        Employee::factory()->active()->count(3)->create();
        asUser(['view_employees']);

        $res = getJson('/api/v1/dashboard')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'data' => [
                    'total_active_employees',
                    'pending_requests',
                    'attendance_rate',
                    'employees_by_department',
                    'request_stats',
                ],
            ]);

        expect($res->json('data.total_active_employees'))->toBeGreaterThanOrEqual(3);
    });

    it('returns 401 when unauthenticated', function () {
        getJson('/api/v1/dashboard')->assertUnauthorized();
    });

    it('returns 403 without view_employees', function () {
        asUser([]);
        getJson('/api/v1/dashboard')->assertForbidden();
    });

    it('returns activity log endpoint', function () {
        asUser(['view_audit_logs']);
        getJson('/api/v1/activity-log')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data', 'meta']);
    });

    it('returns 403 on activity-log without view_audit_logs', function () {
        asUser([]);
        getJson('/api/v1/activity-log')->assertForbidden();
    });
});
