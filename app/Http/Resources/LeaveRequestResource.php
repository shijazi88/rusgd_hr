<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeaveRequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'reference'        => $this->reference,
            'status'           => $this->status?->value,
            'days'             => $this->days,
            'from_date'        => $this->from_date?->format('Y-m-d'),
            'to_date'          => $this->to_date?->format('Y-m-d'),
            'reason'           => $this->reason,
            'approval_level'   => $this->approval_level,
            'rejection_reason' => $this->rejection_reason,
            'approved_at'      => $this->approved_at?->format('Y-m-d H:i'),
            'created_at'       => $this->created_at?->format('Y-m-d H:i'),
            'employee'         => $this->whenLoaded('employee', fn () => [
                'id'         => $this->employee->id,
                'name'       => $this->employee->name,
                'department' => $this->employee->department?->name,
            ]),
            'leave_type'       => $this->whenLoaded('leaveType', fn () => [
                'id'    => $this->leaveType->id,
                'name'  => $this->leaveType->name,
                'color' => $this->leaveType->color,
            ]),
            'approved_by'      => $this->whenLoaded('approvedBy', fn () => $this->approvedBy ? [
                'id'   => $this->approvedBy->id,
                'name' => $this->approvedBy->name,
            ] : null),
        ];
    }
}
