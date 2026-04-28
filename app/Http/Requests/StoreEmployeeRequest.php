<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $store = $this->route('store');
        $storeId = is_object($store) ? $store->id : $store;

        return [
            'FirstName' => ['required', 'string', 'max:255'],
            'LastName'  => ['required', 'string', 'max:255'],
            'HaveCar'   => ['required', 'boolean'],
            'phone'     => ['required', 'string', 'max:50'],
            'email'     => [
                'required',
                'email',
                'max:255',
                Rule::unique('employees', 'email')
                    ->where(fn ($q) => $q->where('store_id', $storeId)),
            ],
            'hire_date' => ['required', 'date'],
            'status'    => ['required', Rule::in(['termination', 'resignation', 'hired', 'OJE'])],

            'ApartmentId' => ['nullable', 'exists:apartments,id'],
            'SimId'       => ['nullable', 'exists:sims,id'],
            'taxesId'     => ['required', 'exists:taxes,id'],
        ];
    }
}