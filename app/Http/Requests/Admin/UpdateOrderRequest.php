<?php

namespace App\Http\Requests\Admin;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', new Enum(OrderStatus::class)],
            'payment_status' => ['required', new Enum(PaymentStatus::class)],
            'admin_note' => ['nullable', 'string', 'max:2000'],
            'delivery_url' => ['nullable', 'url', 'max:500'],
            'delivery_file' => ['nullable', 'file', 'max:10240', 'mimes:pdf,png,jpg,jpeg,csv,xlsx,docx,zip'],
            'remove_delivery_file' => ['nullable', 'boolean'],
        ];
    }
}
