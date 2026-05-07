<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PayrollRunResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'month'        => $this->month,
            'year'         => $this->year,
            'status'       => $this->status?->value,
            'total_amount' => (float) $this->total_amount,
            // items_count is set by withCount()/loadCount() on index & store;
            // falls back to counting the loaded relation on show
            'items_count'  => $this->items_count ?? $this->whenLoaded('items', fn () => $this->items->count()),
            'run_by'       => $this->whenLoaded('runBy', fn () => $this->runBy ? [
                'id'   => $this->runBy->id,
                'name' => $this->runBy->name,
            ] : null),
            'items'        => PayrollItemResource::collection($this->whenLoaded('items')),
            'created_at'   => $this->created_at?->format('Y-m-d H:i'),
        ];
    }
}
