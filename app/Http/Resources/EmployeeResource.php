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
            'status'       => $this->status?->value,
            'hire_date'    => $this->hire_date?->format('Y-m-d'),

            'department'   => $this->whenLoaded('department', fn () => $this->department ? [
                'id'   => $this->department->id,
                'name' => $this->department->name,
            ] : null),

            'job_title'    => $this->whenLoaded('jobTitle', fn () => $this->jobTitle ? [
                'id'   => $this->jobTitle->id,
                'name' => $this->jobTitle->name,
            ] : null),

            'contract_type'=> $this->whenLoaded('contractType', fn () => $this->contractType ? [
                'id'   => $this->contractType->id,
                'name' => $this->contractType->name,
                'slug' => $this->contractType->slug,
            ] : null),

            'manager'      => $this->whenLoaded('manager', fn () => $this->manager ? [
                'id'   => $this->manager->id,
                'name' => $this->manager->name,
                // load nested jobTitle only if available
                'job_title' => $this->manager->relationLoaded('jobTitle') ? $this->manager->jobTitle?->name : null,
            ] : null),

            // salary data only for authorised roles — closures ensure lazy evaluation
            'base_salary'         => $this->when($canViewPayroll, fn () => (float) $this->resource->getRawOriginal('base_salary')),
            'housing_allowance'   => $this->when($canViewPayroll, fn () => (float) $this->resource->getRawOriginal('housing_allowance')),
            'transport_allowance' => $this->when($canViewPayroll, fn () => (float) $this->resource->getRawOriginal('transport_allowance')),

            'created_at'   => $this->created_at?->format('Y-m-d'),
        ];
    }
}
