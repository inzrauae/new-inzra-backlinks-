<?php

namespace App\Actions;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Mail\OrderPlacedAdminNotification;
use App\Mail\OrderReceived;
use App\Models\Order;
use Illuminate\Support\Facades\Mail;

class MarkOrderPaid
{
    /**
     * Called from both the direct PayPal capture (PayPalController) and the
     * webhook backstop (PayPalWebhookController) — the payment_status check
     * keeps whichever one runs second from re-marking the order or sending
     * duplicate emails.
     */
    public function handle(Order $order): void
    {
        if ($order->payment_status === PaymentStatus::Paid) {
            return;
        }

        $order->update([
            'status' => OrderStatus::Confirmed,
            'payment_status' => PaymentStatus::Paid,
            'paid_at' => $order->paid_at ?? now(),
        ]);

        $order->refresh()->load('items');

        Mail::to($order->customer_email)->send(new OrderReceived($order));
        Mail::to(config('inzra.order_notification_email'))->send(new OrderPlacedAdminNotification($order));
    }
}
