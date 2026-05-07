<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApprovalRuleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'type'           => $this->type?->value,
            'description'    => $this->description,
            'min_value'      => $this->min_value,
            'max_value'      => $this->max_value,
            'approver_role'  => $this->approver_role,
            'approver_label' => $this->approver_label,
            'priority'       => $this->priority?->value,
            'is_active'      => $this->is_active,
        ];
    }
}
