<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'employee'     => EmployeeResource::make($this->whenLoaded('employee')),
            'shift'        => ShiftResource::make($this->whenLoaded('shift')),
            'date'         => $this->date?->format('Y-m-d'),
            'check_in'     => $this->check_in,
            'check_out'    => $this->check_out,
            'work_hours'   => $this->work_hours,
            'late_minutes' => $this->late_minutes,
            'status'       => $this->status?->value,
        ];
    }
}
