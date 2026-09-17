<?php

namespace Tests\Feature\AiSeoChecker;

use App\Enums\PaymentStatus;
use App\Models\AiSeoCheck;
use App\Models\PaymentSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AiSeoReportPayPalWebhookTest extends TestCase
{
    use RefreshDatabase;

    private function enablePayPalWithWebhook(): void
    {
        PaymentSetting::paypal()->update([
            'enabled' => true,
            'mode' => 'sandbox',
            'client_id' => 'test-client-id',
            'client_secret' => 'test-client-secret',
            'webhook_id' => 'WH-TEST-1',
        ]);

        Http::fake([
            '*/v1/oauth2/token' => Http::response(['access_token' => 'fake-token', 'expires_in' => 3000]),
            '*/v1/notifications/verify-webhook-signature' => Http::response(['verification_status' => 'SUCCESS']),
        ]);
    }

    public function test_an_aiseo_webhook_event_marks_the_check_paid(): void
    {
        $this->enablePayPalWithWebhook();
        $check = AiSeoCheck::factory()->create(['payment_status' => PaymentStatus::Unpaid]);

        $response = $this->postJson('/webhooks/paypal', [
            'event_type' => 'PAYMENT.CAPTURE.COMPLETED',
            'resource' => ['custom_id' => "aiseo:{$check->id}"],
        ]);

        $response->assertOk();
        $this->assertSame(PaymentStatus::Paid, $check->fresh()->payment_status);
        $this->assertNotNull($check->fresh()->paid_at);
    }

    public function test_an_aiseo_webhook_never_touches_a_seo_order_with_the_same_numeric_id(): void
    {
        $this->enablePayPalWithWebhook();

        $check = AiSeoCheck::factory()->create(['payment_status' => PaymentStatus::Unpaid]);
        $this->assertSame(1, $check->id);

        $this->postJson('/webhooks/paypal', [
            'event_type' => 'PAYMENT.CAPTURE.COMPLETED',
            'resource' => ['custom_id' => 'aiseo:1'],
        ])->assertOk();

        $this->assertSame(PaymentStatus::Paid, $check->fresh()->payment_status);
    }
}
