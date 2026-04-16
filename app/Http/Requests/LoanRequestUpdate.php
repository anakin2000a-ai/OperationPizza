<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Loan;

class LoanRequestUpdate extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $loanId = $this->route('loan');
        $loan = Loan::find($loanId);

        return [
            'loanName' => [
                'nullable',
                'string',
                function ($attribute, $value, $fail) use ($loanId) {
                    $exists = Loan::where('loanName', $value)
                        ->where('id', '!=', $loanId)
                        ->exists();

                    if ($exists) {
                        $fail('Loan name must be unique.');
                    }
                },
            ],

            'loanAmount' => [
                'nullable',
                'numeric',
                function ($attribute, $value, $fail) use ($loan, $loanId) {

                    $loanType = $this->input('loanType', $loan->loanType);

                    // validate by type
                    if ($loanType === 'phone') {
                        $allowed = [250, 500, 750, 1000, 1250, 1500];
                        if (!in_array($value, $allowed)) {
                            $fail('Phone loans must be one of: 250,500,750,1000,1250,1500.');
                            return;
                        }
                    }

                    if ($loanType === 'car') {
                        $allowed = [2500, 5000, 7500, 10000];
                        if (!in_array($value, $allowed)) {
                            $fail('Car loans must be one of: 2500,5000,7500,10000.');
                            return;
                        }
                    }

                    // prevent duplicate
                    $exists = Loan::where('loanType', $loanType)
                        ->where('loanAmount', $value)
                        ->where('id', '!=', $loanId)
                        ->exists();

                    if ($exists) {
                        $fail('This loanType with this loanAmount already exists.');
                    }
                }
            ],

            'taxValue' => 'nullable|numeric|between:5,20',

            'loanType' => [
                'nullable',
                'in:phone,car',
                function ($attribute, $value, $fail) use ($loan, $loanId) {

                    $loanAmount = $this->input('loanAmount', $loan->loanAmount);

                    if ($value === 'phone') {
                        $allowed = [250, 500, 750, 1000, 1250, 1500];
                        if (!in_array($loanAmount, $allowed)) {
                            $fail('Phone loans must be one of: 250,500,750,1000,1250,1500.');
                            return;
                        }
                    }

                    if ($value === 'car') {
                        $allowed = [2500, 5000, 7500, 10000];
                        if (!in_array($loanAmount, $allowed)) {
                            $fail('Car loans must be one of: 2500,5000,7500,10000.');
                            return;
                        }
                    }

                    // prevent duplicate
                    $exists = Loan::where('loanType', $value)
                        ->where('loanAmount', $loanAmount)
                        ->where('id', '!=', $loanId)
                        ->exists();

                    if ($exists) {
                        $fail('This loanType with this loanAmount already exists.');
                    }
                }
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'loanAmount.numeric' => 'Loan amount must be a number.',
            'taxValue.numeric' => 'Tax value must be a number.',
            'taxValue.between' => 'Tax value must be between 5 and 20.',
            'loanType.in' => 'Loan type must be phone or car.',
        ];
    }

    /**
     * 🔥 THIS IS THE IMPORTANT PART
     */
    protected function prepareForValidation()
    {
        $loanId = $this->route('loan');
        $loan = Loan::find($loanId);

        if (!$loan) {
            return;
        }

        $data = $this->all();

        // ✅ Ignore same loanName
        if ($this->has('loanName') && $this->loanName === $loan->loanName) {
            unset($data['loanName']);
        }

        // ✅ Ignore same loanAmount
        if ($this->has('loanAmount') && $this->loanAmount == $loan->loanAmount) {
            unset($data['loanAmount']);
        }

        // ✅ Ignore same loanType
        if ($this->has('loanType') && $this->loanType === $loan->loanType) {
            unset($data['loanType']);
        }

        $this->replace($data);
    }
}