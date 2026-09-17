<?php

namespace Tests\Unit;

use App\Services\AiSeoAnalyzer;
use Tests\TestCase;

class AiSeoAnalyzerTest extends TestCase
{
    private function analyzer(): AiSeoAnalyzer
    {
        return new AiSeoAnalyzer;
    }

    public function test_a_well_optimized_page_scores_highly_across_engines(): void
    {
        $html = <<<'HTML'
        <html>
        <head>
          <title>The Complete Guide to Widget Maintenance</title>
          <meta name="description" content="A practical, 700-word guide covering everything you need to know about maintaining industrial widgets safely and efficiently.">
          <link rel="canonical" href="https://example.com/widget-guide">
          <meta name="viewport" content="width=device-width, initial-scale=1">
          <script type="application/ld+json">
          {"@context":"https://schema.org","@type":"Article","headline":"Widget Maintenance","datePublished":"2026-01-01","author":{"@type":"Person","name":"Jane Doe"}}
          </script>
          <script type="application/ld+json">
          {"@context":"https://schema.org","@type":"FAQPage","mainEntity":[{"@type":"Question","name":"How often should I service a widget?","acceptedAnswer":{"@type":"Answer","text":"Every 6 months."}}]}
          </script>
        </head>
        <body>
          <h1>The Complete Guide to Widget Maintenance</h1>
          <p rel="author" class="author">By Jane Doe</p>
          <time datetime="2026-01-01">1 January 2026</time>
          <h2>Why maintenance matters?</h2>
          <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
          <ul><li>Step one</li><li>Step two</li><li>Step three</li></ul>
          <h2>Common problems?</h2>
          <p>Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>
          <img src="widget.jpg" alt="A technician servicing a widget">
          <a href="https://authority-source.example/standards">industry standards</a>
          <a href="https://another-source.example/safety">safety research</a>
        </body>
        </html>
        HTML;

        $result = $this->analyzer()->analyze($html, '', 'https://example.com/widget-guide');

        $this->assertGreaterThanOrEqual(70, $result['overall_score']);
        $this->assertCount(5, $result['engines']);
        foreach ($result['engines'] as $engine) {
            $this->assertTrue($engine['crawler_allowed']);
        }
    }

    public function test_a_thin_page_with_no_signals_scores_low_and_produces_findings(): void
    {
        $html = '<html><head></head><body><p>Hello world.</p></body></html>';

        $result = $this->analyzer()->analyze($html, '', 'http://example.com/thin');

        $this->assertLessThan(40, $result['overall_score']);
        $this->assertNotEmpty($result['findings']);

        $titles = collect($result['findings'])->pluck('title');
        $this->assertTrue($titles->contains('No structured data (JSON-LD) found'));
    }

    public function test_a_robots_txt_blocking_gptbot_zeroes_out_chatgpts_crawler_access(): void
    {
        $html = '<html><head><title>Blocked page for GPTBot testing here</title></head><body><h1>Blocked</h1></body></html>';
        $robotsTxt = "User-agent: GPTBot\nDisallow: /\n";

        $result = $this->analyzer()->analyze($html, $robotsTxt, 'https://example.com/blocked');

        $chatgpt = collect($result['engines'])->firstWhere('key', 'chatgpt');
        $this->assertFalse($chatgpt['crawler_allowed']);

        $findingTitles = collect($result['findings'])->pluck('title');
        $this->assertTrue($findingTitles->contains('ChatGPT (OpenAI) crawler is blocked'));
    }

    public function test_a_noindex_page_is_flagged_as_critical(): void
    {
        $html = '<html><head><meta name="robots" content="noindex, nofollow"></head><body><h1>Hidden</h1></body></html>';

        $result = $this->analyzer()->analyze($html, '', 'https://example.com/hidden');

        $critical = collect($result['findings'])->firstWhere('title', 'Page is set to "noindex"');
        $this->assertNotNull($critical);
        $this->assertSame('critical', $critical['severity']);
    }
}
