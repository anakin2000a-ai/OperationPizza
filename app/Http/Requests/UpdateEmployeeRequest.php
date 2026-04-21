<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $store = $this->route('store');
        $storeId = is_object($store) ? $store->id : $store;
        $employeeId = (int) $this->route('employeeId');

        return [
            'FirstName' => ['sometimes', 'string', 'max:255'],
            'LastName'  => ['sometimes', 'string', 'max:255'],
            'HaveCar'   => ['sometimes', 'boolean'],
            'phone'     => ['sometimes', 'string', 'max:50'],
            'email'     => [
                'sometimes',
                'email',
                'max:255',
                Rule::unique('employees', 'email')
                    ->ignore($employeeId)
                    ->where(fn ($q) => $q->where('store_id', $storeId)),
            ],
            'hire_date' => ['sometimes', 'date'],
            'status'    => ['sometimes', Rule::in(['termination', 'resignation', 'hired', 'OJE'])],

            'ApartmentId' => ['nullable', 'exists:apartments,id'],
            'SimId'       => ['nullable', 'exists:sims,id'],
            'taxesId'     => ['nullable', 'exists:taxes,id'],
        ];
    }

    /**
     * ✅ CRITICAL: validate employee belongs to store
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $store = $this->route('store');
            $employeeId = (int) $this->route('employeeId');

            $storeId = is_object($store) ? $store->id : $store;

            if (!\App\Models\Employee::where('id', $employeeId)
                ->where('store_id', $storeId)
                ->exists()
            ) {
                $validator->errors()->add('employee', 'Employee does not belong to this store');
            }
        });
    }
}