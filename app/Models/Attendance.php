<?php

namespace App\Models;

use App\Enums\AttendanceStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class Attendance extends Model
{
    protected $table = 'attendance';

    protected $fillable = [
        'employee_id',
        'shift_id',
        'date',
        'check_in',
        'check_out',
        'work_hours',
        'late_minutes',
        'status',
    ];

    protected $casts = [
        'date'       => 'date',
        'status'     => AttendanceStatus::class,
        'work_hours' => 'float',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    public function scopeByEmployee(Builder $query, int $employeeId): Builder
    {
        return $query->where('employee_id', $employeeId);
    }

    public function scopeForDate(Builder $query, Carbon $date): Builder
    {
        return $query->whereDate('date', $date);
    }

    public function scopeForMonth(Builder $query, int $month, int $year): Builder
    {
        return $query->whereMonth('date', $month)->whereYear('date', $year);
    }
}
