<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeaveBalanceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'leave_type'     => LeaveTypeResource::make($this->whenLoaded('leaveType')),
            'year'           => $this->year,
            'total_days'     => $this->total_days,
            'used_days'      => $this->used_days,
            'remaining_days' => $this->remaining_days,
        ];
    }
}
