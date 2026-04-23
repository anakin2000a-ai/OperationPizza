<?php 

namespace App\Http\Requests;

use App\Models\Payroll;
use App\Models\ScoreCard;
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
                'integer',
                'exists:score_cards,id',
                function ($attribute, $value, $fail) {
                    $scoreCard = ScoreCard::find($value);

                    if (!$scoreCard) {
                        return;
                    }

                    if (Payroll::where('scorecardId', $value)->exists()) {
                        $fail('A payroll entry already exists for this scorecard.');
                        return;
                    }

                    if ($scoreCard->ScoreCardStatus !== 'pending') {
                        $fail('Payroll can only be created for pending score cards.');
                        return;
                    }

                    if ($scoreCard->finalSalary <= 0) {
                        $fail('Cannot create payroll for scorecard with zero or negative salary.');
                    }
                },
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'scoreCardId.required' => 'ScoreCard is required.',
            'scoreCardId.integer' => 'ScoreCard must be a valid ID.',
            'scoreCardId.exists' => 'ScoreCard not found.',
        ];
    }

    // public function rules(): array
    // {
    //     return [
    //         'scoreCardId' => [
    //             'required',
    //             'exists:score_cards,id', // Validate that the scoreCardId exists in the score_cards table
    //             // Custom validation to check if this scoreCardId already exists in the payroll table
    //             function ($attribute, $value, $fail) {
    //                 // Check if the scoreCardId already exists in the payroll table
    //                 $payrollExists = \App\Models\Payroll::where('scorecardId', $value)->exists();

    //                 if ($payrollExists) {
    //                     $fail('A payroll entry already exists for this scorecard.');
    //                 }
    //             },
    //         ],
    //     ];
    // }
}