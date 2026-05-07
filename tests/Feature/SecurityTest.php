<?php

use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\PayrollRun;
use App\Models\PurchaseRequest;
use App\Models\Role;

describe('Security — unauthenticated requests return 401', function () {

    $protectedEndpoints = [
        ['GET',  '/api/v1/employees'],
        ['GET',  '/api/v1/departments'],
        ['GET',  '/api/v1/leave-requests'],
        ['GET',  '/api/v1/attendance'],
        ['GET',  '/api/v1/shifts'],
        ['GET',  '/api/v1/purchase-requests'],
        ['GET',  '/api/v1/purchase-requests/budget-stats'],
        ['GET',  '/api/v1/payroll-runs'],
        ['GET',  '/api/v1/roles'],
        ['GET',  '/api/v1/permissions'],
        ['GET',  '/api/v1/approval-rules'],
        ['GET',  '/api/v1/approvals'],
        ['GET',  '/api/v1/approvals/pending-count'],
        ['GET',  '/api/v1/dashboard'],
        ['GET',  '/api/v1/activity-log'],
        ['GET',  '/api/v1/org-chart'],
    ];

    foreach ($protectedEndpoints as [$method, $uri]) {
        it("blocks {$method} {$uri} when unauthenticated", function () use ($method, $uri) {
            $this->json($method, $uri)->assertUnauthorized();
        });
    }
});

describe('Security — low-privilege user receives 403', function () {

    it('blocks employee list for user with no permissions', function () {
        asUser([]);
        getJson('/api/v1/employees')->assertForbidden();
    });

    it('blocks payroll run for non-finance user', function () {
        asUser(['view_employees']);
        postJson('/api/v1/payroll-runs', ['month' => 1, 'year' => 2024])->assertForbidden();
    });

    it('blocks leave approval for regular employee', function () {
        $leave = LeaveRequest::factory()->pending()->create();
        asUser([]);  // no approve_leaves

        postJson("/api/v1/leave-requests/{$leave->id}/approve")->assertForbidden();
    });

    it('blocks purchase approval for regular employee', function () {
        $pr = PurchaseRequest::factory()->pending()->create();
        asUser([]);

        postJson("/api/v1/purchase-requests/{$pr->id}/approve")->assertForbidden();
    });

    it('blocks role management for non-admin', function () {
        asUser(['view_employees']);
        getJson('/api/v1/roles')->assertForbidden();
    });

    it('blocks activity log for user without view_audit_logs', function () {
        asUser(['view_employees']);
        getJson('/api/v1/activity-log')->assertForbidden();
    });

    it('blocks approvals center for user with no approver permissions', function () {
        asUser([]);
        getJson('/api/v1/approvals')->assertForbidden();
    });
});

describe('Security — API response format', function () {

    it('returns success:false on 401', function () {
        getJson('/api/v1/employees')
            ->assertUnauthorized()
            ->assertJsonPath('success', false);
    });

    it('returns success:false on 403', function () {
        asUser([]);
        getJson('/api/v1/employees')
            ->assertForbidden()
            ->assertJsonPath('success', false);
    });

    it('returns success:false on 404', function () {
        asUser(['view_employees']);
        getJson('/api/v1/employees/999999')
            ->assertNotFound()
            ->assertJsonPath('success', false);
    });

    it('returns success:false on 422', function () {
        asUser(['edit_employees']);
        postJson('/api/v1/employees', [])
            ->assertUnprocessable()
            ->assertJsonPath('success', false);
    });
});
