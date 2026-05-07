<?php

namespace App\Http\Requests\JobTitle;

use App\Models\JobTitle;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateJobTitleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermissionTo('manage_departments');
    }

    public function rules(): array
    {
        $id = (int) $this->route('id');
        // Resolve department for the unique scope: prefer input, fall back to existing row.
        $deptId = $this->input('department_id') ?? optional(JobTitle::find($id))->department_id;

        return [
            // department_id is intentionally NOT editable. Moving a job title between
            // departments would orphan the linked employees' (department, job_title)
            // consistency. To "move", create a new one and reassign.
            'name'          => [
                'sometimes', 'string', 'max:100',
                Rule::unique('job_titles', 'name')
                    ->ignore($id)
                    ->where(fn ($q) => $q->where('department_id', $deptId)),
            ],
            'is_active'     => ['sometimes', 'boolean'],
        ];
    }
}
