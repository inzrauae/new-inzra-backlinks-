<?php

namespace App\Http\Controllers;

use App\Actions\CreatePendingOrder;
use App\Actions\CreatePendingOrderFromCart;
use App\Actions\MarkOrderPaid;
use App\Enums\PaymentMethod;
use App\Http\Requests\StoreOrderRequest;
use App\Models\Order;
use App\Models\Product;
use App\Services\PayPalClient;
use App\Support\Cart;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Throwable;

class PayPalController extends Controller
{
    public function createCartOrder(CreatePendingOrderFromCart $createPendingOrderFromCart): JsonResponse
    {
        $paypal = PayPalClient::make();

        if (! $paypal->isConfigured()) {
            return response()->json(['error' => 'PayPal is not available right now.'], 503);
        }

        if (Cart::isEmpty()) {
            return response()->json(['error' => 'Your cart is empty.'], 422);
        }

        $order = $createPendingOrderFromCart->handle(
            user: Auth::user(),
            lines: Cart::lines(),
            paymentMethod: PaymentMethod::PayPal,
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

        Cart::clear();

        return response()->json(['id' => $paypalOrder['id']]);
    }

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
            targetCountry: $request->validated('target_country'),
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

    public function captureOrder(string $paypalOrderId, MarkOrderPaid $markOrderPaid): JsonResponse
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
            $markOrderPaid->handle($order);

            session()->flash('status', 'Thank you for your order! Add each item\'s target URL, anchor text and target country below so we can get started.');
        }

        return response()->json([
            'status' => $result['status'] ?? 'UNKNOWN',
            'redirect' => route('orders.show', $order),
        ]);
    }
}
