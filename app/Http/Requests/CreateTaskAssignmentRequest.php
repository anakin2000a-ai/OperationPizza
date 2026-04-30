<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateTaskAssignmentRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Adjust if you need role-based checks
    }

    public function rules()
    {
        return [
            'employee_id' => 'required|exists:employees,id',
            'cleaning_task_id' => 'required|exists:cleaning_tasks,id',
             'assigned_at' => 'required|date',
        ];
    }
}