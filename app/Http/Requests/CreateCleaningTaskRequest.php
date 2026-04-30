<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateCleaningTaskRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Authorization logic (customize based on roles if needed)
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255|unique:cleaning_tasks,name', // Ensure the name is unique
            'frequency' => 'required|in:daily,weekly,monthly', // Valid frequencies
            'times_per_frequency' => 'required|integer|min:1' // Positive integer for times
        ];
    }
}