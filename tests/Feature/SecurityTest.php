<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_no_route_is_exempted_from_csrf_protection(): void
    {
        // Laravel disables CSRF enforcement while running tests (so feature
        // tests don't need to pass a token on every request), which makes a
        // behavioral "POST without a token" test unreliable here. Instead,
        // assert directly that nobody added an exemption to the app's CSRF
        // middleware — its $except list should stay empty.
        $middleware = app(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);

        $except = (function () {
            return $this->except;
        })->call($middleware);

        $this->assertEmpty($except);
    }

    public function test_a_customer_cannot_escalate_their_own_role_via_profile_update(): void
    {
        $user = User::factory()->create(['role' => UserRole::Customer]);

        $this->actingAs($user)->patch('/profile', [
            'name' => $user->name,
            'email' => $user->email,
            'role' => UserRole::Admin->value,
        ]);

        $this->assertSame(UserRole::Customer, $user->fresh()->role);
    }

    public function test_registration_validates_required_fields(): void
    {
        $response = $this->post('/register', []);

        $response->assertSessionHasErrors(['name', 'email', 'phone', 'password']);
    }

    public function test_admin_order_update_rejects_an_invalid_status_value(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
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

        $response = $this->actingAs($admin)->patch("/admin/orders/{$order->id}", [
            'status' => 'not-a-real-status',
            'payment_status' => 'not-a-real-status',
        ]);

        $response->assertSessionHasErrors(['status', 'payment_status']);
    }
}
