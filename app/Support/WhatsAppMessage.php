<?php

namespace App\Support;

use App\Models\Order;

final class WhatsAppMessage
{
    public static function forOrder(Order $order): string
    {
        $item = $order->items->first();

        $lines = [
            "Hi INZRA! I'd like to order: {$item->product_name} (\${$item->price}) — SKU {$item->sku}.",
            'Order ref: '.$order->order_number,
            'Target URL: '.($item->target_url ?: ''),
            'Anchor text preference: '.($item->anchor_text ?: ''),
        ];

        return implode("\n", $lines);
    }

    public static function url(string $message): string
    {
        return 'https://wa.me/'.config('inzra.whatsapp_number').'?text='.rawurlencode($message);
    }
}
