<?php

namespace Tests\Feature\Orders;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OrderDeliveryTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_admin_can_attach_a_delivery_link_and_file(): void
    {
        Storage::fake('local');

        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $order = $this->makeOrder();
        $file = UploadedFile::fake()->create('report.pdf', 100, 'application/pdf');

        $response = $this->actingAs($admin)->patch("/admin/orders/{$order->id}", [
            'status' => OrderStatus::Completed->value,
            'payment_status' => PaymentStatus::Paid->value,
            'delivery_url' => 'https://example.com/the-live-post',
            'delivery_file' => $file,
        ]);

        $response->assertRedirect(route('admin.orders.show', $order));

        $order->refresh();
        $this->assertSame('https://example.com/the-live-post', $order->delivery_url);
        $this->assertSame('report.pdf', $order->delivery_file_name);
        Storage::disk('local')->assertExists($order->delivery_file_path);
    }

    public function test_a_customer_sees_the_delivery_link_and_file_on_their_order(): void
    {
        Storage::fake('local');

        $user = User::factory()->create();
        $order = $this->makeOrder($user);
        $order->update([
            'delivery_url' => 'https://example.com/the-live-post',
            'delivery_file_path' => 'deliveries/report.pdf',
            'delivery_file_name' => 'report.pdf',
        ]);
        Storage::disk('local')->put('deliveries/report.pdf', 'fake-pdf-contents');

        $response = $this->actingAs($user)->get("/orders/{$order->id}");

        $response->assertOk();
        $response->assertSee('https://example.com/the-live-post', false);
        $response->assertSee('report.pdf');
    }

    public function test_a_customer_can_download_their_own_delivery_file(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('deliveries/report.pdf', 'fake-pdf-contents');

        $user = User::factory()->create();
        $order = $this->makeOrder($user);
        $order->update([
            'delivery_file_path' => 'deliveries/report.pdf',
            'delivery_file_name' => 'report.pdf',
        ]);

        $response = $this->actingAs($user)->get(route('orders.delivery', $order));

        $response->assertOk();
    }

    public function test_a_customer_cannot_download_another_customers_delivery_file(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('deliveries/report.pdf', 'fake-pdf-contents');

        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $order = $this->makeOrder($owner);
        $order->update([
            'delivery_file_path' => 'deliveries/report.pdf',
            'delivery_file_name' => 'report.pdf',
        ]);

        $response = $this->actingAs($intruder)->get(route('orders.delivery', $order));

        $response->assertForbidden();
    }

    public function test_downloading_a_delivery_that_does_not_exist_yet_404s(): void
    {
        $user = User::factory()->create();
        $order = $this->makeOrder($user);

        $response = $this->actingAs($user)->get(route('orders.delivery', $order));

        $response->assertNotFound();
    }

    private function makeOrder(?User $customer = null): Order
    {
        $customer ??= User::factory()->create();
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
