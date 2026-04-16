<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Sim;  // Import the Sim model

class SimRequestUpdate extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Allow all users (adjust for specific user roles if needed)
    }

    public function rules(): array
    {
        $simId = $this->route('sim'); // Get the SIM ID from the route
        $sim = Sim::find($simId); // Get the existing SIM record

        return [
            'SimCardType' => [
                'nullable',
                'string',
                'regex:/^[a-z]+$/',  // Ensure it's lowercase and contains no spaces
                function ($attribute, $value, $fail) use ($sim) {
                    // Check if SimCardType is changing
                    if ($sim && $sim->SimCardType !== $value) {
                        // If it's changing, ensure it's unique
                        $existingSim = Sim::where('SimCardType', $value)->first();
                        if ($existingSim) {
                            $fail('The SimCardType must be unique.');
                        }
                    }
                },
            ],
            'simCardInstallment' => 'nullable|numeric|min:5|max:40',  // Not required for update
        ];
    }

    public function messages()
    {
        return [
            'SimCardType.regex' => 'SimCardType must be in lowercase and contain no spaces.',
            'simCardInstallment.numeric' => 'The sim card installment must be a number.',
            'simCardInstallment.min' => 'The sim card installment must be at least 5.',
            'simCardInstallment.max' => 'The sim card installment must not exceed 40.',
        ];
    }

    // Modify the SimCardType attribute before validation
    protected function prepareForValidation()
    {
        // If SimCardType exists, remove spaces and convert it to lowercase
        if ($this->has('SimCardType')) {
            $newSimCardType = strtolower(str_replace(' ', '', $this->SimCardType));  // Remove spaces and convert to lowercase
            
            // Get the current SimCardType from the database
            $simId = $this->route('sim');
            $sim = Sim::find($simId);

            // Check if the SimCardType is the same as the current one in the database
            if ($sim && $sim->SimCardType === $newSimCardType) {
                // If it's the same, use the existing value in the database
                $this->merge([
                    'SimCardType' => $sim->SimCardType, // Keep the existing value
                ]);
            } else {
                // If it's different, apply the new value
                $this->merge([
                    'SimCardType' => $newSimCardType, // Set the updated value
                ]);
            }
        }
    }
}