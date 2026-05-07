<?php

namespace App\Models;

use App\Enums\PayrollStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PayrollRun extends Model
{
    protected $fillable = [
        'month',
        'year',
        'status',
        'total_amount',
        'run_by',
    ];

    protected $casts = [
        'status'       => PayrollStatus::class,
        'total_amount' => 'decimal:2',
        'month'        => 'integer',
        'year'         => 'integer',
    ];

    public function runBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'run_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PayrollItem::class);
    }
}
