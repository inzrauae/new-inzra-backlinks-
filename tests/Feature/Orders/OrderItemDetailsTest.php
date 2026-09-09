<?php

namespace Tests\Feature\Orders;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderItemDetailsTest extends TestCase
{
    use RefreshDatabase;

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

    public function test_a_guest_is_redirected_to_login_when_updating_order_item_details(): void
    {
        $order = $this->makeOrderFor(User::factory()->create());
        $item = $order->items->first();

        $response = $this->patch("/orders/{$order->id}/items/{$item->id}", ['target_url' => 'https://example.com']);

        $response->assertRedirect('/login');
    }

    public function test_the_owner_can_fill_in_their_orders_item_details(): void
    {
        $user = User::factory()->create();
        $order = $this->makeOrderFor($user);
        $item = $order->items->first();

        $response = $this->actingAs($user)->patch("/orders/{$order->id}/items/{$item->id}", [
            'target_url' => 'https://example.com/page',
            'anchor_text' => 'best seo backlinks',
            'target_country' => 'Sri Lanka',
        ]);

        $response->assertRedirect(route('orders.show', $order));

        $this->assertDatabaseHas('order_items', [
            'id' => $item->id,
            'target_url' => 'https://example.com/page',
            'anchor_text' => 'best seo backlinks',
            'target_country' => 'Sri Lanka',
        ]);
    }

    public function test_a_customer_cannot_update_another_customers_order_item(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $order = $this->makeOrderFor($owner);
        $item = $order->items->first();

        $response = $this->actingAs($intruder)->patch("/orders/{$order->id}/items/{$item->id}", [
            'target_url' => 'https://example.com',
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseHas('order_items', ['id' => $item->id, 'target_url' => null]);
    }

    public function test_an_item_that_does_not_belong_to_the_order_404s(): void
    {
        $user = User::factory()->create();
        $orderA = $this->makeOrderFor($user);
        $orderB = $this->makeOrderFor($user);
        $itemFromOrderB = $orderB->items->first();

        $response = $this->actingAs($user)->patch("/orders/{$orderA->id}/items/{$itemFromOrderB->id}", [
            'target_url' => 'https://example.com',
        ]);

        $response->assertStatus(404);
    }

    public function test_the_admin_order_page_flags_items_missing_details_and_shows_them_once_filled_in(): void
    {
        $user = User::factory()->create();
        $admin = User::factory()->create(['role' => 'admin']);
        $order = $this->makeOrderFor($user);
        $item = $order->items->first();

        $before = $this->actingAs($admin)->get("/admin/orders/{$order->id}");
        $before->assertSee('Order details not provided yet');

        $item->update(['target_url' => 'https://example.com/page', 'anchor_text' => 'best seo']);

        $after = $this->actingAs($admin)->get("/admin/orders/{$order->id}");
        $after->assertDontSee('Order details not provided yet');
        $after->assertSee('https://example.com/page');
        $after->assertSee('best seo');
    }
}
