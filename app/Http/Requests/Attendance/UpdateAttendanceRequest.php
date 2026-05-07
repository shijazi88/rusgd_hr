<?php

namespace App\Http\Requests\Attendance;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'check_in'     => 'sometimes|date_format:H:i',
            'check_out'    => 'sometimes|date_format:H:i',
            'status'       => 'sometimes|string|in:present,absent,late,on_leave,remote',
            'late_minutes' => 'sometimes|integer|min:0',
        ];
    }
}
