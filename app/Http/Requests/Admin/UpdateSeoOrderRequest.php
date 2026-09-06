<?php

namespace App\Http\Requests\Admin;

use App\Enums\PaymentStatus;
use App\Enums\SeoOrderStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateSeoOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'order_status' => ['required', new Enum(SeoOrderStatus::class)],
            'payment_status' => ['required', new Enum(PaymentStatus::class)],
            'estimated_completion_at' => ['nullable', 'date'],
        ];
    }
}
