<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'email'       => $this->email,
            'permissions' => $this->permissions()->values(),
            'roles'       => $this->roles()->map(fn ($r) => [
                'id'              => $r->id,
                'name'            => $r->name,
                'slug'            => $r->slug,
                'financial_limit' => $r->financial_limit,
                'leave_limit_days'=> $r->leave_limit_days,
            ]),
            'employee'    => $this->whenLoaded('employee', fn () => [
                'id'         => $this->employee->id,
                'name'       => $this->employee->name,
                'job_title'  => $this->employee->job_title,
                'department' => $this->employee->relationLoaded('department')
                    ? ['id' => $this->employee->department->id, 'name' => $this->employee->department->name]
                    : null,
            ]),
        ];
    }
}
