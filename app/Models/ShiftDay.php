<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShiftDay extends Model
{
    use HasFactory;

    public const DAYS = ['sat', 'sun', 'mon', 'tue', 'wed', 'thu', 'fri'];

    protected $fillable = [
        'shift_id', 'day_of_week',
        'first_period_id', 'second_period_id',
        'multiplier',
    ];

    protected $casts = [
        'multiplier' => 'decimal:2',
    ];

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function firstPeriod(): BelongsTo
    {
        return $this->belongsTo(Period::class, 'first_period_id');
    }

    public function secondPeriod(): BelongsTo
    {
        return $this->belongsTo(Period::class, 'second_period_id');
    }
}
