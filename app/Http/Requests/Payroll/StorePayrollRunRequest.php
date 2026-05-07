<?php

namespace App\Http\Requests\Payroll;

use Illuminate\Foundation\Http\FormRequest;

class StorePayrollRunRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'month' => 'required|integer|between:1,12',
            'year'  => 'required|integer|min:2020',
        ];
    }

    public function messages(): array
    {
        return [
            'month.required' => 'الشهر مطلوب.',
            'month.between'  => 'الشهر يجب أن يكون بين 1 و 12.',
            'year.required'  => 'السنة مطلوبة.',
            'year.min'       => 'السنة غير صالحة.',
        ];
    }
}
