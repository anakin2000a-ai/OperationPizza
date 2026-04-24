<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreShiftRequirementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'store_id' => ['required', 'exists:stores,id'],
            'day_of_week' => ['required', 'in:sunday,monday,tuesday,wednesday,thursday,friday,saturday'],

            'times' => ['required', 'array', 'min:1'],

            'times.*.start_time' => ['required', 'date_format:H:i'],
            'times.*.end_time' => ['required', 'date_format:H:i', 'after:times.*.start_time'],
            'times.*.skill_id' => ['required', 'exists:skills,id'],
            'times.*.required_employees' => ['required', 'integer', 'min:1'],
        ];
    }
}