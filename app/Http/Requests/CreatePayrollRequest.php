<?php 

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreatePayrollRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Allow the request to proceed (adjust this if you have specific authorization logic)
    }

    public function rules(): array
    {
        return [
            'scoreCardId' => [
                'required',
                'exists:score_cards,id', // Validate that the scoreCardId exists in the score_cards table
                // Custom validation to check if this scoreCardId already exists in the payroll table
                function ($attribute, $value, $fail) {
                    // Check if the scoreCardId already exists in the payroll table
                    $payrollExists = \App\Models\Payroll::where('scorecardId', $value)->exists();

                    if ($payrollExists) {
                        $fail('A payroll entry already exists for this scorecard.');
                    }
                },
            ],
        ];
    }
}