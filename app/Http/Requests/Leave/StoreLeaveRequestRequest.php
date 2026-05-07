<?php

namespace App\Http\Requests\Leave;

use Illuminate\Foundation\Http\FormRequest;

class StoreLeaveRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // policy handles authorization in the controller
    }

    public function rules(): array
    {
        return [
            'employee_id'   => ['required', 'integer', 'exists:employees,id'],
            'leave_type_id' => ['required', 'integer', 'exists:leave_types,id'],
            'from_date'     => ['required', 'date', 'after_or_equal:today'],
            'to_date'       => ['required', 'date', 'after_or_equal:from_date'],
            'reason'        => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'employee_id.required'    => 'الموظف مطلوب.',
            'employee_id.exists'      => 'الموظف المحدد غير موجود.',
            'leave_type_id.required'  => 'نوع الإجازة مطلوب.',
            'leave_type_id.exists'    => 'نوع الإجازة غير موجود.',
            'from_date.required'      => 'تاريخ البداية مطلوب.',
            'from_date.after_or_equal'=> 'لا يمكن أن يكون تاريخ البداية في الماضي.',
            'to_date.required'        => 'تاريخ الانتهاء مطلوب.',
            'to_date.after_or_equal'  => 'تاريخ الانتهاء يجب أن يكون بعد أو يساوي تاريخ البداية.',
        ];
    }
}
