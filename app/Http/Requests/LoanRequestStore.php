<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Loan;

class LoanRequestStore extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'loanName' => 'required|unique:loans,loanName',

            'loanAmount' => [
                'required',
                'numeric',
                function ($attribute, $value, $fail) {

                    $loanType = $this->input('loanType');

                    // ✅ RULE 1: validate based on loanType
                    if ($loanType === 'phone') {
                        $allowed = [250, 500, 750, 1000, 1250, 1500];

                        if (!in_array($value, $allowed)) {
                            $fail('Phone loans must be one of: 250,500,750,1000,1250,1500.');
                        }
                    }

                    if ($loanType === 'car') {
                        $allowed = [2500, 5000, 7500, 10000];

                        if (!in_array($value, $allowed)) {
                            $fail('Car loans must be one of: 2500,5000,7500,10000.');
                        }
                    }

                    // ❌ RULE 2: prevent duplicate (loanType + loanAmount)
                    $exists = Loan::where('loanType', $loanType)
                        ->where('loanAmount', $value)
                        ->exists();

                    if ($exists) {
                        $fail('This loanType with this loanAmount already exists.');
                    }
                }
            ],

            'taxValue' => 'required|numeric|between:5,20',

            'loanType' => 'required|in:phone,car',
        ];
    }

    public function messages()
    {
        return [
            'loanName.required' => 'Loan name is required.',
            'loanName.unique' => 'Loan name must be unique.',

            'loanAmount.required' => 'Loan amount is required.',
            'loanAmount.numeric' => 'Loan amount must be a number.',

            'taxValue.required' => 'Tax value is required.',
            'taxValue.between' => 'Tax value must be between 5 and 20.',

            'loanType.required' => 'Loan type is required.',
            'loanType.in' => 'Loan type must be phone or car.',
        ];
    }
}