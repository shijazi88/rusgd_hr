<?php

namespace App\Models;

use App\Enums\ContractType;
use App\Enums\EmployeeStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'email', 'phone', 'department_id', 'job_title',
        'manager_id', 'contract_type', 'base_salary',
        'housing_allowance', 'transport_allowance', 'hire_date', 'status',
    ];

    protected $casts = [
        'hire_date'           => 'date',
        'base_salary'         => 'decimal:2',
        'housing_allowance'   => 'decimal:2',
        'transport_allowance' => 'decimal:2',
        'status'              => EmployeeStatus::class,
        'contract_type'       => ContractType::class,
    ];

    protected $hidden = ['base_salary', 'housing_allowance', 'transport_allowance'];

    // ── Scopes ──────────────────────────────────────────────────────────────

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', EmployeeStatus::Active->value);
    }

    public function scopeSearch(Builder $query, string $term): Builder
    {
        return $query->where(function (Builder $q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
              ->orWhere('email', 'like', "%{$term}%")
              ->orWhere('job_title', 'like', "%{$term}%");
        });
    }

    public function scopeByDepartment(Builder $query, int $departmentId): Builder
    {
        return $query->where('department_id', $departmentId);
    }

    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    // ── Relationships ────────────────────────────────────────────────────────

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'manager_id');
    }

    public function directReports(): HasMany
    {
        return $this->hasMany(Employee::class, 'manager_id');
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class);
    }
}
