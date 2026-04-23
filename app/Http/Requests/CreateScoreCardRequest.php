<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateScoreCardRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $storeParam = $this->route('store');

        $store = \App\Models\Store::where('store', $storeParam)->first();

        if (!$store) {
            abort(404, 'Store not found');
        }

        return [
            'schedule_week_id' => [
                'required',
                \Illuminate\Validation\Rule::exists('master_schedule', 'id')
                    ->where(fn ($q) => $q->where('store_id', $store->id)),
            ],
        ];
    }
}