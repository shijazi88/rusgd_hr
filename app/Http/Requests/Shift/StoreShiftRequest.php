<?php

namespace App\Http\Requests\Shift;

use Illuminate\Foundation\Http\FormRequest;

class StoreShiftRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'          => 'required|string|max:100',
            'start_time'    => 'required|date_format:H:i',
            'end_time'      => 'required|date_format:H:i',
            'color'         => 'sometimes|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'grace_minutes' => 'sometimes|integer|min:0|max:60',
        ];
    }
}
