<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseRequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'reference'        => $this->reference,
            'item_name'        => $this->item_name,
            'vendor'           => $this->vendor,
            'quantity'         => $this->quantity,
            'amount'           => (float) $this->amount,
            'reason'           => $this->reason,
            'status'           => $this->status?->value,
            'approval_level'   => $this->approval_level,
            'rejection_reason' => $this->rejection_reason,
            'approved_at'      => $this->approved_at?->format('Y-m-d H:i'),
            'created_at'       => $this->created_at?->format('Y-m-d H:i'),
            'employee'         => $this->whenLoaded('employee', fn () => [
                'id'         => $this->employee->id,
                'name'       => $this->employee->name,
                'department' => $this->employee->department?->name,
            ]),
            'approved_by'      => $this->whenLoaded('approvedBy', fn () => $this->approvedBy ? [
                'id'   => $this->approvedBy->id,
                'name' => $this->approvedBy->name,
            ] : null),
        ];
    }
}
