<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;
class UpdateMasterScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'store_id' => ['sometimes', 'exists:stores,id'],

            'start_date' => [
                'sometimes',
                'date',
                function ($attribute, $value, $fail) {
                    if (date('l', strtotime($value)) !== 'Tuesday') {
                        $fail('start_date must be a Tuesday.');
                    }
                },
            ],

            'end_date' => [
                'sometimes',
                'date',
                function ($attribute, $value, $fail) {
                    if (date('l', strtotime($value)) !== 'Monday') {
                        $fail('end_date must be a Monday.');
                    }
                },
            ],

 
            'schedules' => ['sometimes', 'array', 'min:1'],

            'schedules.*.employee_id' => ['nullable', 'exists:employees,id'],
            'schedules.*.date' => ['required_with:schedules', 'date'],
            'schedules.*.start_time' => ['required_with:schedules', 'date_format:H:i'],
            'schedules.*.end_time' => ['required_with:schedules', 'date_format:H:i'],
            'schedules.*.skill_id' => ['required_with:schedules', 'exists:skills,id'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $startDate = $this->input('start_date');
            $endDate = $this->input('end_date');
            $schedules = $this->input('schedules', []);

            if ($startDate && $endDate) {
                $start = Carbon::parse($startDate)->startOfDay();
                $end = Carbon::parse($endDate)->startOfDay();

                if ($start->gte($end)) {
                    $validator->errors()->add('start_date', 'start_date must be before end_date.');
                }

                if (!$start->copy()->addDays(6)->isSameDay($end)) {
                    $validator->errors()->add(
                        'end_date',
                        'The date range must be exactly 7 days (Tuesday to Monday).'
                    );
                }
            }

            foreach ($schedules as $index => $schedule) {
                if (isset($schedule['start_time'], $schedule['end_time'])) {
                    $startTime = Carbon::createFromFormat('H:i', $schedule['start_time']);
                    $endTime = Carbon::createFromFormat('H:i', $schedule['end_time']);

                    if ($endTime->lte($startTime)) {
                        $validator->errors()->add(
                            "schedules.$index.end_time",
                            'end_time must be after start_time.'
                        );
                    }
                }

                if (isset($schedule['date']) && $startDate && $endDate) {
                    $scheduleDate = Carbon::parse($schedule['date'])->startOfDay();
                    $start = Carbon::parse($startDate)->startOfDay();
                    $end = Carbon::parse($endDate)->startOfDay();

                    if ($scheduleDate->lt($start) || $scheduleDate->gt($end)) {
                        $validator->errors()->add(
                            "schedules.$index.date",
                            'schedule date must be within the master schedule range.'
                        );
                    }
                }
            }
        });
    }
}