<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ActivityLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'action'      => $this->action,
            'entity_type' => class_basename($this->auditable_type),
            'entity_id'   => $this->auditable_id,
            'user'        => $this->user_id ? [
                'id'   => $this->user_id,
                'name' => $this->user_name,
            ] : null,
            'old_values'  => $this->old_values ? json_decode($this->old_values, true) : null,
            'new_values'  => $this->new_values ? json_decode($this->new_values, true) : null,
            'ip_address'  => $this->ip_address,
            'created_at'  => $this->created_at,
        ];
    }
}
