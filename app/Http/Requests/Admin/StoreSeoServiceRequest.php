<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreSeoServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'slug' => ['nullable', 'string', 'max:160', 'alpha_dash', 'unique:seo_services,slug'],
            'description' => ['nullable', 'string', 'max:2000'],
            'unit_price' => ['required', 'numeric', 'min:0.0001', 'max:99999999'],
            'min_quantity' => ['required', 'integer', 'min:1'],
            'max_quantity' => ['required', 'integer', 'gte:min_quantity'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
