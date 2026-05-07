<?php

namespace App\Http\Requests\Purchase;

use Illuminate\Foundation\Http\FormRequest;

class StorePurchaseRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employee_id' => 'required|integer|exists:employees,id',
            'item_name'   => 'required|string|max:255',
            'vendor'      => 'nullable|string|max:255',
            'quantity'    => 'sometimes|integer|min:1',
            'amount'      => 'required|numeric|min:0.01',
            'reason'      => 'required|string|max:1000',
        ];
    }
}
