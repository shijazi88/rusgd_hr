<?php

namespace App\Http\Requests\Rule;

use Illuminate\Foundation\Http\FormRequest;

class StoreApprovalRuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type'           => 'required|string|in:financial,leave',
            'description'    => 'required|string|max:255',
            'min_value'      => 'required|numeric|min:0',
            'max_value'      => 'required|numeric|min:0',
            'approver_role'  => 'required|string|max:100',
            'approver_label' => 'required|string|max:100',
            'priority'       => 'required|string|in:low,medium,high,critical',
            'is_active'      => 'sometimes|boolean',
        ];
    }
}
