<?php

use App\Models\LeaveRequest;
use App\Models\PurchaseRequest;

describe('Approvals Center', function () {

    // ── Pending Count ─────────────────────────────────────────────────────

    it('returns pending count for approver', function () {
        LeaveRequest::factory()->pending()->count(2)->create();
        PurchaseRequest::factory()->pending()->count(3)->create();

        asUser(['approve_leaves', 'approve_purchases']);

        $res = getJson('/api/v1/approvals/pending-count')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data' => ['leaves', 'purchases', 'total']]);

        expect($res->json('data.leaves'))->toBeGreaterThanOrEqual(2);
        expect($res->json('data.purchases'))->toBeGreaterThanOrEqual(3);
    });

    it('returns 403 on pending-count without approver permissions', function () {
        asUser([]);
        getJson('/api/v1/approvals/pending-count')->assertForbidden();
    });

    it('returns 401 when unauthenticated on pending-count', function () {
        getJson('/api/v1/approvals/pending-count')->assertUnauthorized();
    });

    // ── Unified List ──────────────────────────────────────────────────────

    it('lists approvals for user with approve_leaves', function () {
        LeaveRequest::factory()->pending()->count(2)->create();
        asUser(['approve_leaves']);

        getJson('/api/v1/approvals')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data', 'meta']);
    });

    it('lists approvals for user with approve_purchases', function () {
        PurchaseRequest::factory()->pending()->count(2)->create();
        asUser(['approve_purchases']);

        getJson('/api/v1/approvals')
            ->assertOk()
            ->assertJsonPath('success', true);
    });

    it('filters approvals by status', function () {
        LeaveRequest::factory()->pending()->count(2)->create();
        LeaveRequest::factory()->approved()->count(1)->create();
        asUser(['approve_leaves']);

        $res = getJson('/api/v1/approvals?status=pending')->assertOk();
        $items = collect($res->json('data'));
        expect($items->every(fn ($i) => $i['status'] === 'pending'))->toBeTrue();
    });

    it('returns all statuses with status=all', function () {
        LeaveRequest::factory()->pending()->create();
        LeaveRequest::factory()->approved()->create();
        LeaveRequest::factory()->rejected()->create();
        asUser(['approve_leaves']);

        $res = getJson('/api/v1/approvals?status=all')->assertOk();
        expect($res->json('meta.total'))->toBeGreaterThanOrEqual(3);
    });

    it('returns 403 without approver permissions', function () {
        asUser([]);
        getJson('/api/v1/approvals')->assertForbidden();
    });

    it('returns 401 when unauthenticated on approvals list', function () {
        getJson('/api/v1/approvals')->assertUnauthorized();
    });
});
