<?php

namespace Tests\Feature\Cart;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\PaymentSetting;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CheckoutTest extends TestCase
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

    public function test_checkout_redirects_to_the_cart_when_it_is_empty(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/checkout');

        $response->assertRedirect(route('cart.index'));
    }

    public function test_creating_a_paypal_cart_order_sums_multiple_items_into_one_order(): void
    {
        $this->enablePayPal();
        $user = User::factory()->create();
        $productA = Product::factory()->create(['price' => 10, 'currency' => 'USD']);
        $productB = Product::factory()->create(['price' => 25, 'currency' => 'USD']);

        $this->actingAs($user)->get("/cart/add/{$productA->slug}?quantity=2");
        $this->actingAs($user)->get("/cart/add/{$productB->slug}");

        Http::fake([
            '*/v1/oauth2/token' => Http::response(['access_token' => 'fake-token', 'expires_in' => 3000]),
            '*/v2/checkout/orders' => Http::response(['id' => 'PAYPAL-ORDER-CART-1', 'status' => 'CREATED']),
        ]);

        $response = $this->actingAs($user)->postJson('/paypal/cart/orders');

        $response->assertOk()->assertJson(['id' => 'PAYPAL-ORDER-CART-1']);

        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'payment_method' => 'paypal',
            'paypal_order_id' => 'PAYPAL-ORDER-CART-1',
            'status' => OrderStatus::Pending->value,
            'payment_status' => PaymentStatus::Unpaid->value,
            'total' => '45.00',
        ]);

        $this->assertDatabaseCount('order_items', 2);
        $this->assertDatabaseHas('order_items', ['product_id' => $productA->id, 'quantity' => 2, 'subtotal' => 20]);
        $this->assertDatabaseHas('order_items', ['product_id' => $productB->id, 'quantity' => 1, 'subtotal' => 25]);

        $this->assertEmpty(session('cart', []));
    }

    public function test_creating_a_paypal_cart_order_is_rejected_when_the_cart_is_empty(): void
    {
        $this->enablePayPal();
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/paypal/cart/orders');

        $response->assertStatus(422);
    }

    public function test_checkout_via_whatsapp_creates_an_order_and_clears_the_cart(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 15]);

        $this->actingAs($user)->get("/cart/add/{$product->slug}?target_url=https%3A%2F%2Fexample.com");

        $response = $this->actingAs($user)->post('/checkout/whatsapp');

        $response->assertRedirect();
        $this->assertStringStartsWith('https://wa.me/', $response->headers->get('Location'));

        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'payment_method' => 'whatsapp',
            'status' => OrderStatus::Pending->value,
        ]);
        $this->assertDatabaseHas('order_items', [
            'product_id' => $product->id,
            'target_url' => 'https://example.com',
        ]);

        $this->assertEmpty(session('cart', []));
    }
}
