<?php

namespace App\Http\Requests\ContractType;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateContractTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermissionTo('manage_contract_types');
    }

    public function rules(): array
    {
        $id = $this->route('id');

        return [
            'name'        => ['sometimes', 'string', 'max:100', Rule::unique('contract_types', 'name')->ignore($id)],
            'description' => ['sometimes', 'nullable', 'string', 'max:500'],
            'is_active'   => ['sometimes', 'boolean'],
            // slug is intentionally not editable — it's a stable code-side handle.
        ];
    }
}
