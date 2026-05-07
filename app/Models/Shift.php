<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shift extends Model
{
    protected $fillable = ['name', 'start_time', 'end_time', 'color', 'grace_minutes'];

    public function assignments(): HasMany
    {
        return $this->hasMany(ShiftAssignment::class);
    }
}
