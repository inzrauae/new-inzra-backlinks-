<?php

namespace Tests\Feature\Authorization;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        $response = $this->get('/admin');

        $response->assertRedirect('/login');
    }

    public function test_a_customer_cannot_access_the_admin_dashboard(): void
    {
        $customer = User::factory()->create(['role' => UserRole::Customer]);

        $response = $this->actingAs($customer)->get('/admin');

        $response->assertForbidden();
    }

    public function test_a_customer_cannot_access_admin_orders(): void
    {
        $customer = User::factory()->create(['role' => UserRole::Customer]);
        $order = $this->makeOrder();

        $response = $this->actingAs($customer)->get("/admin/orders/{$order->id}");

        $response->assertForbidden();
    }

    public function test_an_admin_can_access_the_admin_dashboard(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $response = $this->actingAs($admin)->get('/admin');

        $response->assertOk();
    }

    public function test_an_admin_can_update_an_orders_status_and_payment(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $order = $this->makeOrder();

        $response = $this->actingAs($admin)->patch("/admin/orders/{$order->id}", [
            'status' => OrderStatus::Confirmed->value,
            'payment_status' => PaymentStatus::Paid->value,
            'admin_note' => 'Payment confirmed.',
        ]);

        $response->assertRedirect(route('admin.orders.show', $order));
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => OrderStatus::Confirmed->value,
            'payment_status' => PaymentStatus::Paid->value,
        ]);
    }

    private function makeOrder(): Order
    {
        $customer = User::factory()->create();
        $product = Product::factory()->create();

        $order = Order::create([
            'user_id' => $customer->id,
            'order_number' => Order::generateOrderNumber(),
            'status' => OrderStatus::Pending,
            'payment_status' => PaymentStatus::Unpaid,
            'subtotal' => $product->price,
            'total' => $product->price,
            'customer_name' => $customer->name,
            'customer_email' => $customer->email,
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
