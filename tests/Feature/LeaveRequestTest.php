<?php

use App\Enums\LeaveStatus;
use App\Models\Employee;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

describe('Leave Requests', function () {

    // ── Index ─────────────────────────────────────────────────────────────

    it('lists leave requests for approver', function () {
        LeaveRequest::factory()->count(2)->create();
        asUser(['view_employees']);

        getJson('/api/v1/leave-requests')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data', 'meta']);
    });

    it('returns 401 when unauthenticated', function () {
        getJson('/api/v1/leave-requests')->assertUnauthorized();
    });

    // ── Store ─────────────────────────────────────────────────────────────

    it('creates a leave request with valid data', function () {
        $leaveType = LeaveType::factory()->create();
        $user      = asUser([]);  // any authenticated user can submit
        $emp       = $user->employee;
        seedLeaveBalance($emp, $leaveType);

        $from = now()->addDay()->toDateString();
        $to   = now()->addDays(3)->toDateString();

        postJson('/api/v1/leave-requests', [
            'employee_id'   => $emp->id,
            'leave_type_id' => $leaveType->id,
            'from_date'     => $from,
            'to_date'       => $to,
            'reason'        => 'استراحة',
        ])
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'pending');

        expect(LeaveRequest::where('employee_id', $emp->id)->exists())->toBeTrue();
    });

    it('rejects past from_date', function () {
        $leaveType = LeaveType::factory()->create();
        $user      = asUser([]);
        $emp       = $user->employee;

        postJson('/api/v1/leave-requests', [
            'employee_id'   => $emp->id,
            'leave_type_id' => $leaveType->id,
            'from_date'     => now()->subDay()->toDateString(),
            'to_date'       => now()->addDay()->toDateString(),
        ])->assertUnprocessable();
    });

    it('rejects to_date before from_date', function () {
        $leaveType = LeaveType::factory()->create();
        $user      = asUser([]);
        $emp       = $user->employee;

        postJson('/api/v1/leave-requests', [
            'employee_id'   => $emp->id,
            'leave_type_id' => $leaveType->id,
            'from_date'     => now()->addDays(5)->toDateString(),
            'to_date'       => now()->addDays(2)->toDateString(),
        ])->assertUnprocessable();
    });

    it('rejects request when balance is insufficient', function () {
        $leaveType = LeaveType::factory()->create(['max_days_per_year' => 5]);
        $user      = asUser([]);
        $emp       = $user->employee;

        // Balance fully used
        seedLeaveBalance($emp, $leaveType, 5);
        LeaveBalance::where('employee_id', $emp->id)->update(['used_days' => 5]);

        postJson('/api/v1/leave-requests', [
            'employee_id'   => $emp->id,
            'leave_type_id' => $leaveType->id,
            'from_date'     => now()->addDay()->toDateString(),
            'to_date'       => now()->addDays(3)->toDateString(),
        ])->assertStatus(422);
    });

    // ── Approve ───────────────────────────────────────────────────────────

    it('approves a pending leave request', function () {
        $leaveType = LeaveType::factory()->create();
        $requester = Employee::factory()->create();
        seedLeaveBalance($requester, $leaveType, 21);

        $leave = LeaveRequest::factory()->create([
            'employee_id'   => $requester->id,
            'leave_type_id' => $leaveType->id,
            'status'        => LeaveStatus::Pending->value,
            'days'          => 3,
        ]);

        asUser(['approve_leaves']);

        postJson("/api/v1/leave-requests/{$leave->id}/approve")
            ->assertOk()
            ->assertJsonPath('data.status', 'approved');

        expect($leave->fresh()->status)->toBe(LeaveStatus::Approved);
    });

    it('returns 403 when approving without approve_leaves', function () {
        $leave = LeaveRequest::factory()->create(['status' => LeaveStatus::Pending->value]);
        asUser(['view_employees']);

        postJson("/api/v1/leave-requests/{$leave->id}/approve")->assertForbidden();
    });

    it('cannot approve an already-approved request', function () {
        $leave = LeaveRequest::factory()->create(['status' => LeaveStatus::Approved->value]);
        asUser(['approve_leaves']);

        postJson("/api/v1/leave-requests/{$leave->id}/approve")->assertForbidden();
    });

    // ── Reject ────────────────────────────────────────────────────────────

    it('rejects a pending leave request with a reason', function () {
        $leave = LeaveRequest::factory()->create(['status' => LeaveStatus::Pending->value]);
        asUser(['approve_leaves']);

        postJson("/api/v1/leave-requests/{$leave->id}/reject", ['reason' => 'موظفون في إجازة مسبقاً'])
            ->assertOk()
            ->assertJsonPath('data.status', 'rejected');

        expect($leave->fresh()->rejection_reason)->toBe('موظفون في إجازة مسبقاً');
    });

    it('requires reason when rejecting', function () {
        $leave = LeaveRequest::factory()->create(['status' => LeaveStatus::Pending->value]);
        asUser(['approve_leaves']);

        postJson("/api/v1/leave-requests/{$leave->id}/reject", [])->assertUnprocessable();
    });

    // ── Destroy ───────────────────────────────────────────────────────────

    it('lets the owner cancel a pending request', function () {
        $user  = userWith([]);
        $emp   = $user->employee;
        $leave = LeaveRequest::factory()->create([
            'employee_id' => $emp->id,
            'status'      => LeaveStatus::Pending->value,
        ]);
        Sanctum::actingAs($user);

        deleteJson("/api/v1/leave-requests/{$leave->id}")->assertOk();
        expect(LeaveRequest::find($leave->id))->toBeNull();
    });

    it('cannot cancel an approved request', function () {
        $user  = userWith([]);
        $emp   = $user->employee;
        $leave = LeaveRequest::factory()->create([
            'employee_id' => $emp->id,
            'status'      => LeaveStatus::Approved->value,
        ]);
        Sanctum::actingAs($user);

        deleteJson("/api/v1/leave-requests/{$leave->id}")->assertForbidden();
    });
});
