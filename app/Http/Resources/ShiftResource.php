<?php

namespace App\Http\Resources;

use App\Models\ShiftDay;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShiftResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // Always return all 7 days; for days not configured, return nulls so
        // the frontend can render a complete weekly grid in a single pass.
        $byDay = $this->whenLoaded('days', fn () => $this->days->keyBy('day_of_week'));

        $daysOutput = collect(ShiftDay::DAYS)->map(function (string $code) use ($byDay) {
            $d = is_iterable($byDay) ? ($byDay[$code] ?? null) : null;
            return [
                'day_of_week'      => $code,
                'first_period_id'  => $d?->first_period_id,
                'second_period_id' => $d?->second_period_id,
                'multiplier'       => $d ? (float) $d->multiplier : 1.0,
                'first_period'     => $d?->relationLoaded('firstPeriod') && $d->firstPeriod ? [
                    'id' => $d->firstPeriod->id, 'name' => $d->firstPeriod->name, 'color' => $d->firstPeriod->color,
                ] : null,
                'second_period'    => $d?->relationLoaded('secondPeriod') && $d->secondPeriod ? [
                    'id' => $d->secondPeriod->id, 'name' => $d->secondPeriod->name, 'color' => $d->secondPeriod->color,
                ] : null,
            ];
        })->values();

        return [
            'id'    => $this->id,
            'name'  => $this->name,
            'color' => $this->color,
            'show_additional_periods' => (bool) $this->show_additional_periods,
            'is_stopped'              => (bool) $this->is_stopped,
            'days'  => $daysOutput,
        ];
    }
}
