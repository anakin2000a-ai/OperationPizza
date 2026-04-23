<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreManagerPayrollIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'paymentStatus' => 'nullable|in:paid,pending,overdue,failed',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'per_page' => 'nullable|integer|min:1|max:100'
        ];
    }
}