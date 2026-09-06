<?php

namespace Tests\Feature\SeoOrders;

use App\Enums\UserRole;
use App\Models\SeoOrder;
use App\Models\SeoPublication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class SeoPublicationImportTest extends TestCase
{
    use RefreshDatabase;

    private function csv(array $rows): UploadedFile
    {
        $lines = ['publisher_name,publisher_url,published_url,target_url,anchor_text,country,publication_date,status'];

        foreach ($rows as $row) {
            $lines[] = implode(',', $row);
        }

        return UploadedFile::fake()->createWithContent('import.csv', implode("\n", $lines));
    }

    public function test_preview_reports_valid_duplicate_and_invalid_row_counts(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $order = SeoOrder::factory()->create();

        SeoPublication::factory()->for($order, 'order')->create(['published_url' => 'https://existing.example.com/a']);

        $file = $this->csv([
            ['Publisher A', 'https://a.example.com', 'https://a.example.com/post-1', 'https://target.example.com', 'anchor one', 'US', '2026-01-01', 'Verified'],
            ['Publisher B', 'https://b.example.com', 'https://existing.example.com/a', 'https://target.example.com', 'anchor two', 'US', '2026-01-02', 'Verified'],
            ['', 'not-a-url', '', '', '', '', 'not-a-date', 'bogus-status'],
        ]);

        $response = $this->actingAs($admin)->post(route('admin.seo-orders.publications.import.preview', $order), [
            'csv_file' => $file,
        ]);

        $response->assertOk();
        $response->assertViewHas('result', function ($result) {
            return $result['valid_count'] === 1
                && $result['duplicate_count'] === 1
                && $result['invalid_count'] === 1;
        });
    }

    public function test_confirming_the_import_creates_only_the_valid_rows_and_updates_progress(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $order = SeoOrder::factory()->create(['quantity' => 5]);

        $file = $this->csv([
            ['Publisher A', 'https://a.example.com', 'https://a.example.com/post-1', 'https://target.example.com', 'anchor one', 'US', '2026-01-01', 'Verified'],
            ['Publisher B', 'https://b.example.com', 'https://b.example.com/post-2', 'https://target.example.com', 'anchor two', 'US', '2026-01-02', 'Verified'],
        ]);

        $preview = $this->actingAs($admin)->post(route('admin.seo-orders.publications.import.preview', $order), [
            'csv_file' => $file,
        ]);

        $token = $preview->viewData('result')['token'];

        $response = $this->actingAs($admin)->post(route('admin.seo-orders.publications.import', $order), [
            'token' => $token,
        ]);

        $response->assertRedirect(route('admin.seo-orders.show', $order));
        $this->assertDatabaseCount('seo_publications', 2);
        $this->assertSame(2, $order->fresh()->completedCount());
    }
}
