<?php

use App\Enums\PurchaseStatus;
use App\Models\PurchaseRequest;
use Laravel\Sanctum\Sanctum;

describe('Purchase Requests', function () {

    // ── Index ─────────────────────────────────────────────────────────────

    it('lists purchase requests', function () {
        PurchaseRequest::factory()->count(3)->create();
        asUser(['view_employees']);

        getJson('/api/v1/purchase-requests')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data', 'meta']);
    });

    it('returns 401 when unauthenticated', function () {
        getJson('/api/v1/purchase-requests')->assertUnauthorized();
    });

    // ── Budget Stats ──────────────────────────────────────────────────────
    // Response: { data: { approved: {count, total}, pending: {count, total}, ... } }

    it('returns budget stats for current month', function () {
        PurchaseRequest::factory()->approved()->create(['amount' => 5000]);
        PurchaseRequest::factory()->pending()->create(['amount' => 2000]);
        asUser(['view_employees']);

        $res = getJson('/api/v1/purchase-requests/budget-stats')
            ->assertOk()
            ->assertJsonPath('success', true);

        expect($res->json('data.approved.total'))->toBeGreaterThanOrEqual(5000);
        expect($res->json('data.pending.total'))->toBeGreaterThanOrEqual(2000);
    });

    // ── Store ─────────────────────────────────────────────────────────────

    it('creates a purchase request', function () {
        $user = asUser([]);
        $emp  = $user->employee;

        postJson('/api/v1/purchase-requests', [
            'employee_id' => $emp->id,
            'item_name'   => 'طابعة ليزر',
            'quantity'    => 1,
            'amount'      => 1500.00,
            'reason'      => 'للطباعة اليومية',
        ])
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'pending');

        expect(PurchaseRequest::where('employee_id', $emp->id)->exists())->toBeTrue();
    });

    it('validates required fields', function () {
        asUser([]);
        postJson('/api/v1/purchase-requests', [])->assertUnprocessable();
    });

    it('requires a positive amount', function () {
        $user = asUser([]);

        postJson('/api/v1/purchase-requests', [
            'employee_id' => $user->employee->id,
            'item_name'   => 'شيء ما',
            'amount'      => 0,
            'reason'      => 'سبب',
        ])->assertUnprocessable();
    });

    // ── Approve ───────────────────────────────────────────────────────────

    it('approves a pending purchase request', function () {
        $pr = PurchaseRequest::factory()->pending()->create();
        asUser(['approve_purchases']);

        postJson("/api/v1/purchase-requests/{$pr->id}/approve")
            ->assertOk()
            ->assertJsonPath('data.status', 'approved');

        expect($pr->fresh()->status)->toBe(PurchaseStatus::Approved);
    });

    it('returns 403 when approving without approve_purchases', function () {
        $pr = PurchaseRequest::factory()->pending()->create();
        asUser(['view_employees']);

        postJson("/api/v1/purchase-requests/{$pr->id}/approve")->assertForbidden();
    });

    it('cannot approve an already-approved request', function () {
        $pr = PurchaseRequest::factory()->approved()->create();
        asUser(['approve_purchases']);

        postJson("/api/v1/purchase-requests/{$pr->id}/approve")->assertForbidden();
    });

    // ── Reject ────────────────────────────────────────────────────────────

    it('rejects a purchase request with a reason', function () {
        $pr = PurchaseRequest::factory()->pending()->create();
        asUser(['approve_purchases']);

        postJson("/api/v1/purchase-requests/{$pr->id}/reject", ['reason' => 'تجاوز الميزانية'])
            ->assertOk()
            ->assertJsonPath('data.status', 'rejected');

        expect($pr->fresh()->rejection_reason)->toBe('تجاوز الميزانية');
    });

    it('requires reason when rejecting', function () {
        $pr = PurchaseRequest::factory()->pending()->create();
        asUser(['approve_purchases']);

        postJson("/api/v1/purchase-requests/{$pr->id}/reject", [])->assertUnprocessable();
    });

    // ── Destroy ───────────────────────────────────────────────────────────
    // Policy: only the request owner can cancel, and only if still pending

    it('lets the owner cancel their own pending request', function () {
        $user = userWith([]);
        $emp  = $user->employee;
        $pr   = PurchaseRequest::factory()->pending()->create(['employee_id' => $emp->id]);
        Sanctum::actingAs($user);

        deleteJson("/api/v1/purchase-requests/{$pr->id}")->assertOk();
        expect(PurchaseRequest::find($pr->id))->toBeNull();
    });

    it('returns 403 when a different user tries to cancel someone else\'s request', function () {
        $pr = PurchaseRequest::factory()->pending()->create(); // random employee
        asUser([]);                                            // different user

        deleteJson("/api/v1/purchase-requests/{$pr->id}")->assertForbidden();
    });
});
