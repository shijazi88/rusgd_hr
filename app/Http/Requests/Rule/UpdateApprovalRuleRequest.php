<?php

namespace App\Http\Requests\Rule;

use Illuminate\Foundation\Http\FormRequest;

class UpdateApprovalRuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type'           => 'sometimes|required|string|in:financial,leave',
            'description'    => 'sometimes|required|string|max:255',
            'min_value'      => 'sometimes|required|numeric|min:0',
            'max_value'      => 'sometimes|required|numeric|min:0',
            'approver_role'  => 'sometimes|required|string|max:100',
            'approver_label' => 'sometimes|required|string|max:100',
            'priority'       => 'sometimes|required|string|in:low,medium,high,critical',
            'is_active'      => 'sometimes|boolean',
        ];
    }
}
