<?php

namespace Tests\Feature\SeoOrders;

use App\Enums\PaymentStatus;
use App\Enums\PublicationStatus;
use App\Enums\SeoOrderStatus;
use App\Enums\UserRole;
use App\Mail\SeoOrderStatusChanged;
use App\Models\SeoOrder;
use App\Models\SeoPublication;
use App\Models\SeoService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SeoAdminOrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_customer_cannot_access_admin_seo_orders(): void
    {
        $customer = User::factory()->create(['role' => UserRole::Customer]);
        $order = SeoOrder::factory()->create();

        $response = $this->actingAs($customer)->get(route('admin.seo-orders.show', $order));

        $response->assertForbidden();
    }

    public function test_an_admin_can_view_the_seo_orders_list(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        SeoOrder::factory()->create();

        $response = $this->actingAs($admin)->get(route('admin.seo-orders.index'));

        $response->assertOk();
    }

    public function test_an_admin_can_update_order_and_payment_status(): void
    {
        Mail::fake();
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $order = SeoOrder::factory()->create([
            'order_status' => SeoOrderStatus::OrderReceived,
            'payment_status' => PaymentStatus::Paid,
        ]);

        $response = $this->actingAs($admin)->patch(route('admin.seo-orders.update', $order), [
            'order_status' => SeoOrderStatus::InProgress->value,
            'payment_status' => PaymentStatus::Paid->value,
        ]);

        $response->assertRedirect(route('admin.seo-orders.show', $order));
        $this->assertDatabaseHas('seo_orders', [
            'id' => $order->id,
            'order_status' => SeoOrderStatus::InProgress->value,
        ]);
        $this->assertDatabaseHas('seo_order_status_history', [
            'seo_order_id' => $order->id,
            'from_status' => SeoOrderStatus::OrderReceived->value,
            'to_status' => SeoOrderStatus::InProgress->value,
            'changed_by' => $admin->id,
        ]);
        Mail::assertQueued(SeoOrderStatusChanged::class);
    }

    public function test_admin_can_add_a_publication_record_and_only_verified_ones_count_toward_progress(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $order = SeoOrder::factory()->create(['quantity' => 10]);

        $this->actingAs($admin)->post(route('admin.seo-orders.publications.store', $order), [
            'publisher_name' => 'Example Publisher',
            'published_url' => 'https://publisher.example.com/article',
            'status' => PublicationStatus::Submitted->value,
        ]);

        $this->assertSame(0, $order->fresh()->completedCount());

        $publication = SeoPublication::firstOrFail();

        $this->actingAs($admin)->patch(route('admin.seo-orders.publications.update', [$order, $publication]), [
            'publisher_name' => 'Example Publisher',
            'published_url' => 'https://publisher.example.com/article',
            'status' => PublicationStatus::Verified->value,
        ]);

        $order->refresh();
        $this->assertSame(1, $order->completedCount());
        $this->assertSame(10, $order->quantity);
        $this->assertSame(SeoOrderStatus::PartiallyCompleted, $order->order_status);
    }

    public function test_order_automatically_completes_and_generates_a_report_once_quantity_is_verified(): void
    {
        Mail::fake();
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $order = SeoOrder::factory()->create(['quantity' => 2]);

        SeoPublication::factory()->for($order, 'order')->create(['status' => PublicationStatus::Verified]);
        $publication2 = SeoPublication::factory()->for($order, 'order')->create(['status' => PublicationStatus::Pending]);

        $this->actingAs($admin)->patch(route('admin.seo-orders.publications.update', [$order, $publication2]), [
            'publisher_name' => $publication2->publisher_name,
            'published_url' => $publication2->published_url,
            'status' => PublicationStatus::Verified->value,
        ]);

        $order->refresh();
        $this->assertSame(SeoOrderStatus::Completed, $order->order_status);
        $this->assertNotNull($order->completed_at);
        $this->assertTrue($order->report()->exists());
        $this->assertSame('ready', $order->report->status);
        Mail::assertQueued(SeoOrderStatusChanged::class);
    }

    public function test_internal_notes_are_stored_and_distinguished_from_customer_notes(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $order = SeoOrder::factory()->create();

        $this->actingAs($admin)->post(route('admin.seo-orders.notes.store', $order), [
            'type' => 'internal',
            'body' => 'Do not tell the customer this yet.',
        ]);

        $this->assertDatabaseHas('seo_order_notes', [
            'seo_order_id' => $order->id,
            'type' => 'internal',
            'body' => 'Do not tell the customer this yet.',
        ]);
    }

    public function test_admin_can_manage_seo_services_and_historical_orders_keep_their_original_price(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $service = SeoService::factory()->create(['unit_price' => 0.10]);
        $order = SeoOrder::factory()->create(['seo_service_id' => $service->id, 'unit_price' => 0.10]);

        $response = $this->actingAs($admin)->patch(route('admin.seo-services.update', $service), [
            'name' => $service->name,
            'slug' => $service->slug,
            'unit_price' => 0.12,
            'min_quantity' => $service->min_quantity,
            'max_quantity' => $service->max_quantity,
            'is_active' => 1,
        ]);

        $response->assertRedirect(route('admin.seo-services.index'));
        $this->assertEquals(0.12, (float) $service->fresh()->unit_price);
        $this->assertEquals(0.10, (float) $order->fresh()->unit_price);
    }
}
