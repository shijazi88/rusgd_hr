<?php

namespace App\Models;

use App\Enums\PurchaseStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'reference',
        'employee_id',
        'item_name',
        'vendor',
        'quantity',
        'amount',
        'reason',
        'status',
        'approved_by',
        'approval_level',
        'approved_at',
        'rejection_reason',
    ];

    protected $casts = [
        'status'      => PurchaseStatus::class,
        'amount'      => 'decimal:2',
        'approved_at' => 'datetime',
        'quantity'    => 'integer',
    ];

    // ── Scopes ───────────────────────────────────────────────────────────────

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', PurchaseStatus::Pending->value);
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
        return $query->whereIn('status', [PurchaseStatus::Pending->value, PurchaseStatus::InitialApproval->value]);
    }

    public function scopeForMonth(Builder $query, int $month, int $year): Builder
    {
        return $query->whereMonth('created_at', $month)->whereYear('created_at', $year);
    }

    // ── Relationships ────────────────────────────────────────────────────────

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
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
