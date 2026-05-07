<?php

namespace App\Http\Requests\Role;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $roleId = $this->route('role');

        return [
            'name'             => 'sometimes|required|string|max:100',
            'slug'             => "sometimes|required|string|max:100|unique:roles,slug,{$roleId}",
            'financial_limit'  => 'sometimes|numeric|min:0',
            'leave_limit_days' => 'sometimes|integer|min:0',
            'color'            => 'sometimes|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'permission_ids'   => 'sometimes|array',
            'permission_ids.*' => 'integer|exists:permissions,id',
        ];
    }
}
