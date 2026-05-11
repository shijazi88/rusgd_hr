<?php

namespace App\Http\Requests\Period;

use Illuminate\Foundation\Http\FormRequest;

class StorePeriodRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermissionTo('manage_periods');
    }

    public function rules(): array
    {
        return array_merge($this->fieldRules(true), $this->lateTierRules());
    }

    /**
     * Shared with UpdatePeriodRequest. When $required is false, every field is
     * "sometimes" so partial updates work.
     */
    public function fieldRules(bool $required = true): array
    {
        $r = $required ? 'required' : 'sometimes';
        $rn = $required ? 'required' : 'sometimes';

        return [
            'name'                 => [$r, 'string', 'max:100', 'unique:periods,name' . ($this->route('id') ? ',' . $this->route('id') : '')],
            'color'                => ['sometimes', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'is_open_period'       => ['sometimes', 'boolean'],
            'allow_no_fingerprint' => ['sometimes', 'boolean'],
            'is_stopped'           => ['sometimes', 'boolean'],

            'checkin_required'     => ['sometimes', 'boolean'],
            'checkin_earliest_at'  => ['nullable', 'date_format:H:i:s,H:i'],
            'checkin_start_at'     => ['nullable', 'date_format:H:i:s,H:i'],
            'checkin_end_at'       => ['nullable', 'date_format:H:i:s,H:i'],
            'checkin_latest_at'    => ['nullable', 'date_format:H:i:s,H:i'],
            'checkin_after_grace_action' => ['sometimes', 'string', 'in:entry_only,refuse,late_attendance'],
            'checkin_after_end_action'   => ['sometimes', 'string', 'in:entry_only,refuse,late_attendance,absent'],
            'checkin_absence_without_perm'   => ['sometimes', 'boolean'],
            'checkin_absence_deduction'      => ['sometimes', 'numeric', 'min:0'],
            'checkin_absence_deduction_type' => ['sometimes', 'string', 'in:hour,day,fixed'],

            'checkout_required'    => ['sometimes', 'boolean'],
            'checkout_earliest_at' => ['nullable', 'date_format:H:i:s,H:i'],
            'checkout_start_at'    => ['nullable', 'date_format:H:i:s,H:i'],
            'checkout_end_at'      => ['nullable', 'date_format:H:i:s,H:i'],
            'checkout_latest_at'   => ['nullable', 'date_format:H:i:s,H:i'],
            'checkout_after_grace_action' => ['sometimes', 'string', 'in:exit_only,refuse'],
            'checkout_next_day'    => ['sometimes', 'boolean'],
            'checkout_absence_without_perm'   => ['sometimes', 'boolean'],
            'checkout_absence_deduction'      => ['sometimes', 'numeric', 'min:0'],
            'checkout_absence_deduction_type' => ['sometimes', 'string', 'in:hour,day,fixed'],

            'total_work_minutes'   => ['sometimes', 'integer', 'min:1', 'max:1440'],
        ];
    }

    public function lateTierRules(): array
    {
        return [
            'late_tiers'                          => ['sometimes', 'array'],
            'late_tiers.*.from_time'              => ['required_with:late_tiers.*', 'date_format:H:i:s,H:i'],
            'late_tiers.*.to_time'                => ['required_with:late_tiers.*', 'date_format:H:i:s,H:i'],
            'late_tiers.*.deduction_amount'       => ['required_with:late_tiers.*', 'numeric', 'min:0'],
            'late_tiers.*.deduction_type'         => ['required_with:late_tiers.*', 'string', 'in:hour,day,fixed,absence'],
            'late_tiers.*.min_occurrences'        => ['sometimes', 'integer', 'min:0'],
        ];
    }
}
