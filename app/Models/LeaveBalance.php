<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveBalance extends Model
{
    protected $fillable = [
        'employee_id', 'leave_type_id', 'year', 'total_days', 'used_days',
    ];

    protected $casts = [
        'year'       => 'integer',
        'total_days' => 'integer',
        'used_days'  => 'integer',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class);
    }

    public function getRemainingDaysAttribute(): int
    {
        return max(0, $this->total_days - $this->used_days);
    }
}
