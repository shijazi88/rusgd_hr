<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ShiftAssignment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'employee_id',
        'shift_id',
        'from_date',
        'to_date',
        'days_of_week',
    ];

    protected $casts = [
        'days_of_week' => 'array',
        'from_date'    => 'date',
        'to_date'      => 'date',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }
}
