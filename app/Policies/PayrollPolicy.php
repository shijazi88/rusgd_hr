<?php

namespace App\Policies;

use App\Models\PayrollRun;
use App\Models\User;

class PayrollPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view_payroll')
            || $user->hasPermissionTo('run_payroll');
    }

    public function view(User $user, PayrollRun $run): bool
    {
        return $user->hasPermissionTo('view_payroll')
            || $user->hasPermissionTo('run_payroll');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('run_payroll');
    }
}
