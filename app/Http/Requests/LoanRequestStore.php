<?php

namespace App\Http\Requests;

use App\Models\Loan;
use Illuminate\Foundation\Http\FormRequest;

class LoanRequestStore extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'loanName' => ['required', 'string', 'max:255', 'unique:loans,loanName'],

            'loanAmount' => [
                'required',
                'numeric',
                function ($attribute, $value, $fail) {
                    $loanType = $this->input('loanType');

                    if ($loanType === 'phone') {
                        $allowed = [250, 500, 750, 1000, 1250, 1500];

                        if (!in_array((float) $value, $allowed, true)) {
                            $fail('Phone loans must be one of: 250, 500, 750, 1000, 1250, 1500.');
                            return;
                        }
                    }

                    if ($loanType === 'car') {
                        $allowed = [2500, 5000, 7500, 10000];

                        if (!in_array((float) $value, $allowed, true)) {
                            $fail('Car loans must be one of: 2500, 5000, 7500, 10000.');
                            return;
                        }
                    }

                    $exists = Loan::where('loanType', $loanType)
                        ->where('loanAmount', $value)
                        ->exists();

                    if ($exists) {
                        $fail('This loanType with this loanAmount already exists.');
                    }
                },
            ],

            'taxValue' => ['required', 'numeric', 'between:5,20'],
            'loanType' => ['required', 'in:phone,car'],
        ];
    }

    public function messages(): array
    {
        return [
            'loanName.required' => 'Loan name is required.',
            'loanName.unique' => 'Loan name must be unique.',
            'loanAmount.required' => 'Loan amount is required.',
            'loanAmount.numeric' => 'Loan amount must be a number.',
            'taxValue.required' => 'Tax value is required.',
            'taxValue.numeric' => 'Tax value must be a number.',
            'taxValue.between' => 'Tax value must be between 5 and 20.',
            'loanType.required' => 'Loan type is required.',
            'loanType.in' => 'Loan type must be phone or car.',
        ];
    }
}