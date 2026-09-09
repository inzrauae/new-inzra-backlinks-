<?php

namespace Tests\Feature\Orders;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Mail\OrderPlacedAdminNotification;
use App\Mail\OrderReceived;
use App\Models\Order;
use App\Models\PaymentSetting;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class OrderPayPalWebhookTest extends TestCase
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

    private function makeOrder(User $user, Product $product): Order
    {
        $order = Order::create([
            'user_id' => $user->id,
            'order_number' => Order::generateOrderNumber(),
            'status' => OrderStatus::Pending,
            'payment_status' => PaymentStatus::Unpaid,
            'payment_method' => 'paypal',
            'paypal_order_id' => 'PAYPAL-WEBHOOK-1',
            'subtotal' => $product->price,
            'total' => $product->price,
            'customer_name' => $user->name,
            'customer_email' => $user->email,
        ]);
        $order->items()->create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'sku' => $product->sku,
            'price' => $product->price,
            'quantity' => 1,
            'subtotal' => $product->price,
        ]);

        return $order;
    }

    public function test_a_marketplace_order_webhook_event_marks_it_paid_and_emails_customer_and_admin(): void
    {
        Mail::fake();
        $this->enablePayPalWithWebhook();
        $this->fakeValidSignature();

        $user = User::factory()->create();
        $product = Product::factory()->create();
        $order = $this->makeOrder($user, $product);

        $response = $this->postJson('/webhooks/paypal', [
            'event_type' => 'PAYMENT.CAPTURE.COMPLETED',
            'resource' => ['custom_id' => (string) $order->id],
        ]);

        $response->assertOk();
        $order->refresh();
        $this->assertSame(OrderStatus::Confirmed, $order->status);
        $this->assertSame(PaymentStatus::Paid, $order->payment_status);

        Mail::assertQueued(OrderReceived::class, fn ($mail) => $mail->hasTo($user->email));
        Mail::assertQueued(OrderPlacedAdminNotification::class, fn ($mail) => $mail->hasTo(config('inzra.order_notification_email')));
    }

    public function test_the_webhook_does_not_resend_email_for_an_order_the_direct_capture_already_marked_paid(): void
    {
        Mail::fake();
        $this->enablePayPalWithWebhook();
        $this->fakeValidSignature();

        $user = User::factory()->create();
        $product = Product::factory()->create();
        $order = $this->makeOrder($user, $product);
        $order->update(['status' => OrderStatus::Confirmed, 'payment_status' => PaymentStatus::Paid, 'paid_at' => now()]);

        $this->postJson('/webhooks/paypal', [
            'event_type' => 'PAYMENT.CAPTURE.COMPLETED',
            'resource' => ['custom_id' => (string) $order->id],
        ])->assertOk();

        Mail::assertNotQueued(OrderReceived::class);
        Mail::assertNotQueued(OrderPlacedAdminNotification::class);
    }
}
