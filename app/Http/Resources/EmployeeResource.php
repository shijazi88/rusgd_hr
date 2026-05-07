<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $canViewPayroll = $request->user()?->hasPermissionTo('view_payroll');

        return [
            'id'           => $this->id,
            'name'         => $this->name,
            'email'        => $this->email,
            'phone'        => $this->phone,
            'job_title'    => $this->job_title,
            'contract_type'=> $this->contract_type?->value,
            'status'       => $this->status?->value,
            'hire_date'    => $this->hire_date?->format('Y-m-d'),
            'department'   => $this->whenLoaded('department', fn () => [
                'id'   => $this->department->id,
                'name' => $this->department->name,
            ]),
            'manager'      => $this->whenLoaded('manager', fn () => $this->manager ? [
                'id'        => $this->manager->id,
                'name'      => $this->manager->name,
                'job_title' => $this->manager->job_title,
            ] : null),
            // salary data only for authorised roles — closures ensure lazy evaluation
            'base_salary'         => $this->when($canViewPayroll, fn () => (float) $this->resource->getRawOriginal('base_salary')),
            'housing_allowance'   => $this->when($canViewPayroll, fn () => (float) $this->resource->getRawOriginal('housing_allowance')),
            'transport_allowance' => $this->when($canViewPayroll, fn () => (float) $this->resource->getRawOriginal('transport_allowance')),
            'created_at'   => $this->created_at?->format('Y-m-d'),
        ];
    }
}
