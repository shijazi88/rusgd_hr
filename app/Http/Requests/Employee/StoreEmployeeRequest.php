<?php

namespace App\Http\Requests\Employee;

use App\Enums\ContractType;
use App\Enums\EmployeeStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermissionTo('edit_employees');
    }

    public function rules(): array
    {
        return [
            'name'                => ['required', 'string', 'max:100'],
            'email'               => ['required', 'email', 'unique:employees,email'],
            'phone'               => ['nullable', 'string', 'max:20'],
            'department_id'       => ['required', 'integer', 'exists:departments,id'],
            'job_title'           => ['required', 'string', 'max:100'],
            'manager_id'          => ['nullable', 'integer', 'exists:employees,id'],
            'contract_type'       => ['required', Rule::enum(ContractType::class)],
            'base_salary'         => ['required', 'numeric', 'min:0'],
            'housing_allowance'   => ['nullable', 'numeric', 'min:0'],
            'transport_allowance' => ['nullable', 'numeric', 'min:0'],
            'hire_date'           => ['required', 'date', 'before_or_equal:today'],
            'status'              => ['nullable', Rule::enum(EmployeeStatus::class)],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'          => 'اسم الموظف مطلوب.',
            'email.required'         => 'البريد الإلكتروني مطلوب.',
            'email.unique'           => 'البريد الإلكتروني مستخدم بالفعل.',
            'department_id.required' => 'القسم مطلوب.',
            'department_id.exists'   => 'القسم المحدد غير موجود.',
            'job_title.required'     => 'المسمى الوظيفي مطلوب.',
            'contract_type.required' => 'نوع العقد مطلوب.',
            'base_salary.required'   => 'الراتب الأساسي مطلوب.',
            'hire_date.required'     => 'تاريخ الانضمام مطلوب.',
            'hire_date.before_or_equal' => 'تاريخ الانضمام لا يمكن أن يكون في المستقبل.',
        ];
    }
}
