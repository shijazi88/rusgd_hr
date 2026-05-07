<?php

namespace App\Http\Requests\Shift;

use Illuminate\Foundation\Http\FormRequest;

class AssignShiftRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employee_id'    => 'required|integer|exists:employees,id',
            'shift_id'       => 'required|integer|exists:shifts,id',
            'from_date'      => 'required|date',
            'to_date'        => 'required|date|after_or_equal:from_date',
            'days_of_week'   => 'required|array|min:1',
            'days_of_week.*' => 'integer|between:0,6',
        ];
    }
}
