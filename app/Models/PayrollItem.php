<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollItem extends Model
{
    protected $fillable = [
        'payroll_run_id',
        'employee_id',
        'base_salary',
        'housing_allowance',
        'transport_allowance',
        'other_allowances',
        'deductions',
        'net_salary',
    ];

    protected $casts = [
        'base_salary'         => 'decimal:2',
        'housing_allowance'   => 'decimal:2',
        'transport_allowance' => 'decimal:2',
        'other_allowances'    => 'decimal:2',
        'deductions'          => 'decimal:2',
        'net_salary'          => 'decimal:2',
    ];

    public function payrollRun(): BelongsTo
    {
        return $this->belongsTo(PayrollRun::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
