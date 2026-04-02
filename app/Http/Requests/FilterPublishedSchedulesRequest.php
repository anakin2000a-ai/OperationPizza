<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FilterPublishedSchedulesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'store_id' => ['nullable', 'exists:stores,id'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if (!$this->start_date && !$this->end_date) {
                $validator->errors()->add(
                    'date',
                    'At least start_date or end_date is required.'
                );
            }
        });
    }
}