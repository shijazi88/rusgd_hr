<?php

namespace App\Observers;

use App\Models\Employee;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;

class EmployeeObserver
{
    public function created(Employee $employee): void
    {
        $this->log('created', $employee, [], $employee->getAttributes());
    }

    public function updated(Employee $employee): void
    {
        $this->log('updated', $employee, $employee->getOriginal(), $employee->getChanges());
    }

    public function deleted(Employee $employee): void
    {
        $this->log('deleted', $employee, $employee->getAttributes(), []);
    }

    private function log(string $action, Employee $employee, array $old, array $new): void
    {
        DB::table('audit_logs')->insert([
            'user_id'        => Auth::id(),
            'action'         => $action,
            'auditable_type' => Employee::class,
            'auditable_id'   => $employee->id,
            'old_values'     => empty($old) ? null : json_encode($old),
            'new_values'     => empty($new) ? null : json_encode($new),
            'ip_address'     => Request::ip(),
            'user_agent'     => Request::userAgent(),
            'created_at'     => now(),
        ]);
    }
}
