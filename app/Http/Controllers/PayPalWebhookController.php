<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Services\PayPalClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PayPalWebhookController extends Controller
{
    /**
     * PayPal webhook — extra assurance on top of the direct capture call in
     * PayPalController, in case the buyer's browser never confirms back to
     * us (closed tab, network drop) after PayPal itself processed payment.
     */
    public function handle(Request $request): JsonResponse
    {
        $paypal = PayPalClient::make();

        $headers = [
            'paypal-auth-algo' => $request->header('paypal-auth-algo'),
            'paypal-cert-url' => $request->header('paypal-cert-url'),
            'paypal-transmission-id' => $request->header('paypal-transmission-id'),
            'paypal-transmission-sig' => $request->header('paypal-transmission-sig'),
            'paypal-transmission-time' => $request->header('paypal-transmission-time'),
        ];

        if (! $paypal->verifyWebhookSignature($headers, $request->all())) {
            Log::warning('Rejected PayPal webhook with invalid signature.');

            return response()->json(['error' => 'Invalid signature'], 400);
        }

        $event = $request->input('event_type');
        $resource = $request->input('resource', []);

        if (in_array($event, ['PAYMENT.CAPTURE.COMPLETED', 'PAYMENT.CAPTURE.DENIED', 'PAYMENT.CAPTURE.REFUNDED'], true)) {
            $order = $this->resolveOrder($resource);

            if ($order) {
                $this->applyCaptureEvent($order, $event);
            }
        }

        return response()->json(['status' => 'ok']);
    }

    private function resolveOrder(array $resource): ?Order
    {
        $orderId = $resource['custom_id'] ?? null;
        $paypalOrderId = $resource['supplementary_data']['related_ids']['order_id'] ?? null;

        return Order::query()
            ->when($orderId, fn ($q) => $q->orWhere('id', $orderId))
            ->when($paypalOrderId, fn ($q) => $q->orWhere('paypal_order_id', $paypalOrderId))
            ->first();
    }

    private function applyCaptureEvent(Order $order, string $event): void
    {
        match ($event) {
            'PAYMENT.CAPTURE.COMPLETED' => $order->update([
                'status' => OrderStatus::Confirmed,
                'payment_status' => PaymentStatus::Paid,
                'paid_at' => $order->paid_at ?? now(),
            ]),
            'PAYMENT.CAPTURE.DENIED' => $order->update([
                'payment_status' => PaymentStatus::Unpaid,
            ]),
            'PAYMENT.CAPTURE.REFUNDED' => $order->update([
                'payment_status' => PaymentStatus::Refunded,
            ]),
            default => null,
        };
    }
}
