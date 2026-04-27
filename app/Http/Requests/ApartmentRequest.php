<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
class ApartmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Allow all users (adjust for specific user roles if needed)
    }

    public function rules(): array
    {
        return [
            'Location' => 'required|string|max:255|unique:apartments,Location', // Validation for Location
            'ApartmentRent' => 'required|numeric|min:100|max:200', // Validation for ApartmentRent
      ];
    }

    public function messages()
    {
        return [
            'Location.required' => 'The location is required.',
            'ApartmentRent.required' => 'The apartment rent is required.',
            'ApartmentRent.numeric' => 'The apartment rent must be a number.',
            'ApartmentRent.min' => 'The apartment rent must be at least 100.',
            'ApartmentRent.max' => 'The apartment rent must not exceed 200.',
            'createdBy.required' => 'The createdBy field is required.',
            'createdBy.exists' => 'The specified creator must be a valid user.',
            'Location.unique' => 'The location must be unique.',
        ];
    }

    // Modify the location attribute before validation
    protected function prepareForValidation()
    {
        // Remove spaces and convert the location to lowercase
        if ($this->has('Location')) {
            $this->merge([
                'Location' => strtolower(str_replace(' ', '', $this->Location)),
            ]);
        }
    }
}