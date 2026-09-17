<?php

namespace Tests\Feature\AiSeoChecker;

use App\Enums\PaymentStatus;
use App\Models\AiSeoCheck;
use App\Models\PaymentSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class AiSeoReportPayPalTest extends TestCase
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

    public function test_creating_an_order_is_unavailable_when_paypal_is_not_configured(): void
    {
        $check = AiSeoCheck::factory()->create();

        $response = $this->postJson(
            route('ai-seo-checker.paypal.orders.create', $check),
            ['access_token' => $check->access_token]
        );

        $response->assertStatus(503);
    }

    public function test_creating_an_order_requires_the_correct_access_token(): void
    {
        $this->enablePayPal();
        $check = AiSeoCheck::factory()->create();

        $response = $this->postJson(
            route('ai-seo-checker.paypal.orders.create', $check),
            ['access_token' => 'wrong-token']
        );

        $response->assertStatus(404);
    }

    public function test_creating_an_order_stores_the_paypal_order_id_without_requiring_login(): void
    {
        $this->enablePayPal();
        $check = AiSeoCheck::factory()->create();

        Http::fake([
            '*/v1/oauth2/token' => Http::response(['access_token' => 'fake-token', 'expires_in' => 3000]),
            '*/v2/checkout/orders' => Http::response(['id' => 'PAYPAL-AISEO-1', 'status' => 'CREATED']),
        ]);

        $response = $this->postJson(
            route('ai-seo-checker.paypal.orders.create', $check),
            ['access_token' => $check->access_token]
        );

        $response->assertOk()->assertJson(['id' => 'PAYPAL-AISEO-1']);
        $this->assertDatabaseHas('ai_seo_checks', [
            'id' => $check->id,
            'paypal_order_id' => 'PAYPAL-AISEO-1',
            'payment_status' => PaymentStatus::Unpaid->value,
        ]);
    }

    public function test_capturing_an_order_marks_the_check_paid_and_returns_a_download_link(): void
    {
        $this->enablePayPal();
        $check = AiSeoCheck::factory()->create(['paypal_order_id' => 'PAYPAL-AISEO-2']);

        Http::fake([
            '*/v1/oauth2/token' => Http::response(['access_token' => 'fake-token', 'expires_in' => 3000]),
            '*/v2/checkout/orders/PAYPAL-AISEO-2/capture' => Http::response([
                'id' => 'PAYPAL-AISEO-2',
                'status' => 'COMPLETED',
                'payer' => ['email_address' => 'buyer@example.com'],
            ]),
        ]);

        $response = $this->postJson(
            route('ai-seo-checker.paypal.orders.capture', ['aiSeoCheck' => $check, 'paypalOrderId' => 'PAYPAL-AISEO-2']),
            ['access_token' => $check->access_token]
        );

        $response->assertOk()->assertJson(['status' => 'COMPLETED']);
        $this->assertNotNull($response->json('download_url'));

        $check->refresh();
        $this->assertSame(PaymentStatus::Paid, $check->payment_status);
        $this->assertSame('buyer@example.com', $check->payer_email);
        $this->assertNotNull($check->paid_at);
    }

    public function test_the_pdf_report_is_only_downloadable_via_a_valid_signed_link_after_payment(): void
    {
        $unpaid = AiSeoCheck::factory()->create();
        $unsignedUrl = route('ai-seo-checker.report.pdf', $unpaid);
        $this->get($unsignedUrl)->assertStatus(403);

        $signedButUnpaidUrl = URL::temporarySignedRoute('ai-seo-checker.report.pdf', now()->addDay(), ['aiSeoCheck' => $unpaid->id]);
        $this->get($signedButUnpaidUrl)->assertStatus(403);

        $paid = AiSeoCheck::factory()->create(['payment_status' => PaymentStatus::Paid, 'paid_at' => now()]);
        $signedUrl = URL::temporarySignedRoute('ai-seo-checker.report.pdf', now()->addDay(), ['aiSeoCheck' => $paid->id]);

        $response = $this->get($signedUrl);
        $response->assertOk();
        $this->assertSame('application/pdf', $response->headers->get('Content-Type'));
    }
}
