<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shift extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'color',
        'show_additional_periods', 'is_stopped',
    ];

    protected $casts = [
        'show_additional_periods' => 'boolean',
        'is_stopped'              => 'boolean',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_stopped', false);
    }

    public function days(): HasMany
    {
        return $this->hasMany(ShiftDay::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(ShiftAssignment::class);
    }

    /**
     * Helper used by attendance evaluation. Returns the ShiftDay row for
     * a given day-of-week code ('sat','sun', etc.), or null if not configured.
     */
    public function dayFor(string $dayCode): ?ShiftDay
    {
        return $this->days->firstWhere('day_of_week', $dayCode);
    }
}
