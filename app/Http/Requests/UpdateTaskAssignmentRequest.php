<?php 
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTaskAssignmentRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Adjust if you need role-based checks
    }

    public function rules()
    {
        return [
            'employee_id' => 'sometimes|exists:employees,id',
            'cleaning_task_id' => 'sometimes|exists:cleaning_tasks,id',
            'store_id' => 'sometimes|exists:stores,id',
            'assigned_at' => 'sometimes|date',
        ];
    }
}