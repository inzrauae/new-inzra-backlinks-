<?php

namespace Tests\Feature\SeoOrders;

use App\Models\Country;
use App\Models\PaymentSetting;
use App\Models\SeoOrder;
use App\Models\SeoService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SeoOrderTest extends TestCase
{
    use RefreshDatabase;

    private function service(array $overrides = []): SeoService
    {
        return SeoService::factory()->create(array_merge([
            'unit_price' => 0.10,
            'min_quantity' => 10,
            'max_quantity' => 5000,
            'is_active' => true,
        ], $overrides));
    }

    public function test_the_services_index_page_loads(): void
    {
        $this->service(['name' => 'DA 70+ Publication']);

        $response = $this->get('/seo-backlink-services');

        $response->assertOk();
        $response->assertSee('DA 70+ Publication');
    }

    public function test_the_service_order_page_loads_for_guests(): void
    {
        $service = $this->service();

        $response = $this->get(route('seo-backlink-services.show', $service));

        $response->assertOk();
        $response->assertSee('Log in to place this order');
    }

    public function test_a_guest_cannot_create_a_seo_order(): void
    {
        $service = $this->service();
        $country = Country::factory()->create();

        $response = $this->postJson(route('seo-paypal.orders.create', $service), [
            'target_url' => 'https://example.com',
            'keyword_1' => 'best seo',
            'country_id' => $country->id,
            'quantity' => 100,
            'terms_accepted' => 1,
        ]);

        $response->assertStatus(401);
    }

    public function test_quantity_outside_the_services_bounds_is_rejected(): void
    {
        $this->enablePayPal();
        $service = $this->service(['min_quantity' => 10, 'max_quantity' => 100]);
        $country = Country::factory()->create();
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson(route('seo-paypal.orders.create', $service), [
            'target_url' => 'https://example.com',
            'keyword_1' => 'best seo',
            'country_id' => $country->id,
            'quantity' => 5000,
            'terms_accepted' => 1,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('quantity');
    }

    public function test_more_than_5_keywords_are_ignored_beyond_the_5_named_fields(): void
    {
        $this->enablePayPal();
        $service = $this->service();
        $country = Country::factory()->create();
        $user = User::factory()->create();

        Http::fake([
            '*/v1/oauth2/token' => Http::response(['access_token' => 'fake-token', 'expires_in' => 3000]),
            '*/v2/checkout/orders' => Http::response(['id' => 'PAYPAL-SEO-KW', 'status' => 'CREATED']),
        ]);

        $response = $this->actingAs($user)->postJson(route('seo-paypal.orders.create', $service), [
            'target_url' => 'https://example.com',
            'keyword_1' => 'one',
            'keyword_2' => 'two',
            'keyword_3' => 'three',
            'keyword_4' => 'four',
            'keyword_5' => 'five',
            'country_id' => $country->id,
            'quantity' => 10,
            'terms_accepted' => 1,
        ]);

        $response->assertOk();
        $order = SeoOrder::firstOrFail();
        $this->assertSame(5, $order->keywords()->count());
    }

    public function test_keyword_1_is_required(): void
    {
        $this->enablePayPal();
        $service = $this->service();
        $country = Country::factory()->create();
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson(route('seo-paypal.orders.create', $service), [
            'target_url' => 'https://example.com',
            'country_id' => $country->id,
            'quantity' => 10,
            'terms_accepted' => 1,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('keyword_1');
    }

    public function test_a_javascript_url_is_rejected(): void
    {
        $this->enablePayPal();
        $service = $this->service();
        $country = Country::factory()->create();
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson(route('seo-paypal.orders.create', $service), [
            'target_url' => 'javascript:alert(1)',
            'keyword_1' => 'best seo',
            'country_id' => $country->id,
            'quantity' => 10,
            'terms_accepted' => 1,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('target_url');
    }

    public function test_terms_must_be_accepted(): void
    {
        $this->enablePayPal();
        $service = $this->service();
        $country = Country::factory()->create();
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson(route('seo-paypal.orders.create', $service), [
            'target_url' => 'https://example.com',
            'keyword_1' => 'best seo',
            'country_id' => $country->id,
            'quantity' => 10,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('terms_accepted');
    }

    public function test_the_server_calculates_price_from_the_service_ignoring_any_client_supplied_price(): void
    {
        $this->enablePayPal();
        $service = $this->service(['unit_price' => 0.10]);
        $country = Country::factory()->create();
        $user = User::factory()->create();

        Http::fake([
            '*/v1/oauth2/token' => Http::response(['access_token' => 'fake-token', 'expires_in' => 3000]),
            '*/v2/checkout/orders' => Http::response(['id' => 'PAYPAL-SEO-1', 'status' => 'CREATED']),
        ]);

        $this->actingAs($user)->postJson(route('seo-paypal.orders.create', $service), [
            'target_url' => 'https://example.com',
            'keyword_1' => 'best seo',
            'country_id' => $country->id,
            'quantity' => 100,
            'terms_accepted' => 1,
            // A client trying to smuggle a fake price/total — must be ignored entirely.
            'price' => 0.01,
            'total' => 1,
        ]);

        $order = SeoOrder::firstOrFail();
        $this->assertEquals(10.0, (float) $order->total);
        $this->assertEquals(0.10, (float) $order->unit_price);
    }

    public function test_a_customer_can_view_their_own_seo_order(): void
    {
        $user = User::factory()->create();
        $order = SeoOrder::factory()->for($user)->create();

        $response = $this->actingAs($user)->get(route('seo-orders.show', $order));

        $response->assertOk();
        $response->assertSee($order->order_number);
    }

    public function test_a_customer_cannot_view_another_customers_seo_order(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $order = SeoOrder::factory()->for($owner)->create();

        $response = $this->actingAs($intruder)->get(route('seo-orders.show', $order));

        $response->assertForbidden();
    }

    private function enablePayPal(): void
    {
        PaymentSetting::paypal()->update([
            'enabled' => true,
            'mode' => 'sandbox',
            'client_id' => 'test-client-id',
            'client_secret' => 'test-client-secret',
        ]);
    }
}
