<?php

namespace Tests\Feature\AiSeoChecker;

use App\Services\SafeUrlFetcher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AiSeoCheckTest extends TestCase
{
    use RefreshDatabase;

    private function fakePublicResolver(): void
    {
        $this->app->bind(SafeUrlFetcher::class, fn () => new SafeUrlFetcher(fn () => ['93.184.216.34']));
    }

    public function test_the_landing_page_loads(): void
    {
        $this->get(route('ai-seo-checker.index'))->assertOk();
    }

    public function test_checking_a_url_scores_it_and_stores_a_record(): void
    {
        $this->fakePublicResolver();

        Http::fake([
            'https://example.com/page' => Http::response('<html><head><title>A Decent Title For This Page</title></head><body><h1>Hello</h1><p>Some content here.</p></body></html>', 200),
            'https://example.com/robots.txt' => Http::response("User-agent: *\nAllow: /\n", 200),
        ]);

        $response = $this->postJson(route('ai-seo-checker.check'), ['url' => 'https://example.com/page']);

        $response->assertOk()->assertJsonStructure([
            'check_id', 'access_token', 'url', 'overall_score', 'engines', 'findings', 'issue_count', 'price', 'currency',
        ]);

        $this->assertCount(5, $response->json('engines'));
        $this->assertDatabaseHas('ai_seo_checks', [
            'url' => 'https://example.com/page',
            'host' => 'example.com',
        ]);
    }

    public function test_only_the_first_two_findings_are_unlocked_for_free(): void
    {
        $this->fakePublicResolver();

        Http::fake([
            'https://example.com/thin' => Http::response('<html><head></head><body><p>Hi</p></body></html>', 200),
            'https://example.com/robots.txt' => Http::response('', 404),
        ]);

        $response = $this->postJson(route('ai-seo-checker.check'), ['url' => 'https://example.com/thin']);

        $findings = $response->json('findings');
        $this->assertGreaterThan(2, count($findings));

        foreach ($findings as $i => $finding) {
            if ($i < 2) {
                $this->assertFalse($finding['locked']);
                $this->assertNotNull($finding['detail']);
            } else {
                $this->assertTrue($finding['locked']);
                $this->assertNull($finding['detail']);
                $this->assertNull($finding['fix']);
            }
        }
    }

    public function test_it_rejects_a_url_that_resolves_to_a_private_network(): void
    {
        $this->app->bind(SafeUrlFetcher::class, fn () => new SafeUrlFetcher(fn () => ['127.0.0.1']));

        $response = $this->postJson(route('ai-seo-checker.check'), ['url' => 'https://internal.example.com']);

        $response->assertStatus(422);
        $this->assertDatabaseCount('ai_seo_checks', 0);
    }

    public function test_it_rejects_a_non_http_url(): void
    {
        $response = $this->postJson(route('ai-seo-checker.check'), ['url' => 'ftp://example.com']);

        $response->assertStatus(422);
    }

    public function test_url_is_required(): void
    {
        $response = $this->postJson(route('ai-seo-checker.check'), ['url' => '']);

        $response->assertStatus(422);
    }

    public function test_a_network_failure_fetching_the_target_returns_a_friendly_error_instead_of_a_500(): void
    {
        $this->fakePublicResolver();

        Http::fake(function () {
            throw new ConnectionException('Connection timed out');
        });

        $response = $this->postJson(route('ai-seo-checker.check'), ['url' => 'https://example.com/unreachable']);

        $response->assertStatus(422)->assertJsonStructure(['error']);
        $this->assertDatabaseCount('ai_seo_checks', 0);
    }
}
