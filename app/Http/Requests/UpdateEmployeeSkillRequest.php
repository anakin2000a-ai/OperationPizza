<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEmployeeSkillRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('employee_skill') ?? $this->route('id');

        // جلب القيم الحالية من DB
        $employeeSkill = \App\Models\EmployeeSkill::find($id);

        $employeeId = $this->employee_id ?? $employeeSkill?->employee_id;
        $skillId = $this->skill_id ?? $employeeSkill?->skill_id;

        return [
            'employee_id' => ['sometimes', 'exists:employees,id'],

            'skill_id' => [
                'sometimes',
                'exists:skills,id',
                Rule::unique('employee_skills')
                    ->where(function ($query) use ($employeeId) {
                        return $query->where('employee_id', $employeeId);
                    })
                    ->ignore($id),
            ],

            'rating' => ['sometimes', 'numeric', 'min:0', 'max:100'],
        ];
    }
}