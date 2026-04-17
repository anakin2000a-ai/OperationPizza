<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEmployeeRequest extends FormRequest
{
    public function rules(): array
    {
        $employeeId = $this->route('employee')->id;

        return [
            'FirstName' => 'sometimes|string',
            'LastName' => 'sometimes|string',
            'HaveCar' => 'sometimes|boolean',
            'phone' => 'sometimes|string',
            'email' => 'sometimes|email|unique:employees,email,' . $employeeId,
            'hire_date' => 'sometimes|date',
            'status' => 'sometimes|in:termination,resignation,hired,OJE',

            'ApartmentId' => 'nullable|exists:apartments,id',
            'SimId' => 'nullable|exists:sims,id',
            'taxesId' => 'nullable|exists:taxes,id',
        ];
    }
}