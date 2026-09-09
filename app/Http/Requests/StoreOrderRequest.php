<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'target_url' => ['nullable', 'url', 'max:500'],
            'anchor_text' => ['nullable', 'string', 'max:255'],
            'target_country' => ['nullable', 'string', 'max:100'],
        ];
    }
}
