<?php

namespace App\Http\Requests\Employee;

use App\Enums\EmployeeStatus;
use App\Models\ContractType;
use App\Models\Employee;
use App\Models\JobTitle;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermissionTo('edit_employees');
    }

    public function rules(): array
    {
        return [
            'name'                => ['sometimes', 'string', 'max:100'],
            'phone'               => ['sometimes', 'nullable', 'string', 'max:20'],
            'department_id'       => ['sometimes', 'integer', 'exists:departments,id'],
            'job_title_id'        => ['sometimes', 'integer', 'exists:job_titles,id'],
            'manager_id'          => ['sometimes', 'nullable', 'integer', 'exists:employees,id'],
            'contract_type_id'    => ['sometimes', 'integer', 'exists:contract_types,id'],
            'base_salary'         => ['sometimes', 'numeric', 'min:0'],
            'housing_allowance'   => ['sometimes', 'numeric', 'min:0'],
            'transport_allowance' => ['sometimes', 'numeric', 'min:0'],
            'hire_date'           => ['sometimes', 'date', 'before_or_equal:today'],
            'status'              => ['sometimes', Rule::enum(EmployeeStatus::class)],
        ];
    }

    /**
     * Two cross-cutting rules that can't be expressed in `rules()`:
     *
     *   1. Cross-FK consistency — the resulting (department_id, job_title_id)
     *      pair must be coherent. Falls back to the employee's existing values
     *      for any field not in the request.
     *
     *   2. Activation state — when a request switches the employee to a
     *      different job_title_id or contract_type_id, the new one must be
     *      active. Keeping the SAME id (no change) is always allowed even if
     *      that lookup row was deactivated later — that's how history is
     *      preserved.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            if ($v->errors()->isNotEmpty()) return;

            // Route param is `{employee}` (apiResource singular form), NOT `id`.
            $current = Employee::find($this->route('employee'));
            if (!$current) return;

            // ── 1. Cross-FK consistency ────────────────────────────────────
            if ($this->has('department_id') || $this->has('job_title_id')) {
                $deptId    = (int) ($this->input('department_id', $current->department_id));
                $jobTitleId = (int) ($this->input('job_title_id',  $current->job_title_id));

                $jt = JobTitle::find($jobTitleId);
                if ($jt && (int) $jt->department_id !== $deptId) {
                    $v->errors()->add('job_title_id', 'المسمى الوظيفي لا ينتمي إلى القسم المحدد.');
                }
            }

            // ── 2. Can't switch TO an inactive lookup row ──────────────────
            if ($this->has('job_title_id')) {
                $newId = (int) $this->input('job_title_id');
                if ($newId !== (int) $current->job_title_id) {
                    $jt = JobTitle::find($newId);
                    if ($jt && !$jt->is_active) {
                        $v->errors()->add('job_title_id', 'لا يمكن إسناد مسمى وظيفي غير مفعّل.');
                    }
                }
            }

            if ($this->has('contract_type_id')) {
                $newId = (int) $this->input('contract_type_id');
                if ($newId !== (int) $current->contract_type_id) {
                    $ct = ContractType::find($newId);
                    if ($ct && !$ct->is_active) {
                        $v->errors()->add('contract_type_id', 'لا يمكن إسناد نوع عقد غير مفعّل.');
                    }
                }
            }
        });
    }
}
