<?php

namespace Tests\Feature\SeoOrders;

use App\Enums\PaymentStatus;
use App\Enums\SeoOrderStatus;
use App\Models\Order;
use App\Models\PaymentSetting;
use App\Models\Product;
use App\Models\SeoOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SeoPayPalWebhookTest extends TestCase
{
    use RefreshDatabase;

    private function enablePayPalWithWebhook(): void
    {
        PaymentSetting::paypal()->update([
            'enabled' => true,
            'mode' => 'sandbox',
            'client_id' => 'test-client-id',
            'client_secret' => 'test-client-secret',
            'webhook_id' => 'WH-TEST-1',
        ]);
    }

    private function fakeValidSignature(): void
    {
        Http::fake([
            '*/v1/oauth2/token' => Http::response(['access_token' => 'fake-token', 'expires_in' => 3000]),
            '*/v1/notifications/verify-webhook-signature' => Http::response(['verification_status' => 'SUCCESS']),
        ]);
    }

    public function test_a_seo_order_webhook_event_marks_the_seo_order_paid(): void
    {
        Mail::fake();
        $this->enablePayPalWithWebhook();
        $this->fakeValidSignature();

        $user = User::factory()->create();
        $seoOrder = SeoOrder::factory()->for($user)->create([
            'payment_status' => PaymentStatus::Unpaid,
            'order_status' => SeoOrderStatus::PendingPayment,
        ]);

        $response = $this->postJson('/webhooks/paypal', [
            'event_type' => 'PAYMENT.CAPTURE.COMPLETED',
            'resource' => ['custom_id' => "seo:{$seoOrder->id}"],
        ]);

        $response->assertOk();
        $seoOrder->refresh();
        $this->assertSame(PaymentStatus::Paid, $seoOrder->payment_status);
        $this->assertSame(SeoOrderStatus::OrderReceived, $seoOrder->order_status);
    }

    public function test_a_seo_order_webhook_event_never_touches_a_marketplace_order_with_the_same_numeric_id(): void
    {
        Mail::fake();
        $this->enablePayPalWithWebhook();
        $this->fakeValidSignature();

        $customer = User::factory()->create();
        $product = Product::factory()->create();

        // Both tables auto-increment independently from a fresh test
        // database, so the first row in each naturally shares id=1 — proving
        // the "seo:" prefix (not luck) is what prevents cross-contamination.
        $marketplaceOrder = Order::create([
            'user_id' => $customer->id,
            'order_number' => Order::generateOrderNumber(),
            'status' => 'pending',
            'payment_status' => PaymentStatus::Unpaid,
            'subtotal' => $product->price,
            'total' => $product->price,
            'customer_name' => $customer->name,
            'customer_email' => $customer->email,
        ]);
        $this->assertSame(1, $marketplaceOrder->id);

        $seoOrder = SeoOrder::factory()->for($customer)->create([
            'payment_status' => PaymentStatus::Unpaid,
        ]);
        $this->assertSame(1, $seoOrder->id);

        $this->postJson('/webhooks/paypal', [
            'event_type' => 'PAYMENT.CAPTURE.COMPLETED',
            'resource' => ['custom_id' => 'seo:1'],
        ])->assertOk();

        $this->assertSame(PaymentStatus::Paid, $seoOrder->fresh()->payment_status);
        $this->assertSame(PaymentStatus::Unpaid, $marketplaceOrder->fresh()->payment_status);
    }
}
