<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case WhatsApp = 'whatsapp';
    case PayPal = 'paypal';

    public function label(): string
    {
        return match ($this) {
            self::WhatsApp => 'WhatsApp',
            self::PayPal => 'PayPal',
        };
    }
}
