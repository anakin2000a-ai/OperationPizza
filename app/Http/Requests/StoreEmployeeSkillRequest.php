<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEmployeeSkillRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employee_id' => ['required', 'exists:employees,id'],
            'skill_id' => ['required', 'exists:skills,id'],

            'rating' => ['required', 'numeric', 'min:0', 'max:100'],

            // منع التكرار
            'skill_id' => [
                'required',
                'exists:skills,id',
                Rule::unique('employee_skills')
                    ->where(function ($query) {
                        return $query->where('employee_id', $this->employee_id);
                    }),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'rating.min' => 'Rating must be at least 0',
            'rating.max' => 'Rating must not exceed 100',
            'skill_id.unique' => 'This employee already has this skill',
        ];
    }
}