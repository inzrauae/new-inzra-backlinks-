<?php

namespace App\Http\Controllers;

use App\Enums\PaymentStatus;
use App\Models\AiSeoCheck;
use App\Services\PayPalClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Throwable;

/**
 * Purchase flow for the $30 "how to reach 100%" PDF. Unlike the rest of
 * the site's PayPal flows, this one is intentionally guest-accessible —
 * the checker itself needs no account, so ownership of a check is proven
 * with its random access_token (returned once, at creation time) rather
 * than Auth::id().
 */
class AiSeoReportPayPalController extends Controller
{
    public function createOrder(Request $request, AiSeoCheck $aiSeoCheck): JsonResponse
    {
        $this->authorizeToken($request, $aiSeoCheck);

        if ($aiSeoCheck->payment_status === PaymentStatus::Paid) {
            return response()->json(['error' => 'This report has already been purchased.'], 422);
        }

        $paypal = PayPalClient::make();

        if (! $paypal->isConfigured()) {
            return response()->json(['error' => 'Online payment is not available right now. Please contact support.'], 503);
        }

        try {
            $paypalOrder = $paypal->createOrderForAmount(
                referenceId: 'AISEO-'.$aiSeoCheck->id,
                customId: "aiseo:{$aiSeoCheck->id}",
                currency: $aiSeoCheck->currency,
                amount: (float) $aiSeoCheck->amount,
                description: 'INZRA AI SEO Optimization Report — '.$aiSeoCheck->host,
            );
        } catch (Throwable $e) {
            report($e);

            return response()->json(['error' => 'Could not start PayPal checkout. Please try again.'], 502);
        }

        $aiSeoCheck->update(['paypal_order_id' => $paypalOrder['id']]);

        return response()->json(['id' => $paypalOrder['id']]);
    }

    public function captureOrder(Request $request, AiSeoCheck $aiSeoCheck, string $paypalOrderId): JsonResponse
    {
        $this->authorizeToken($request, $aiSeoCheck);

        abort_unless($aiSeoCheck->paypal_order_id === $paypalOrderId, 404);

        $paypal = PayPalClient::make();

        try {
            $result = $paypal->captureOrder($paypalOrderId);
        } catch (Throwable $e) {
            report($e);

            return response()->json(['error' => 'Could not confirm payment with PayPal. Please try again.'], 502);
        }

        if (($result['status'] ?? null) === 'COMPLETED' && $aiSeoCheck->payment_status !== PaymentStatus::Paid) {
            $aiSeoCheck->update([
                'payment_status' => PaymentStatus::Paid,
                'paid_at' => now(),
                'payer_email' => $result['payer']['email_address'] ?? $aiSeoCheck->payer_email,
            ]);
        }

        return response()->json([
            'status' => $result['status'] ?? 'UNKNOWN',
            'download_url' => $aiSeoCheck->fresh()->payment_status === PaymentStatus::Paid
                ? URL::temporarySignedRoute('ai-seo-checker.report.pdf', now()->addDays(30), ['aiSeoCheck' => $aiSeoCheck->id])
                : null,
        ]);
    }

    private function authorizeToken(Request $request, AiSeoCheck $aiSeoCheck): void
    {
        $token = $request->header('X-Check-Token') ?? $request->input('access_token');

        abort_unless(is_string($token) && hash_equals($aiSeoCheck->access_token, $token), 404);
    }
}
