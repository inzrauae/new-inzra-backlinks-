<?php

namespace Tests\Feature\SeoOrders;

use App\Actions\GenerateSeoOrderReport;
use App\Enums\PublicationStatus;
use App\Enums\UserRole;
use App\Models\SeoOrder;
use App\Models\SeoPublication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_report_is_not_downloadable_before_it_is_generated(): void
    {
        $user = User::factory()->create();
        $order = SeoOrder::factory()->for($user)->create();

        $response = $this->actingAs($user)->get(route('seo-orders.report.pdf', $order));

        $response->assertStatus(404);
    }

    public function test_generating_a_report_only_includes_verified_publications(): void
    {
        $order = SeoOrder::factory()->create(['quantity' => 5]);
        SeoPublication::factory()->for($order, 'order')->create(['status' => PublicationStatus::Verified]);
        SeoPublication::factory()->for($order, 'order')->create(['status' => PublicationStatus::Rejected]);
        SeoPublication::factory()->for($order, 'order')->create(['status' => PublicationStatus::Pending]);

        $report = (new GenerateSeoOrderReport)->handle($order);

        $this->assertSame(1, $report->publication_count);
        $this->assertSame('ready', $report->status);
    }

    public function test_a_customer_can_download_their_own_ready_report(): void
    {
        $user = User::factory()->create();
        $order = SeoOrder::factory()->for($user)->create(['quantity' => 1]);
        SeoPublication::factory()->for($order, 'order')->create(['status' => PublicationStatus::Verified]);
        (new GenerateSeoOrderReport)->handle($order);

        $response = $this->actingAs($user)->get(route('seo-orders.report.csv', $order));

        $response->assertOk();
    }

    public function test_a_customer_cannot_download_another_customers_report(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $order = SeoOrder::factory()->for($owner)->create(['quantity' => 1]);
        SeoPublication::factory()->for($order, 'order')->create(['status' => PublicationStatus::Verified]);
        (new GenerateSeoOrderReport)->handle($order);

        $response = $this->actingAs($intruder)->get(route('seo-orders.report.csv', $order));

        $response->assertForbidden();
    }

    public function test_an_admin_can_access_any_customers_report_via_the_admin_order_page(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $order = SeoOrder::factory()->create(['quantity' => 1]);
        SeoPublication::factory()->for($order, 'order')->create(['status' => PublicationStatus::Verified]);
        (new GenerateSeoOrderReport)->handle($order);

        $response = $this->actingAs($admin)->get(route('admin.seo-orders.show', $order));

        $response->assertOk();
    }
}
