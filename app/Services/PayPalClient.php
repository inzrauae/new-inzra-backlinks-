<?php

namespace App\Services;

use App\Models\Order;
use App\Models\PaymentSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class PayPalClient
{
    public function __construct(private readonly PaymentSetting $settings) {}

    public static function make(): self
    {
        return new self(PaymentSetting::paypal());
    }

    public function isConfigured(): bool
    {
        return $this->settings->enabled
            && filled($this->settings->client_id)
            && filled($this->settings->client_secret);
    }

    public function baseUrl(): string
    {
        return $this->settings->isLive()
            ? 'https://api-m.paypal.com'
            : 'https://api-m.sandbox.paypal.com';
    }

    public function clientId(): ?string
    {
        return $this->settings->client_id;
    }

    /**
     * OAuth2 client-credentials access token, cached for its lifetime.
     */
    public function accessToken(): string
    {
        $cacheKey = 'paypal.access_token.'.$this->settings->mode;

        return Cache::remember($cacheKey, now()->addMinutes(50), function () {
            $response = Http::asForm()
                ->withBasicAuth($this->settings->client_id, $this->settings->client_secret)
                ->post("{$this->baseUrl()}/v1/oauth2/token", [
                    'grant_type' => 'client_credentials',
                ]);

            if ($response->failed()) {
                throw new RuntimeException('Unable to authenticate with PayPal: '.$response->body());
            }

            return $response->json('access_token');
        });
    }

    /**
     * Create a PayPal order for the given Order's total. Returns the decoded response.
     */
    public function createOrder(Order $order): array
    {
        $response = Http::withToken($this->accessToken())
            ->post("{$this->baseUrl()}/v2/checkout/orders", [
                'intent' => 'CAPTURE',
                'purchase_units' => [[
                    'reference_id' => $order->order_number,
                    'custom_id' => (string) $order->id,
                    'description' => $order->items->first()?->product_name,
                    'amount' => [
                        'currency_code' => $order->currency,
                        'value' => number_format((float) $order->total, 2, '.', ''),
                    ],
                ]],
                'application_context' => [
                    'shipping_preference' => 'NO_SHIPPING',
                    'user_action' => 'PAY_NOW',
                ],
            ]);

        if ($response->failed()) {
            throw new RuntimeException('PayPal order creation failed: '.$response->body());
        }

        return $response->json();
    }

    /**
     * Capture an approved PayPal order. Returns the decoded response.
     */
    public function captureOrder(string $paypalOrderId): array
    {
        $response = Http::withToken($this->accessToken())
            ->post("{$this->baseUrl()}/v2/checkout/orders/{$paypalOrderId}/capture");

        if ($response->failed()) {
            throw new RuntimeException('PayPal capture failed: '.$response->body());
        }

        return $response->json();
    }

    /**
     * Verify an incoming webhook's signature against PayPal.
     */
    public function verifyWebhookSignature(array $headers, array $body): bool
    {
        if (blank($this->settings->webhook_id)) {
            return false;
        }

        $response = Http::withToken($this->accessToken())
            ->post("{$this->baseUrl()}/v1/notifications/verify-webhook-signature", [
                'auth_algo' => $headers['paypal-auth-algo'] ?? '',
                'cert_url' => $headers['paypal-cert-url'] ?? '',
                'transmission_id' => $headers['paypal-transmission-id'] ?? '',
                'transmission_sig' => $headers['paypal-transmission-sig'] ?? '',
                'transmission_time' => $headers['paypal-transmission-time'] ?? '',
                'webhook_id' => $this->settings->webhook_id,
                'webhook_event' => $body,
            ]);

        return $response->successful() && $response->json('verification_status') === 'SUCCESS';
    }
}
