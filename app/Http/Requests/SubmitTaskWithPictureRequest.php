<?php
// app/Http/Requests/SubmitTaskWithPictureRequest.php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubmitTaskWithPictureRequest extends FormRequest
{
    public function authorize()
    {
        return true; // You can add additional authorization checks if needed
    }

    public function rules()
    {
        return [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Validate the image
            'status' => 'required|in:completed', // Ensure the status is 'completed'
        ];
    }
}