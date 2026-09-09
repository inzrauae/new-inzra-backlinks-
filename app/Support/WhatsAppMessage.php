<?php

namespace App\Support;

use App\Models\Order;

final class WhatsAppMessage
{
    public static function forOrder(Order $order): string
    {
        $lines = ["Hi INZRA! I'd like to order:"];

        foreach ($order->items as $item) {
            $lines[] = "- {$item->product_name} x{$item->quantity} (\${$item->price}) — SKU {$item->sku}";

            if ($item->target_url) {
                $lines[] = '  Target URL: '.$item->target_url;
            }
            if ($item->anchor_text) {
                $lines[] = '  Anchor text preference: '.$item->anchor_text;
            }
            if ($item->target_country) {
                $lines[] = '  Target country: '.$item->target_country;
            }
        }

        $lines[] = 'Order ref: '.$order->order_number;

        return implode("\n", $lines);
    }

    public static function url(string $message): string
    {
        return 'https://wa.me/'.config('inzra.whatsapp_number').'?text='.rawurlencode($message);
    }
}
