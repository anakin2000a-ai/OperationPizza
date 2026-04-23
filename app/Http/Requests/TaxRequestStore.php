<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TaxRequestStore extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Allow all users (adjust for specific user roles if needed)
    }

    public function rules(): array
    {
        return [
            'taxAmount' => 'required|numeric|between:0,150', // taxAmount is required and between 0 to 300
            'taxtype' => 'required|in:w2,1099|unique:taxes,taxtype',  // taxtype must be one of 'w2' or '1099' and unique
        ];
    }

    public function messages()
    {
        return [
            'taxAmount.required' => 'Tax amount is required.',
            'taxAmount.numeric' => 'Tax amount must be a number.',
            'taxAmount.between' => 'Tax amount must be between 0 and 300.',
            'taxtype.required' => 'Tax type is required.',
            'taxtype.in' => 'Tax type must be either "w2" or "1099".',
            'taxtype.unique' => 'Tax type must be unique.',
        ];
    }

    // Modify the taxtype attribute before validation
    protected function prepareForValidation()
    {
        // Convert taxtype to lowercase and remove spaces
        if ($this->has('taxtype')) {
            $this->merge([
                'taxtype' => strtolower(str_replace(' ', '', $this->taxtype)),  // Remove spaces and convert to lowercase
            ]);
        }
    }
}