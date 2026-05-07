<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShiftAssignmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'employee'     => EmployeeResource::make($this->whenLoaded('employee')),
            'shift'        => ShiftResource::make($this->whenLoaded('shift')),
            'from_date'    => $this->from_date?->format('Y-m-d'),
            'to_date'      => $this->to_date?->format('Y-m-d'),
            'days_of_week' => $this->days_of_week,
        ];
    }
}
