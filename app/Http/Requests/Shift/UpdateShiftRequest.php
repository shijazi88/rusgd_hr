<?php

namespace App\Http\Requests\Shift;

use App\Models\ShiftDay;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateShiftRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermissionTo('manage_shifts');
    }

    public function rules(): array
    {
        return [
            'name'                    => ['sometimes', 'string', 'max:100'],
            'color'                   => ['sometimes', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'show_additional_periods' => ['sometimes', 'boolean'],
            'is_stopped'              => ['sometimes', 'boolean'],

            'days'                          => ['sometimes', 'array'],
            'days.*.day_of_week'            => ['required_with:days.*', Rule::in(ShiftDay::DAYS)],
            'days.*.first_period_id'        => ['nullable', 'integer', 'exists:periods,id'],
            'days.*.second_period_id'       => ['nullable', 'integer', 'exists:periods,id'],
            'days.*.multiplier'             => ['sometimes', 'numeric', 'min:0', 'max:10'],
        ];
    }
}
