<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PeriodLateTier extends Model
{
    use HasFactory;

    protected $fillable = [
        'period_id', 'from_time', 'to_time',
        'deduction_amount', 'deduction_type',
        'min_occurrences', 'sort_order',
    ];

    protected $casts = [
        'deduction_amount' => 'decimal:2',
        'min_occurrences'  => 'integer',
        'sort_order'       => 'integer',
    ];

    public function period(): BelongsTo
    {
        return $this->belongsTo(Period::class);
    }
}
