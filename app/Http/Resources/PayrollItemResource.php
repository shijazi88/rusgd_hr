<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PayrollItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                  => $this->id,
            'employee'            => $this->whenLoaded('employee', fn () => [
                'id'         => $this->employee->id,
                'name'       => $this->employee->name,
                'job_title'  => $this->employee->job_title,
                'department' => $this->employee->department?->name,
            ]),
            'base_salary'         => (float) $this->base_salary,
            'housing_allowance'   => (float) $this->housing_allowance,
            'transport_allowance' => (float) $this->transport_allowance,
            'other_allowances'    => (float) $this->other_allowances,
            'deductions'          => (float) $this->deductions,
            'net_salary'          => (float) $this->net_salary,
        ];
    }
}
