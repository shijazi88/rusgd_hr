<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DepartmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'name'           => $this->name,
            'parent_id'      => $this->parent_id,
            'employees_count'=> $this->employees_count ?? null,
            'parent'         => $this->whenLoaded('parent', fn () => $this->parent ? [
                'id'   => $this->parent->id,
                'name' => $this->parent->name,
            ] : null),
        ];
    }
}
