<?php

namespace App\Http\Requests\Admin;

use App\Enums\PublicationStatus;
use App\Rules\SafeUrl;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreSeoPublicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'publisher_name' => ['nullable', 'string', 'max:255'],
            'publisher_url' => ['nullable', 'string', 'max:500', new SafeUrl],
            'published_url' => ['nullable', 'string', 'max:500', new SafeUrl],
            'target_url' => ['nullable', 'string', 'max:500', new SafeUrl],
            'anchor_text' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:100'],
            'publication_date' => ['nullable', 'date'],
            'status' => ['required', new Enum(PublicationStatus::class)],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
