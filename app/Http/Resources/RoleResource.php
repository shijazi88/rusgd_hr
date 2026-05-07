<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'name'             => $this->name,
            'slug'             => $this->slug,
            'financial_limit'  => $this->financial_limit,
            'leave_limit_days' => $this->leave_limit_days,
            'color'            => $this->color,
            'permissions'      => PermissionResource::collection($this->whenLoaded('permissions')),
        ];
    }
}
