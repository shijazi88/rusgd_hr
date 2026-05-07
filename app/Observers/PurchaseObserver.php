<?php

namespace App\Observers;

use App\Models\PurchaseRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;

class PurchaseObserver
{
    public function created(PurchaseRequest $purchase): void
    {
        $this->log('created', $purchase, [], $purchase->getAttributes());
    }

    public function updated(PurchaseRequest $purchase): void
    {
        $this->log('updated', $purchase, $purchase->getOriginal(), $purchase->getChanges());
    }

    public function deleted(PurchaseRequest $purchase): void
    {
        $this->log('deleted', $purchase, $purchase->getAttributes(), []);
    }

    private function log(string $action, PurchaseRequest $purchase, array $old, array $new): void
    {
        DB::table('audit_logs')->insert([
            'user_id'        => Auth::id(),
            'action'         => $action,
            'auditable_type' => PurchaseRequest::class,
            'auditable_id'   => $purchase->id,
            'old_values'     => empty($old) ? null : json_encode($old),
            'new_values'     => empty($new) ? null : json_encode($new),
            'ip_address'     => Request::ip(),
            'user_agent'     => Request::userAgent(),
            'created_at'     => now(),
        ]);
    }
}
