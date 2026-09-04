<?php

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Models\PaymentSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_customer_cannot_view_payment_settings(): void
    {
        $customer = User::factory()->create(['role' => UserRole::Customer]);

        $response = $this->actingAs($customer)->get('/admin/settings/payment');

        $response->assertForbidden();
    }

    public function test_an_admin_can_view_payment_settings(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $response = $this->actingAs($admin)->get('/admin/settings/payment');

        $response->assertOk();
    }

    public function test_an_admin_can_enable_paypal_and_save_credentials(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $response = $this->actingAs($admin)->patch('/admin/settings/payment', [
            'enabled' => '1',
            'mode' => 'sandbox',
            'client_id' => 'test-client-id',
            'client_secret' => 'test-client-secret',
            'webhook_id' => 'WH-123',
        ]);

        $response->assertRedirect(route('admin.settings.payment.edit'));

        $paypal = PaymentSetting::paypal();
        $this->assertTrue($paypal->enabled);
        $this->assertSame('sandbox', $paypal->mode);
        $this->assertSame('test-client-id', $paypal->client_id);
        $this->assertSame('test-client-secret', $paypal->client_secret);
        $this->assertSame('WH-123', $paypal->webhook_id);
    }

    public function test_leaving_the_secret_blank_on_update_keeps_the_existing_one(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        PaymentSetting::paypal()->update(['client_secret' => 'original-secret']);

        $this->actingAs($admin)->patch('/admin/settings/payment', [
            'enabled' => '1',
            'mode' => 'live',
            'client_id' => 'new-client-id',
            'client_secret' => '',
        ]);

        $this->assertSame('original-secret', PaymentSetting::paypal()->client_secret);
        $this->assertSame('new-client-id', PaymentSetting::paypal()->client_id);
    }

    public function test_mode_must_be_sandbox_or_live(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $response = $this->actingAs($admin)->patch('/admin/settings/payment', [
            'mode' => 'not-a-real-mode',
        ]);

        $response->assertSessionHasErrors('mode');
    }
}
