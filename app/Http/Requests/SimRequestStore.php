<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SimRequestStore extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'SimCardType' => ['required', 'string', 'regex:/^[a-z]+$/', 'max:255', Rule::unique('sims', 'SimCardType')],
            'simCardInstallment' => ['required', 'numeric', 'min:5', 'max:40'],
        ];
    }

    public function messages(): array
    {
        return [
            'SimCardType.required' => 'SimCardType is required and must be lowercase with no spaces.',
            'SimCardType.unique' => 'The SimCardType must be unique.',
            'SimCardType.regex' => 'SimCardType must be in lowercase and contain no spaces.',
            'simCardInstallment.required' => 'The sim card installment is required.',
            'simCardInstallment.numeric' => 'The sim card installment must be a number.',
            'simCardInstallment.min' => 'The sim card installment must be at least 5.',
            'simCardInstallment.max' => 'The sim card installment must not exceed 40.',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('SimCardType')) {
            $this->merge([
                'SimCardType' => strtolower(str_replace(' ', '', (string) $this->SimCardType)),
            ]);
        }
    }
}