<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCleaningTaskRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Authorization logic (customize based on roles if needed)
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255|unique:cleaning_tasks,name,' . $this->route('cleaning_task')->id,
            'frequency' => 'required|in:daily,weekly,monthly',
            'times_per_frequency' => 'required|integer|min:1',
        ];
    }
}