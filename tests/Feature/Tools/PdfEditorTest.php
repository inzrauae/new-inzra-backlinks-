<?php

namespace Tests\Feature\Tools;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class PdfEditorTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_pdf_editor_page_loads(): void
    {
        $response = $this->get('/pdf-editor');

        $response->assertOk();
        $response->assertViewIs('pages.tools.pdf-editor');
    }

    public function test_it_is_listed_as_the_second_tool_on_the_tools_hub(): void
    {
        $tools = array_keys(config('tools'));

        $this->assertSame('pdf-editor', $tools[1] ?? null);
    }

    public function test_no_upload_endpoint_exists_for_the_pdf_editor(): void
    {
        // The whole point of this tool is that the user's PDF never reaches
        // the server, so there must be no POST/PUT route it (or a future
        // regression) could send file contents to.
        $writeMethods = ['POST', 'PUT', 'PATCH'];

        $suspicious = collect(Route::getRoutes())->first(function ($route) use ($writeMethods) {
            $uri = $route->uri();

            return array_intersect($route->methods(), $writeMethods)
                && (str_contains($uri, 'pdf') || str_contains($uri, 'upload'));
        });

        $this->assertNull($suspicious, 'Found a write route that looks like a PDF upload endpoint.');
    }

    public function test_the_page_advertises_privacy_and_does_not_reference_an_upload_flow(): void
    {
        $response = $this->get('/pdf-editor');

        $response->assertSee('never uploaded', false);
        $response->assertDontSee('pdf-upload', false);
    }

    public function test_seo_metadata_is_present(): void
    {
        $response = $this->get('/pdf-editor');

        $response->assertSee('Free Online PDF Editor', false);

        $jsonLdTypes = collect($response->viewData('seo')->jsonLd)->pluck('@type');

        $this->assertTrue($jsonLdTypes->contains('WebApplication'));
        $this->assertTrue($jsonLdTypes->contains('FAQPage'));
        $this->assertTrue($jsonLdTypes->contains('HowTo'));
        $this->assertTrue($jsonLdTypes->contains('BreadcrumbList'));
    }

    public function test_the_editor_assets_are_published_and_served_locally(): void
    {
        $response = $this->get('/pdf-editor');

        // pdf.js/pdf-lib must be vendored (not a CDN <script src>) because
        // PDF.js needs a same-origin worker script — cross-origin Worker
        // construction is blocked by the browser regardless of CORS headers.
        $response->assertDontSee('cdnjs.cloudflare.com/ajax/libs/pdf', false);
        $response->assertDontSee('cdn.jsdelivr.net/npm/pdf-lib', false);
        $response->assertSee('js/pdf-editor/main.js', false);

        $this->assertFileExists(public_path('js/pdf-editor/vendor/pdf.min.mjs'));
        $this->assertFileExists(public_path('js/pdf-editor/vendor/pdf.worker.min.mjs'));
        $this->assertFileExists(public_path('js/pdf-editor/vendor/pdf-lib.esm.min.js'));
        $this->assertFileExists(public_path('css/pdf-editor.css'));
    }
}
