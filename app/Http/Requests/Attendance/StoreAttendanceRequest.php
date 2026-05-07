<?php

namespace App\Http\Requests\Attendance;

use Illuminate\Foundation\Http\FormRequest;

class StoreAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employee_id' => 'required|integer|exists:employees,id',
            'date'        => 'required|date',
            'check_in'    => 'required|date_format:H:i',
            'check_out'   => 'nullable|date_format:H:i',
            'status'      => 'sometimes|string|in:present,absent,late,on_leave,remote',
        ];
    }
}
