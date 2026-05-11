<?php

namespace App\Actions;

use App\Enums\PayrollStatus;
use App\Exceptions\PayrollAlreadyProcessedException;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\PayrollItem;
use App\Models\PayrollRun;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class RunPayrollAction
{
    public function execute(int $month, int $year, User $runBy): PayrollRun
    {
        if (PayrollRun::where('month', $month)->where('year', $year)->exists()) {
            throw new PayrollAlreadyProcessedException(
                "تم معالجة مسير رواتب {$month}/{$year} مسبقاً."
            );
        }

        try {
            return DB::transaction(function () use ($month, $year, $runBy) {
                $run = PayrollRun::create([
                    'month'        => $month,
                    'year'         => $year,
                    'status'       => PayrollStatus::Draft->value,
                    'total_amount' => 0,
                    'run_by'       => $runBy->id,
                ]);

                // Days in this payroll month
                $periodStart = Carbon::create($year, $month, 1)->startOfDay();
                $periodEnd   = $periodStart->copy()->endOfMonth();
                $totalAmount = 0.0;
                $now         = now();

                Employee::active()->chunk(100, function ($employees) use ($run, $periodStart, $periodEnd, &$totalAmount, $now) {
                    $employeeIds = $employees->pluck('id');

                    // Fetch all attendance rows for the month in a single query
                    $attendanceByEmployee = Attendance::whereIn('employee_id', $employeeIds)
                        ->whereBetween('date', [$periodStart->toDateString(), $periodEnd->toDateString()])
                        ->get()
                        ->groupBy('employee_id');

                    $rows = [];

                    foreach ($employees as $employee) {
                        $records = $attendanceByEmployee->get($employee->id, collect());

                        $base      = (float) $employee->base_salary;
                        $housing   = (float) $employee->housing_allowance;
                        $transport = (float) $employee->transport_allowance;

                        // ── Compute attendance-driven adjustments ─────────────
                        // Deductions come from late tiers + absence-without-permission.
                        // Multiplier-weighted bonus comes from days where multiplier > 1
                        // (e.g. Friday 1.5x adds 0.5 * day_rate as bonus).
                        $deductions = $this->computeDeductions($records, $base);
                        $other      = $this->computeMultiplierBonus($records, $base);

                        $net = $base + $housing + $transport + $other - $deductions;

                        $rows[] = [
                            'payroll_run_id'      => $run->id,
                            'employee_id'         => $employee->id,
                            'base_salary'         => $base,
                            'housing_allowance'   => $housing,
                            'transport_allowance' => $transport,
                            'other_allowances'    => round($other, 2),
                            'deductions'          => round($deductions, 2),
                            'net_salary'          => round($net, 2),
                            'created_at'          => $now,
                            'updated_at'          => $now,
                        ];

                        $totalAmount += $net;
                    }

                    if (!empty($rows)) {
                        PayrollItem::insert($rows);
                    }
                });

                $run->update([
                    'status'       => PayrollStatus::Completed->value,
                    'total_amount' => round($totalAmount, 2),
                ]);

                return $run->fresh(['runBy'])->loadCount('items');
            });
        } catch (QueryException $e) {
            if ($e->errorInfo[1] === 1062) {
                throw new PayrollAlreadyProcessedException(
                    "تم معالجة مسير رواتب {$month}/{$year} مسبقاً."
                );
            }
            throw $e;
        }
    }

    /**
     * Sum every attendance row's deduction, converted to a money amount using
     * the employee's base salary as the reference rate.
     */
    private function computeDeductions(Collection $records, float $baseSalary): float
    {
        // Assumption: 30 days per month, 8 hours per day for rate computation.
        // The figures match common Arab-region payroll conventions; an
        // admin-configurable setting could replace these constants later.
        $dayRate  = $baseSalary / 30;
        $hourRate = $dayRate / 8;

        $total = 0.0;

        foreach ($records as $r) {
            $amount = (float) $r->deduction_amount;
            if ($amount <= 0) continue;

            $total += match ($r->deduction_type) {
                'hour'    => $amount * $hourRate,
                'day'     => $amount * $dayRate,
                'absence' => $dayRate,  // counted as full-day absence
                'fixed'   => $amount,    // raw monetary amount
                default   => 0,
            };
        }

        return $total;
    }

    /**
     * Multiplier bonus: e.g. a 1.5x day adds 0.5 * day_rate of bonus to pay,
     * a 2x day adds 1.0 * day_rate. A 1x day adds nothing.
     */
    private function computeMultiplierBonus(Collection $records, float $baseSalary): float
    {
        $dayRate = $baseSalary / 30;
        $bonus = 0.0;

        foreach ($records as $r) {
            if ($r->status?->value !== 'present' && $r->status?->value !== 'late') continue;
            $mult = (float) $r->multiplier;
            if ($mult > 1.0) {
                $bonus += ($mult - 1.0) * $dayRate;
            }
        }

        return $bonus;
    }
}
