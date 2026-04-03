<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $details = $this->input('details', []);

        $cleanedDetails = array_map(function ($detail) {
            if (isset($detail['day_of_week']) && $detail['day_of_week'] !== null) {
                $detail['day_of_week'] = strtolower(
                    preg_replace('/\s+/', '', trim($detail['day_of_week']))
                );
            }

            return $detail;
        }, $details);

        $this->merge([
            'details' => $cleanedDetails,
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],

            'details' => ['required', 'array', 'min:1'],

            'details.*.day_of_week' => [
                'required',
                'in:sunday,monday,tuesday,wednesday,thursday,friday,saturday',
            ],

            'details.*.start_time' => ['required', 'date_format:H:i'],
            'details.*.end_time' => ['required', 'date_format:H:i'],
            'details.*.skill_id' => ['required', 'exists:skills,id'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            foreach ($this->input('details', []) as $index => $detail) {
                if (
                    isset($detail['start_time'], $detail['end_time']) &&
                    $detail['end_time'] <= $detail['start_time']
                ) {
                    $validator->errors()->add(
                        "details.$index.end_time",
                        'end_time must be after start_time.'
                    );
                }
            }
        });
    }
}