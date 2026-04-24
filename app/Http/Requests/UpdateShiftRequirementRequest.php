<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateShiftRequirementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'day_of_week' => ['sometimes', 'in:sunday,monday,tuesday,wednesday,thursday,friday,saturday'],

            'times' => ['sometimes', 'array'],

            'times.*.id' => ['nullable', 'exists:shift_requirement_times,id'],
            'times.*.start_time' => ['required_with:times', 'date_format:H:i'],
            'times.*.end_time' => ['required_with:times', 'date_format:H:i'],
            'times.*.skill_id' => ['required_with:times', 'exists:skills,id'],
            'times.*.required_employees' => ['required_with:times', 'integer', 'min:1'],
        ];
    }
}