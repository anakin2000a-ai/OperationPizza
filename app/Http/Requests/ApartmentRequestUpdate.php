<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ApartmentRequestUpdate extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Allow all users (adjust for specific user roles if needed)
    }

    public function rules(): array
    {
        $apartmentId = $this->route('id');  // Get apartment ID from route parameters

        return [
            'Location' => [
                'sometimes',
                'string',
                'max:255',
                // Only apply unique validation if Location is updated
                function ($attribute, $value, $fail) use ($apartmentId) {
                    // Check if the Location has changed
                    if ($this->isLocationChanged($apartmentId, $value)) {
                        // Apply unique validation only if Location is changed
                        if (\App\Models\Apartment::where('Location', $value)->exists()) {
                            $fail('The location must be unique.');
                        }
                    }
                },
            ],
            'ApartmentRent' => 'sometimes|numeric|min:100|max:500', // Validation for ApartmentRent
        ];
    }

    public function messages()
    {
        return [
            'Location.sometimes' => 'The location is not required.',
            'ApartmentRent.sometimes' => 'The apartment rent is not required.',
            'ApartmentRent.numeric' => 'The apartment rent must be a number.',
            'ApartmentRent.min' => 'The apartment rent must be at least 100.',
            'ApartmentRent.max' => 'The apartment rent must not exceed 500.',
            'Location.unique' => 'The location must be unique.',
        ];
    }

    // Method to check if the Location is being changed
    protected function isLocationChanged($apartmentId, $newLocation)
    {
        $apartment = \App\Models\Apartment::find($apartmentId);
        return $apartment && $apartment->Location !== $newLocation;
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