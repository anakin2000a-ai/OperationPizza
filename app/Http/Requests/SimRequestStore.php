<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SimRequestStore extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'SimCardType' => 'required|unique:sims,SimCardType,' . $this->route('sim') . '|regex:/^[a-z]+$/',  // Ensure lowercase and no spaces
            'simCardInstallment' => 'required|numeric|min:5|max:40',  // Validate simCardInstallment
        ];
    }

    public function messages()
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

    // Modify the SimCardType attribute before validation
    protected function prepareForValidation()
    {
        // Remove spaces and convert the SimCardType to lowercase
        if ($this->has('SimCardType')) {
            $this->merge([
                'SimCardType' => strtolower(str_replace(' ', '', $this->SimCardType)),
            ]);
        }
    }
}