<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateTaskAssignmentRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Adjust if you need role-based checks
    }

    public function rules()
    {
        return [
            'employee_id' => [
                'required',
                'exists:employees,id', // Ensure the employee exists in the employees table
                Rule::unique('task_assignments')->where(function ($query) {
                    return $query->where('employee_id', $this->employee_id)
                                 ->where('cleaning_task_id', $this->cleaning_task_id);
                }),
            ],
            'cleaning_task_id' => [
                'required',
                'exists:cleaning_tasks,id', // Ensure the cleaning task exists in the cleaning_tasks table
                Rule::unique('task_assignments')->where(function ($query) {
                    return $query->where('employee_id', $this->employee_id)
                                 ->where('cleaning_task_id', $this->cleaning_task_id);
                }),
            ],
            'assigned_at' => 'required|date',
        ];
    }
}