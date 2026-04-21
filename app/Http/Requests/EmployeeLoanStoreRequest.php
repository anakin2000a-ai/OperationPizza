<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EmployeeLoanStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'employeeId' => ['required', 'integer', 'exists:employees,id'],
            'loansId'    => ['required', 'integer', 'exists:loans,id'],
        ];
    }
}