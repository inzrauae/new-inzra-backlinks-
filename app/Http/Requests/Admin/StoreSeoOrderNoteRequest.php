<?php

namespace App\Http\Requests\Admin;

use App\Enums\SeoOrderNoteType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreSeoOrderNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', new Enum(SeoOrderNoteType::class)],
            'body' => ['required', 'string', 'max:4000'],
        ];
    }
}
