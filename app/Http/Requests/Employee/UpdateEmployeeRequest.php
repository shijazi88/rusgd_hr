<?php

namespace App\Http\Requests\Employee;

use App\Enums\ContractType;
use App\Enums\EmployeeStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermissionTo('edit_employees');
    }

    public function rules(): array
    {
        $employeeId = $this->route('employee');

        return [
            'name'                => ['sometimes', 'string', 'max:100'],
            'phone'               => ['sometimes', 'nullable', 'string', 'max:20'],
            'department_id'       => ['sometimes', 'integer', 'exists:departments,id'],
            'job_title'           => ['sometimes', 'string', 'max:100'],
            'manager_id'          => ['sometimes', 'nullable', 'integer', 'exists:employees,id'],
            'contract_type'       => ['sometimes', Rule::enum(ContractType::class)],
            'base_salary'         => ['sometimes', 'numeric', 'min:0'],
            'housing_allowance'   => ['sometimes', 'numeric', 'min:0'],
            'transport_allowance' => ['sometimes', 'numeric', 'min:0'],
            'hire_date'           => ['sometimes', 'date', 'before_or_equal:today'],
            'status'              => ['sometimes', Rule::enum(EmployeeStatus::class)],
        ];
    }
}
