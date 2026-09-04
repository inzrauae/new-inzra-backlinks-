<?php

namespace Tests\Feature\Orders;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\PaymentSetting;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PayPalCheckoutTest extends TestCase
{
    use RefreshDatabase;

    private function enablePayPal(): void
    {
        PaymentSetting::paypal()->update([
            'enabled' => true,
            'mode' => 'sandbox',
            'client_id' => 'test-client-id',
            'client_secret' => 'test-client-secret',
        ]);
    }

    public function test_creating_a_paypal_order_is_unavailable_when_paypal_is_not_configured(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $response = $this->actingAs($user)->postJson("/paypal/orders/{$product->slug}");

        $response->assertStatus(503);
    }

    public function test_a_guest_cannot_create_a_paypal_order(): void
    {
        $this->enablePayPal();
        $product = Product::factory()->create();

        $response = $this->postJson("/paypal/orders/{$product->slug}");

        $response->assertStatus(401);
    }

    public function test_creating_a_paypal_order_stores_a_pending_order_and_returns_the_paypal_order_id(): void
    {
        $this->enablePayPal();
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 19.99]);

        Http::fake([
            '*/v1/oauth2/token' => Http::response(['access_token' => 'fake-token', 'expires_in' => 3000]),
            '*/v2/checkout/orders' => Http::response(['id' => 'PAYPAL-ORDER-1', 'status' => 'CREATED']),
        ]);

        $response = $this->actingAs($user)->postJson("/paypal/orders/{$product->slug}", [
            'target_url' => 'https://example.com',
            'anchor_text' => 'buy now',
        ]);

        $response->assertOk()->assertJson(['id' => 'PAYPAL-ORDER-1']);

        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'payment_method' => 'paypal',
            'paypal_order_id' => 'PAYPAL-ORDER-1',
            'status' => OrderStatus::Pending->value,
            'payment_status' => PaymentStatus::Unpaid->value,
        ]);
    }

    public function test_a_failed_paypal_order_creation_does_not_leave_a_dangling_order(): void
    {
        $this->enablePayPal();
        $user = User::factory()->create();
        $product = Product::factory()->create();

        Http::fake([
            '*/v1/oauth2/token' => Http::response(['access_token' => 'fake-token', 'expires_in' => 3000]),
            '*/v2/checkout/orders' => Http::response(['error' => 'invalid_client'], 401),
        ]);

        $response = $this->actingAs($user)->postJson("/paypal/orders/{$product->slug}");

        $response->assertStatus(502);
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_capturing_a_paypal_order_marks_it_paid_and_confirmed(): void
    {
        $this->enablePayPal();
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $order = Order::create([
            'user_id' => $user->id,
            'order_number' => Order::generateOrderNumber(),
            'status' => OrderStatus::Pending,
            'payment_status' => PaymentStatus::Unpaid,
            'payment_method' => 'paypal',
            'paypal_order_id' => 'PAYPAL-ORDER-2',
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

        Http::fake([
            '*/v1/oauth2/token' => Http::response(['access_token' => 'fake-token', 'expires_in' => 3000]),
            '*/v2/checkout/orders/PAYPAL-ORDER-2/capture' => Http::response(['id' => 'PAYPAL-ORDER-2', 'status' => 'COMPLETED']),
        ]);

        $response = $this->actingAs($user)->postJson('/paypal/orders/PAYPAL-ORDER-2/capture');

        $response->assertOk()->assertJson(['status' => 'COMPLETED']);

        $order->refresh();
        $this->assertSame(OrderStatus::Confirmed, $order->status);
        $this->assertSame(PaymentStatus::Paid, $order->payment_status);
        $this->assertNotNull($order->paid_at);
    }

    public function test_a_customer_cannot_capture_another_customers_paypal_order(): void
    {
        $this->enablePayPal();
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $product = Product::factory()->create();

        Order::create([
            'user_id' => $owner->id,
            'order_number' => Order::generateOrderNumber(),
            'status' => OrderStatus::Pending,
            'payment_status' => PaymentStatus::Unpaid,
            'payment_method' => 'paypal',
            'paypal_order_id' => 'PAYPAL-ORDER-3',
            'subtotal' => $product->price,
            'total' => $product->price,
            'customer_name' => $owner->name,
            'customer_email' => $owner->email,
        ]);

        $response = $this->actingAs($intruder)->postJson('/paypal/orders/PAYPAL-ORDER-3/capture');

        $response->assertStatus(404);
    }
}
