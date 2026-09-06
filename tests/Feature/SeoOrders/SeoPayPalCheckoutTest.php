<?php

namespace Tests\Feature\SeoOrders;

use App\Enums\PaymentStatus;
use App\Enums\SeoOrderStatus;
use App\Mail\SeoOrderReceived;
use App\Models\Country;
use App\Models\PaymentSetting;
use App\Models\SeoOrder;
use App\Models\SeoService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SeoPayPalCheckoutTest extends TestCase
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

    public function test_creating_a_seo_paypal_order_is_unavailable_when_paypal_is_not_configured(): void
    {
        $service = SeoService::factory()->create();
        $country = Country::factory()->create();
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson(route('seo-paypal.orders.create', $service), [
            'target_url' => 'https://example.com',
            'keyword_1' => 'seo',
            'country_id' => $country->id,
            'quantity' => $service->min_quantity,
            'terms_accepted' => 1,
        ]);

        $response->assertStatus(503);
    }

    public function test_creating_a_seo_paypal_order_stores_a_pending_order_and_returns_the_paypal_order_id(): void
    {
        $this->enablePayPal();
        $service = SeoService::factory()->create(['unit_price' => 0.10, 'min_quantity' => 10, 'max_quantity' => 5000]);
        $country = Country::factory()->create();
        $user = User::factory()->create();

        Http::fake([
            '*/v1/oauth2/token' => Http::response(['access_token' => 'fake-token', 'expires_in' => 3000]),
            '*/v2/checkout/orders' => Http::response(['id' => 'PAYPAL-SEO-ORDER-1', 'status' => 'CREATED']),
        ]);

        $response = $this->actingAs($user)->postJson(route('seo-paypal.orders.create', $service), [
            'target_url' => 'https://example.com',
            'keyword_1' => 'seo',
            'country_id' => $country->id,
            'quantity' => 100,
            'terms_accepted' => 1,
        ]);

        $response->assertOk()->assertJson(['id' => 'PAYPAL-SEO-ORDER-1']);

        $this->assertDatabaseHas('seo_orders', [
            'user_id' => $user->id,
            'paypal_order_id' => 'PAYPAL-SEO-ORDER-1',
            'payment_status' => PaymentStatus::Unpaid->value,
            'order_status' => SeoOrderStatus::PendingPayment->value,
        ]);
    }

    public function test_a_failed_seo_paypal_order_creation_does_not_leave_a_dangling_order(): void
    {
        $this->enablePayPal();
        $service = SeoService::factory()->create();
        $country = Country::factory()->create();
        $user = User::factory()->create();

        Http::fake([
            '*/v1/oauth2/token' => Http::response(['access_token' => 'fake-token', 'expires_in' => 3000]),
            '*/v2/checkout/orders' => Http::response(['error' => 'invalid_client'], 401),
        ]);

        $response = $this->actingAs($user)->postJson(route('seo-paypal.orders.create', $service), [
            'target_url' => 'https://example.com',
            'keyword_1' => 'seo',
            'country_id' => $country->id,
            'quantity' => $service->min_quantity,
            'terms_accepted' => 1,
        ]);

        $response->assertStatus(502);
        $this->assertDatabaseCount('seo_orders', 0);
    }

    public function test_capturing_a_seo_paypal_order_marks_it_paid_and_sends_confirmation_email(): void
    {
        Mail::fake();
        $this->enablePayPal();
        $user = User::factory()->create();
        $order = SeoOrder::factory()->for($user)->create([
            'paypal_order_id' => 'PAYPAL-SEO-ORDER-2',
            'payment_status' => PaymentStatus::Unpaid,
            'order_status' => SeoOrderStatus::PendingPayment,
        ]);

        Http::fake([
            '*/v1/oauth2/token' => Http::response(['access_token' => 'fake-token', 'expires_in' => 3000]),
            '*/v2/checkout/orders/PAYPAL-SEO-ORDER-2/capture' => Http::response(['id' => 'PAYPAL-SEO-ORDER-2', 'status' => 'COMPLETED']),
        ]);

        $response = $this->actingAs($user)->postJson(route('seo-paypal.orders.capture', 'PAYPAL-SEO-ORDER-2'));

        $response->assertOk()->assertJson(['status' => 'COMPLETED']);

        $order->refresh();
        $this->assertSame(PaymentStatus::Paid, $order->payment_status);
        $this->assertSame(SeoOrderStatus::OrderReceived, $order->order_status);
        $this->assertNotNull($order->paid_at);

        Mail::assertQueued(SeoOrderReceived::class);
    }

    public function test_a_customer_cannot_capture_another_customers_seo_paypal_order(): void
    {
        $this->enablePayPal();
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        SeoOrder::factory()->for($owner)->create(['paypal_order_id' => 'PAYPAL-SEO-ORDER-3']);

        $response = $this->actingAs($intruder)->postJson(route('seo-paypal.orders.capture', 'PAYPAL-SEO-ORDER-3'));

        $response->assertStatus(404);
    }
}
