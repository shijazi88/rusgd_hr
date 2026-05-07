<?php

namespace App\Models;

use App\Enums\ApprovalRuleType;
use App\Enums\Priority;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApprovalRule extends Model
{
    use HasFactory;
    protected $fillable = [
        'type', 'description', 'min_value', 'max_value',
        'approver_role', 'approver_label', 'priority', 'is_active',
    ];

    protected $casts = [
        'min_value' => 'float',
        'max_value' => 'float',
        'is_active' => 'boolean',
        'type'      => ApprovalRuleType::class,
        'priority'  => Priority::class,
    ];
}
