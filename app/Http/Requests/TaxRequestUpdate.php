<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Tax;  // Import the Tax model

class TaxRequestUpdate extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Allow all users (adjust for specific user roles if needed)
    }

    public function rules(): array
    {
        $taxId = $this->route('tax'); // Get the Tax ID from the route
        $tax = Tax::find($taxId); // Get the existing tax record

        return [
            'taxAmount' => 'sometimes|numeric|between:0,300', // taxAmount is optional for updates
            'taxtype' => [
                'sometimes',
                'string',
                'in:w2,1099', // taxtype must be either 'w2' or '1099'
                function ($attribute, $value, $fail) use ($tax) {
                    // Check if taxtype is being changed
                    if ($tax && $tax->taxtype !== $value) {
                        // If it's changing, ensure it's unique
                        $existingTax = Tax::where('taxtype', $value)->first();
                        if ($existingTax) {
                            $fail('The taxtype must be unique.');
                        }
                    }
                },
            ],
        ];
    }

    public function messages()
    {
        return [
            'taxAmount.numeric' => 'The tax amount must be a number.',
            'taxAmount.between' => 'The tax amount must be between 0 and 300.',
            'taxtype.in' => 'Tax type must be either "w2" or "1099".',
        ];
    }

    // Modify the taxtype attribute before validation
    protected function prepareForValidation()
    {
        // If taxtype exists in the request, remove spaces and convert it to lowercase
        if ($this->has('taxtype')) {
            $this->merge([
                'taxtype' => strtolower(str_replace(' ', '', $this->taxtype)),  // Remove spaces and convert to lowercase
            ]);
        }

        // Check if taxtype is the same as the current value in the database, and if so, don't change it
        $taxId = $this->route('tax');
        $tax = Tax::find($taxId);

        if ($tax && $tax->taxtype === $this->taxtype) {
            // If taxtype hasn't changed, use the current value from the database
            $this->merge([
                'taxtype' => $tax->taxtype,  // Keep the existing taxtype value
            ]);
        }
    }
}