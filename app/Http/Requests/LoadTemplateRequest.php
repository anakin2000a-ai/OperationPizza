<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoadTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'template_id' => ['required', 'exists:schedule_templates,id'],
            'start_date' => [
                'required',
                'date',
                function ($attribute, $value, $fail) {
                    if (date('l', strtotime($value)) !== 'Tuesday') {
                        $fail('start_date must be a Tuesday.');
                    }
                },
            ],
        ];
    }
}