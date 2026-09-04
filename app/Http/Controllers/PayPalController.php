<?php

namespace App\Http\Controllers;

use App\Actions\CreatePendingOrder;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Http\Requests\StoreOrderRequest;
use App\Models\Order;
use App\Models\Product;
use App\Services\PayPalClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Throwable;

class PayPalController extends Controller
{
    public function createOrder(StoreOrderRequest $request, Product $product, CreatePendingOrder $createPendingOrder): JsonResponse
    {
        $paypal = PayPalClient::make();

        if (! $paypal->isConfigured()) {
            return response()->json(['error' => 'PayPal is not available right now.'], 503);
        }

        $order = $createPendingOrder->handle(
            user: Auth::user(),
            product: $product,
            paymentMethod: PaymentMethod::PayPal,
            targetUrl: $request->validated('target_url'),
            anchorText: $request->validated('anchor_text'),
        );

        try {
            $paypalOrder = $paypal->createOrder($order);
        } catch (Throwable $e) {
            report($e);

            // Never reached PayPal, so don't leave a dead order in the
            // customer's history.
            $order->items()->delete();
            $order->delete();

            return response()->json(['error' => 'Could not start PayPal checkout. Please try again.'], 502);
        }

        $order->update(['paypal_order_id' => $paypalOrder['id']]);

        return response()->json(['id' => $paypalOrder['id']]);
    }

    public function captureOrder(string $paypalOrderId): JsonResponse
    {
        $order = Order::where('paypal_order_id', $paypalOrderId)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $paypal = PayPalClient::make();

        try {
            $result = $paypal->captureOrder($paypalOrderId);
        } catch (Throwable $e) {
            report($e);

            return response()->json(['error' => 'Could not confirm payment with PayPal. Please try again.'], 502);
        }

        if (($result['status'] ?? null) === 'COMPLETED') {
            $order->update([
                'status' => OrderStatus::Confirmed,
                'payment_status' => PaymentStatus::Paid,
                'paid_at' => now(),
            ]);
        }

        return response()->json([
            'status' => $result['status'] ?? 'UNKNOWN',
            'redirect' => route('orders.show', $order),
        ]);
    }
}
