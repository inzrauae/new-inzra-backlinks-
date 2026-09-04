<?php

namespace Tests\Feature\Orders;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_guest_is_redirected_to_login_when_buying(): void
    {
        $product = Product::factory()->create();

        $response = $this->get("/buy/{$product->slug}");

        $response->assertRedirect('/login');
    }

    public function test_a_guests_intended_purchase_survives_the_login_redirect(): void
    {
        $product = Product::factory()->create();
        $user = User::factory()->create();

        $this->get("/buy/{$product->slug}?target_url=https%3A%2F%2Fexample.com&anchor_text=hello");

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect();
        $location = $response->headers->get('Location');
        $this->assertStringContainsString("/buy/{$product->slug}?", $location);
        parse_str(parse_url($location, PHP_URL_QUERY), $query);
        $this->assertSame('https://example.com', $query['target_url']);
        $this->assertSame('hello', $query['anchor_text']);
    }

    public function test_a_logged_in_user_can_place_an_order_and_is_redirected_to_whatsapp(): void
    {
        $product = Product::factory()->create();
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get("/buy/{$product->slug}?target_url=https%3A%2F%2Fexample.com&anchor_text=best+seo");

        $response->assertRedirect();
        $this->assertStringStartsWith('https://wa.me/', $response->headers->get('Location'));

        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'status' => OrderStatus::Pending->value,
            'payment_status' => PaymentStatus::Unpaid->value,
        ]);

        $this->assertDatabaseHas('order_items', [
            'product_id' => $product->id,
            'target_url' => 'https://example.com',
            'anchor_text' => 'best seo',
        ]);
    }

    public function test_ordering_a_nonexistent_product_404s(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/buy/this-product-does-not-exist');

        $response->assertNotFound();
    }

    public function test_a_customer_can_view_their_own_order(): void
    {
        $user = User::factory()->create();
        $order = $this->makeOrderFor($user);

        $response = $this->actingAs($user)->get("/orders/{$order->id}");

        $response->assertOk();
        $response->assertSee($order->order_number);
    }

    public function test_a_customer_cannot_view_another_customers_order(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $order = $this->makeOrderFor($owner);

        $response = $this->actingAs($intruder)->get("/orders/{$order->id}");

        $response->assertForbidden();
    }

    public function test_a_customer_cannot_view_order_id_1_by_guessing_even_when_it_belongs_to_someone_else(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $order = $this->makeOrderFor($owner);

        $this->assertSame(1, $order->id);

        $response = $this->actingAs($intruder)->get('/orders/1');

        $response->assertForbidden();
    }

    private function makeOrderFor(User $user): Order
    {
        $product = Product::factory()->create();

        $order = Order::create([
            'user_id' => $user->id,
            'order_number' => Order::generateOrderNumber(),
            'status' => OrderStatus::Pending,
            'payment_status' => PaymentStatus::Unpaid,
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
}
