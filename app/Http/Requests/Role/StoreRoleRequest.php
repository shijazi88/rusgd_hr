<?php

namespace App\Http\Requests\Role;

use Illuminate\Foundation\Http\FormRequest;

class StoreRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'              => 'required|string|max:100',
            'slug'              => 'required|string|max:100|unique:roles,slug',
            'financial_limit'   => 'sometimes|numeric|min:0',
            'leave_limit_days'  => 'sometimes|integer|min:0',
            'color'             => 'sometimes|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'permission_ids'    => 'sometimes|array',
            'permission_ids.*'  => 'integer|exists:permissions,id',
        ];
    }
}
