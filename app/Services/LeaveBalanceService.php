<?php

namespace App\Services;

use App\Exceptions\InsufficientLeaveBalanceException;
use App\Models\Employee;
use App\Models\LeaveBalance;
use App\Models\LeaveType;
use Illuminate\Database\Eloquent\Collection;

class LeaveBalanceService
{
    public function getForEmployee(Employee $employee, int $year): Collection
    {
        $types = LeaveType::all();

        // Ensure a balance row exists for each leave type
        foreach ($types as $type) {
            LeaveBalance::firstOrCreate(
                [
                    'employee_id'   => $employee->id,
                    'leave_type_id' => $type->id,
                    'year'          => $year,
                ],
                [
                    'total_days' => $type->max_days_per_year,
                    'used_days'  => 0,
                ]
            );
        }

        return LeaveBalance::with('leaveType')
            ->where('employee_id', $employee->id)
            ->where('year', $year)
            ->get();
    }

    public function validate(Employee $employee, LeaveType $leaveType, int $days): void
    {
        if (!$leaveType->is_paid) {
            return; // unpaid leave has no balance limit
        }

        $balance = LeaveBalance::where('employee_id', $employee->id)
            ->where('leave_type_id', $leaveType->id)
            ->where('year', now()->year)
            ->first();

        $remaining = $balance ? ($balance->total_days - $balance->used_days) : $leaveType->max_days_per_year;

        if ($days > $remaining) {
            throw new InsufficientLeaveBalanceException(
                "رصيد الإجازة غير كافٍ. المتبقي: {$remaining} يوم، المطلوب: {$days} أيام."
            );
        }
    }

    public function deduct(Employee $employee, LeaveType $leaveType, int $days): void
    {
        // firstOrCreate — never resets used_days on existing records
        LeaveBalance::firstOrCreate(
            [
                'employee_id'   => $employee->id,
                'leave_type_id' => $leaveType->id,
                'year'          => now()->year,
            ],
            [
                'total_days' => $leaveType->max_days_per_year,
                'used_days'  => 0,
            ]
        );

        LeaveBalance::where('employee_id', $employee->id)
            ->where('leave_type_id', $leaveType->id)
            ->where('year', now()->year)
            ->increment('used_days', $days);
    }

    public function restore(Employee $employee, LeaveType $leaveType, int $days): void
    {
        LeaveBalance::where('employee_id', $employee->id)
            ->where('leave_type_id', $leaveType->id)
            ->where('year', now()->year)
            ->decrement('used_days', $days);
    }
}
