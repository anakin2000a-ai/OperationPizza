<?php

namespace App\Http\Requests;

use App\Models\Loan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LoanRequestUpdate extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $loanId = (int) $this->route('id');
        $loan = Loan::find($loanId);

        return [
            'loanName' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('loans', 'loanName')->ignore($loanId),
            ],

            'loanAmount' => [
                'sometimes',
                'numeric',
                function ($attribute, $value, $fail) use ($loan, $loanId) {
                    if (!$loan) {
                        return;
                    }

                    $loanType = $this->input('loanType', $loan->loanType);
                    $amount = (float) $value;

                    if ($loanType === 'phone') {
                        $allowed = [250.0, 500.0, 750.0, 1000.0, 1250.0, 1500.0];

                        if (!in_array($amount, $allowed, true)) {
                            $fail('Phone loans must be one of: 250, 500, 750, 1000, 1250, 1500.');
                            return;
                        }
                    }

                    if ($loanType === 'car') {
                        $allowed = [2500.0, 5000.0, 7500.0, 10000.0];

                        if (!in_array($amount, $allowed, true)) {
                            $fail('Car loans must be one of: 2500, 5000, 7500, 10000.');
                            return;
                        }
                    }

                    $exists = Loan::where('loanType', $loanType)
                        ->where('loanAmount', $amount)
                        ->where('id', '!=', $loanId)
                        ->exists();

                    if ($exists) {
                        $fail('This loanType with this loanAmount already exists.');
                    }
                },
            ],

            'taxValue' => ['sometimes', 'numeric', 'between:5,20'],

            'loanType' => [
                'sometimes',
                'in:phone,car',
                function ($attribute, $value, $fail) use ($loan, $loanId) {
                    if (!$loan) {
                        return;
                    }

                    $loanAmount = (float) $this->input('loanAmount', $loan->loanAmount);

                    if ($value === 'phone') {
                        $allowed = [250.0, 500.0, 750.0, 1000.0, 1250.0, 1500.0];

                        if (!in_array($loanAmount, $allowed, true)) {
                            $fail('Phone loans must be one of: 250, 500, 750, 1000, 1250, 1500.');
                            return;
                        }
                    }

                    if ($value === 'car') {
                        $allowed = [2500.0, 5000.0, 7500.0, 10000.0];

                        if (!in_array($loanAmount, $allowed, true)) {
                            $fail('Car loans must be one of: 2500, 5000, 7500, 10000.');
                            return;
                        }
                    }

                    $exists = Loan::where('loanType', $value)
                        ->where('loanAmount', $loanAmount)
                        ->where('id', '!=', $loanId)
                        ->exists();

                    if ($exists) {
                        $fail('This loanType with this loanAmount already exists.');
                    }
                },
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'loanName.unique' => 'Loan name must be unique.',
            'loanAmount.numeric' => 'Loan amount must be a number.',
            'taxValue.numeric' => 'Tax value must be a number.',
            'taxValue.between' => 'Tax value must be between 5 and 20.',
            'loanType.in' => 'Loan type must be phone or car.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $loanId = (int) $this->route('id');
        $loan = Loan::find($loanId);

        if (!$loan) {
            return;
        }

        $data = $this->all();

        if ($this->has('loanName') && $this->loanName === $loan->loanName) {
            unset($data['loanName']);
        }

        if ($this->has('loanAmount') && abs((float) $this->loanAmount - (float) $loan->loanAmount) < 0.0001) {
            unset($data['loanAmount']);
        }

        if ($this->has('loanType') && $this->loanType === $loan->loanType) {
            unset($data['loanType']);
        }

        if ($this->has('taxValue') && abs((float) $this->taxValue - (float) $loan->taxValue) < 0.0001) {
            unset($data['taxValue']);
        }

        $this->replace($data);
    }
}