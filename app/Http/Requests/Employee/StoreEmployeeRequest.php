<?php

namespace App\Http\Requests\Employee;

use App\Enums\EmployeeStatus;
use App\Models\ContractType;
use App\Models\JobTitle;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

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
            'job_title_id'        => ['required', 'integer', 'exists:job_titles,id'],
            'manager_id'          => ['nullable', 'integer', 'exists:employees,id'],
            'contract_type_id'    => ['required', 'integer', 'exists:contract_types,id'],
            'base_salary'         => ['required', 'numeric', 'min:0'],
            'housing_allowance'   => ['nullable', 'numeric', 'min:0'],
            'transport_allowance' => ['nullable', 'numeric', 'min:0'],
            'hire_date'           => ['required', 'date', 'before_or_equal:today'],
            'status'              => ['nullable', Rule::enum(EmployeeStatus::class)],
        ];
    }

    /**
     * Two checks that can't be expressed in `rules()`:
     *   1. Cross-FK consistency — chosen job_title must belong to chosen department.
     *   2. Both lookup rows (job_title, contract_type) must be active when CREATING
     *      a new employee. Inactive rows are kept around only for historical
     *      records, never for new assignments.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            if ($v->errors()->isNotEmpty()) return;

            $jt = JobTitle::find($this->input('job_title_id'));
            if ($jt) {
                if ((int) $jt->department_id !== (int) $this->input('department_id')) {
                    $v->errors()->add('job_title_id', 'المسمى الوظيفي لا ينتمي إلى القسم المحدد.');
                }
                if (!$jt->is_active) {
                    $v->errors()->add('job_title_id', 'لا يمكن إسناد مسمى وظيفي غير مفعّل.');
                }
            }

            $ct = ContractType::find($this->input('contract_type_id'));
            if ($ct && !$ct->is_active) {
                $v->errors()->add('contract_type_id', 'لا يمكن إسناد نوع عقد غير مفعّل.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'name.required'             => 'اسم الموظف مطلوب.',
            'email.required'            => 'البريد الإلكتروني مطلوب.',
            'email.unique'              => 'البريد الإلكتروني مستخدم بالفعل.',
            'department_id.required'    => 'القسم مطلوب.',
            'department_id.exists'      => 'القسم المحدد غير موجود.',
            'job_title_id.required'     => 'المسمى الوظيفي مطلوب.',
            'job_title_id.exists'       => 'المسمى الوظيفي غير موجود.',
            'contract_type_id.required' => 'نوع العقد مطلوب.',
            'contract_type_id.exists'   => 'نوع العقد غير موجود.',
            'base_salary.required'      => 'الراتب الأساسي مطلوب.',
            'hire_date.required'        => 'تاريخ الانضمام مطلوب.',
            'hire_date.before_or_equal' => 'تاريخ الانضمام لا يمكن أن يكون في المستقبل.',
        ];
    }
}
