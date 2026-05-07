<?php

namespace App\Services;

use App\Actions\RunPayrollAction;
use App\Models\PayrollRun;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PayrollService
{
    public function __construct(private readonly RunPayrollAction $runAction) {}

    public function run(int $month, int $year, User $runBy): PayrollRun
    {
        return $this->runAction->execute($month, $year, $runBy);
    }

    public function getHistory(int $perPage = 20): LengthAwarePaginator
    {
        return PayrollRun::with('runBy')
            ->withCount('items')
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->paginate($perPage);
    }

    public function getById(int $id): PayrollRun
    {
        $run = PayrollRun::with([
            'runBy',
            'items.employee.department',
            'items.employee.jobTitle',
            'items.employee.contractType',
        ])->find($id);

        if (!$run) {
            abort(404, 'مسير الرواتب غير موجود.');
        }

        return $run;
    }
}
