<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FilterSchedulesByEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'master_schedule_id' => ['required', 'exists:master_schedule,id'],
            'employee_id' => ['nullable', 'exists:employees,id'],
        ];
    }
}