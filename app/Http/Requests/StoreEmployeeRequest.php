<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEmployeeRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'FirstName' => 'required|string',
            'LastName' => 'required|string',
            'HaveCar' => 'required|boolean',
            'phone' => 'required|string',
            'email' => 'required|email|unique:employees,email',
            'hire_date' => 'required|date',
            'status' => 'required|in:termination,resignation,hired,OJE',

            'ApartmentId' => 'nullable|exists:apartments,id',
            'SimId' => 'nullable|exists:sims,id',
            'taxesId' => 'nullable|exists:taxes,id',
        ];
    }
}