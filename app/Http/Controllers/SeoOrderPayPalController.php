<?php

namespace App\Http\Controllers;

use App\Actions\CreatePendingSeoOrder;
use App\Actions\RecordSeoOrderStatusChange;
use App\Enums\PaymentStatus;
use App\Enums\SeoOrderStatus;
use App\Http\Requests\StoreSeoOrderRequest;
use App\Mail\SeoOrderReceived;
use App\Models\SeoOrder;
use App\Models\SeoService;
use App\Services\PayPalClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SeoOrderPayPalController extends Controller
{
    public function createOrder(StoreSeoOrderRequest $request, SeoService $service, CreatePendingSeoOrder $createPendingSeoOrder): JsonResponse
    {
        $paypal = PayPalClient::make();

        if (! $paypal->isConfigured()) {
            return response()->json(['error' => 'Online payment is not available right now. Please contact support.'], 503);
        }

        $order = $createPendingSeoOrder->handle(
            user: Auth::user(),
            service: $service,
            targetUrl: $request->validated('target_url'),
            countryId: (int) $request->validated('country_id'),
            keywords: $request->keywords(),
            article: $request->validated('article'),
            instructions: $request->validated('instructions'),
            quantity: (int) $request->validated('quantity'),
        );

        try {
            $paypalOrder = $paypal->createOrderForAmount(
                referenceId: $order->order_number,
                // Prefixed so it can never collide with a marketplace Order's
                // integer id in PayPalWebhookController::resolveOrder(),
                // which looks up the unrelated `orders` table by custom_id.
                customId: "seo:{$order->id}",
                currency: $order->currency,
                amount: (float) $order->total,
                description: "{$order->service_name} x{$order->quantity} — INZRA",
            );
        } catch (Throwable $e) {
            report($e);

            $order->keywords()->delete();
            $order->statusHistory()->delete();
            $order->delete();

            return response()->json(['error' => 'Could not start PayPal checkout. Please try again.'], 502);
        }

        $order->update(['paypal_order_id' => $paypalOrder['id']]);

        return response()->json(['id' => $paypalOrder['id']]);
    }

    public function captureOrder(string $paypalOrderId): JsonResponse
    {
        $order = SeoOrder::where('paypal_order_id', $paypalOrderId)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $paypal = PayPalClient::make();

        try {
            $result = $paypal->captureOrder($paypalOrderId);
        } catch (Throwable $e) {
            report($e);

            return response()->json(['error' => 'Could not confirm payment with PayPal. Please try again.'], 502);
        }

        if (($result['status'] ?? null) === 'COMPLETED' && $order->payment_status !== PaymentStatus::Paid) {
            $order->update([
                'payment_status' => PaymentStatus::Paid,
                'paid_at' => now(),
            ]);

            (new RecordSeoOrderStatusChange)->handle($order, SeoOrderStatus::Paid);
            (new RecordSeoOrderStatusChange)->handle($order->fresh(), SeoOrderStatus::OrderReceived);

            Mail::to($order->user->email)->send(new SeoOrderReceived($order->fresh()));
        }

        return response()->json([
            'status' => $result['status'] ?? 'UNKNOWN',
            'redirect' => route('seo-orders.show', $order),
        ]);
    }
}
