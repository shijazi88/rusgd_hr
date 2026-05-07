<?php

namespace App\Actions;

use App\Enums\PayrollStatus;
use App\Exceptions\PayrollAlreadyProcessedException;
use App\Models\Employee;
use App\Models\PayrollItem;
use App\Models\PayrollRun;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class RunPayrollAction
{
    public function execute(int $month, int $year, User $runBy): PayrollRun
    {
        // Fast pre-flight for sequential duplicate requests (99.9% case)
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

                $totalAmount = 0.0;
                $now         = now(); // single timestamp for all items in this run

                // Chunk to avoid loading all employees into memory at once
                Employee::active()->chunk(100, function ($employees) use ($run, &$totalAmount, $now) {
                    $rows = [];

                    foreach ($employees as $employee) {
                        $base       = (float) $employee->base_salary;
                        $housing    = (float) $employee->housing_allowance;
                        $transport  = (float) $employee->transport_allowance;
                        $other      = 0.0;
                        $deductions = 0.0;
                        $net        = $base + $housing + $transport + $other - $deductions;

                        $rows[] = [
                            'payroll_run_id'      => $run->id,
                            'employee_id'         => $employee->id,
                            'base_salary'         => $base,
                            'housing_allowance'   => $housing,
                            'transport_allowance' => $transport,
                            'other_allowances'    => $other,
                            'deductions'          => $deductions,
                            'net_salary'          => $net,
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
            // Concurrent duplicate: UNIQUE constraint on (month, year) fired inside the transaction
            if ($e->errorInfo[1] === 1062) {
                throw new PayrollAlreadyProcessedException(
                    "تم معالجة مسير رواتب {$month}/{$year} مسبقاً."
                );
            }
            throw $e;
        }
    }
}
