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

                    $loanAmount = $this->input('loanAmount', $loan->loanAmount);

                    if ($value === 'phone') {
                        $allowed = [250, 500, 750, 1000, 1250, 1500];
                        if (!in_array((float) $loanAmount, $allowed, true)) {
                            $fail('Phone loans must be one of: 250, 500, 750, 1000, 1250, 1500.');
                            return;
                        }
                    }

                    if ($value === 'car') {
                        $allowed = [2500, 5000, 7500, 10000];
                        if (!in_array((float) $loanAmount, $allowed, true)) {
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

        if ($this->has('loanAmount') && (float) $this->loanAmount === (float) $loan->loanAmount) {
            unset($data['loanAmount']);
        }

        if ($this->has('loanType') && $this->loanType === $loan->loanType) {
            unset($data['loanType']);
        }

        if ($this->has('taxValue') && (float) $this->taxValue === (float) $loan->taxValue) {
            unset($data['taxValue']);
        }

        $this->replace($data);
    }
}