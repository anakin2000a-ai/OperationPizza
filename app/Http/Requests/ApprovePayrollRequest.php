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

            'action' => [
                'required',
                'in:approve,reject'
            ],

            'comment' => [
                'nullable',
                'string',
                'max:1000'
            ],
        ];
    }

    public function validationData()
    {
        return array_merge($this->all(), [
            'id' => $this->route('id'),
        ]);
    }
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->action === 'reject' && empty($this->comment)) {
                $validator->errors()->add('comment', 'Comment is required when rejecting.');
            }
        });
    }
}