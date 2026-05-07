<?php

namespace App\Http\Requests\ContractType;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class StoreContractTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermissionTo('manage_contract_types');
    }

    public function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'max:100', 'unique:contract_types,name'],
            'slug'        => ['nullable', 'string', 'max:100', 'unique:contract_types,slug'],
            'description' => ['nullable', 'string', 'max:500'],
            'is_active'   => ['sometimes', 'boolean'],
        ];
    }

    /**
     * Auto-generate a slug from name if not provided. Stable handle for code paths
     * that still want to compare against well-known values like 'permanent'.
     */
    protected function prepareForValidation(): void
    {
        if (!$this->filled('slug') && $this->filled('name')) {
            $this->merge(['slug' => Str::slug($this->input('name'), '_')]);
        }
    }

    public function messages(): array
    {
        return [
            'name.required' => 'اسم نوع العقد مطلوب.',
            'name.unique'   => 'هذا الاسم مستخدم بالفعل.',
            'slug.unique'   => 'المعرّف مستخدم بالفعل.',
        ];
    }
}
