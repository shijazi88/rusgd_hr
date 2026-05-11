<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Period extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'color',
        'is_open_period', 'allow_no_fingerprint', 'is_stopped',

        'checkin_required',
        'checkin_earliest_at', 'checkin_start_at', 'checkin_end_at', 'checkin_latest_at',
        'checkin_after_grace_action', 'checkin_after_end_action',
        'checkin_absence_without_perm', 'checkin_absence_deduction', 'checkin_absence_deduction_type',

        'checkout_required',
        'checkout_earliest_at', 'checkout_start_at', 'checkout_end_at', 'checkout_latest_at',
        'checkout_after_grace_action', 'checkout_next_day',
        'checkout_absence_without_perm', 'checkout_absence_deduction', 'checkout_absence_deduction_type',

        'total_work_minutes',
    ];

    protected $casts = [
        'is_open_period'            => 'boolean',
        'allow_no_fingerprint'      => 'boolean',
        'is_stopped'                => 'boolean',
        'checkin_required'          => 'boolean',
        'checkin_absence_without_perm' => 'boolean',
        'checkin_absence_deduction' => 'decimal:2',
        'checkout_required'         => 'boolean',
        'checkout_next_day'         => 'boolean',
        'checkout_absence_without_perm' => 'boolean',
        'checkout_absence_deduction' => 'decimal:2',
        'total_work_minutes'        => 'integer',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_stopped', false);
    }

    public function lateTiers(): HasMany
    {
        return $this->hasMany(PeriodLateTier::class)->orderBy('sort_order')->orderBy('from_time');
    }

    public function firstShiftDays(): HasMany
    {
        return $this->hasMany(ShiftDay::class, 'first_period_id');
    }

    public function secondShiftDays(): HasMany
    {
        return $this->hasMany(ShiftDay::class, 'second_period_id');
    }

    /**
     * Used by attendance evaluation: find the late tier that applies
     * to an actual check-in time (or null if not late at all).
     */
    public function resolveLateTierFor(string $checkInTime): ?PeriodLateTier
    {
        return $this->lateTiers()
            ->where('from_time', '<=', $checkInTime)
            ->where('to_time', '>=', $checkInTime)
            ->first();
    }
}
