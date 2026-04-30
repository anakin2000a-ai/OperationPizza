<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubmitTaskImageRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Allow all users for now (or restrict based on roles)
    }

    public function rules()
    {
        return [
            'task_assignment_id' => 'required|exists:task_assignments,id',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ];
    }
}