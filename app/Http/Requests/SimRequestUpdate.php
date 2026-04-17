<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SimRequestUpdate extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $simId = (int) $this->route('id');

        return [
            'SimCardType' => [
                'sometimes',
                'string',
                'max:255',
                'regex:/^[a-z]+$/',
                Rule::unique('sims', 'SimCardType')->ignore($simId),
            ],
            'simCardInstallment' => ['sometimes', 'numeric', 'min:5', 'max:40'],
        ];
    }

    public function messages(): array
    {
        return [
            'SimCardType.regex' => 'SimCardType must be in lowercase and contain no spaces.',
            'SimCardType.unique' => 'The SimCardType must be unique.',
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