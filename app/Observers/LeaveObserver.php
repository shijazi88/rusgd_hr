<?php

namespace App\Observers;

use App\Models\LeaveRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;

class LeaveObserver
{
    public function created(LeaveRequest $leave): void
    {
        $this->log('created', $leave, [], $leave->getAttributes());
    }

    public function updated(LeaveRequest $leave): void
    {
        $this->log('updated', $leave, $leave->getOriginal(), $leave->getChanges());
    }

    public function deleted(LeaveRequest $leave): void
    {
        $this->log('deleted', $leave, $leave->getAttributes(), []);
    }

    private function log(string $action, LeaveRequest $leave, array $old, array $new): void
    {
        DB::table('audit_logs')->insert([
            'user_id'        => Auth::id(),
            'action'         => $action,
            'auditable_type' => LeaveRequest::class,
            'auditable_id'   => $leave->id,
            'old_values'     => empty($old) ? null : json_encode($old),
            'new_values'     => empty($new) ? null : json_encode($new),
            'ip_address'     => Request::ip(),
            'user_agent'     => Request::userAgent(),
            'created_at'     => now(),
        ]);
    }
}
