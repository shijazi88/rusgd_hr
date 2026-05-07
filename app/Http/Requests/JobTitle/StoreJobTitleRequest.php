<?php

namespace App\Http\Requests\JobTitle;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreJobTitleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermissionTo('manage_departments');
    }

    public function rules(): array
    {
        return [
            'department_id' => ['required', 'integer', 'exists:departments,id'],
            'name'          => [
                'required', 'string', 'max:100',
                Rule::unique('job_titles', 'name')->where(
                    fn ($q) => $q->where('department_id', $this->input('department_id'))
                ),
            ],
            'is_active'     => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'department_id.required' => 'القسم مطلوب.',
            'department_id.exists'   => 'القسم المحدد غير موجود.',
            'name.required'          => 'اسم المسمى الوظيفي مطلوب.',
            'name.unique'            => 'هذا المسمى مستخدم بالفعل في هذا القسم.',
        ];
    }
}
