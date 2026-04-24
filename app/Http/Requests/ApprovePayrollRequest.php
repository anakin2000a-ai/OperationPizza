<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ApprovePayrollRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // you already use middleware
    }

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                'exists:payroll,id'
            ],
        ];
    }
    public function validationData()
    {
        return array_merge($this->all(), [
            'id' => $this->route('id'),
        ]);
    }
}