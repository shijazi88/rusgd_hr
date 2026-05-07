<?php

namespace App\Models;

use App\Enums\LeaveStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeaveRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'reference', 'employee_id', 'leave_type_id',
        'from_date', 'to_date', 'days', 'reason',
        'status', 'approved_by', 'approval_level',
        'approved_at', 'rejection_reason',
    ];

    protected $casts = [
        'from_date'   => 'date',
        'to_date'     => 'date',
        'approved_at' => 'datetime',
        'days'        => 'integer',
        'status'      => LeaveStatus::class,
    ];

    // ── Scopes ──────────────────────────────────────────────────────────────

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', LeaveStatus::Pending->value);
    }

    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    public function scopeByEmployee(Builder $query, int $employeeId): Builder
    {
        return $query->where('employee_id', $employeeId);
    }

    public function scopeNeedsAction(Builder $query): Builder
    {
        return $query->whereIn('status', [LeaveStatus::Pending->value, LeaveStatus::InitialApproval->value]);
    }

    // ── Relationships ────────────────────────────────────────────────────────

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function approvalSteps(): MorphMany
    {
        return $this->morphMany(ApprovalStep::class, 'approvable')->orderBy('created_at');
    }
}
