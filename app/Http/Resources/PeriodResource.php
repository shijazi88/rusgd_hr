<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PeriodResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'    => $this->id,
            'name'  => $this->name,
            'color' => $this->color,

            'is_open_period'       => (bool) $this->is_open_period,
            'allow_no_fingerprint' => (bool) $this->allow_no_fingerprint,
            'is_stopped'           => (bool) $this->is_stopped,

            'checkin' => [
                'required'           => (bool) $this->checkin_required,
                'earliest_at'        => $this->checkin_earliest_at,
                'start_at'           => $this->checkin_start_at,
                'end_at'             => $this->checkin_end_at,
                'latest_at'          => $this->checkin_latest_at,
                'after_grace_action' => $this->checkin_after_grace_action,
                'after_end_action'   => $this->checkin_after_end_action,
                'absence_without_perm' => (bool) $this->checkin_absence_without_perm,
                'absence_deduction'  => (float) $this->checkin_absence_deduction,
                'absence_deduction_type' => $this->checkin_absence_deduction_type,
            ],

            'checkout' => [
                'required'           => (bool) $this->checkout_required,
                'earliest_at'        => $this->checkout_earliest_at,
                'start_at'           => $this->checkout_start_at,
                'end_at'             => $this->checkout_end_at,
                'latest_at'          => $this->checkout_latest_at,
                'after_grace_action' => $this->checkout_after_grace_action,
                'next_day'           => (bool) $this->checkout_next_day,
                'absence_without_perm' => (bool) $this->checkout_absence_without_perm,
                'absence_deduction'  => (float) $this->checkout_absence_deduction,
                'absence_deduction_type' => $this->checkout_absence_deduction_type,
            ],

            'total_work_minutes' => (int) $this->total_work_minutes,

            'late_tiers' => $this->whenLoaded('lateTiers', fn () => $this->lateTiers->map(fn ($t) => [
                'id'               => $t->id,
                'from_time'        => $t->from_time,
                'to_time'          => $t->to_time,
                'deduction_amount' => (float) $t->deduction_amount,
                'deduction_type'   => $t->deduction_type,
                'min_occurrences'  => (int) $t->min_occurrences,
            ])->values()),
        ];
    }
}
