<?php

namespace App\Console\Commands;

use DOMDocument;
use DOMXPath;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class ScrapeStaticCatalog extends Command
{
    /**
     * php artisan scrape:catalog {--source=legacy-static-reference}
     */
    protected $signature = 'scrape:catalog {--source=legacy-static-reference}';

    protected $description = 'Scrape the legacy static product/blog HTML pages into JSON seeder fixtures';

    private array $ebayItemNumberBySku = [];

    public function handle(): int
    {
        $source = base_path($this->option('source'));

        if (! is_dir($source)) {
            $this->error("Source directory not found: {$source}");

            return self::FAILURE;
        }

        $this->loadEbayItemNumbers();

        $products = $this->scrapeProducts($source.'/products');
        $this->writeFixture('products.json', $products);
        $this->info(count($products).' products scraped.');

        $blogPosts = $this->scrapeBlogPosts($source.'/blog', $products);
        $this->writeFixture('blog_posts.json', $blogPosts);
        $this->info(count($blogPosts).' blog posts scraped.');

        return self::SUCCESS;
    }

    private function loadEbayItemNumbers(): void
    {
        $csvPath = collect(glob(base_path('eBay-all-active-listings-report-*.csv')))->first();

        if (! $csvPath) {
            return;
        }

        $handle = fopen($csvPath, 'r');
        $header = fgetcsv($handle);

        // Strip a possible UTF-8 BOM from the first header cell.
        if (isset($header[0])) {
            $header[0] = preg_replace('/^\x{FEFF}/u', '', $header[0]);
        }

        while (($row = fgetcsv($handle)) !== false) {
            $row = array_combine($header, $row);
            $itemNumber = trim((string) ($row['Item number'] ?? ''), " \t\n\r\0\x0B\"");

            if ($itemNumber === '') {
                continue;
            }

            $last6 = substr($itemNumber, -6);
            $this->ebayItemNumberBySku[$last6] = $itemNumber;
        }

        fclose($handle);
    }

    private function scrapeProducts(string $dir): array
    {
        $products = [];

        foreach (glob($dir.'/*.html') as $file) {
            $slug = basename($file, '.html');
            $xpath = $this->loadXPath($file);

            $name = $this->text($xpath, '//h1[contains(@class,"pdp__title")]');
            $priceText = $this->text($xpath, '//p[contains(@class,"pdp__price")]');
            $price = (float) preg_replace('/[^0-9.]/', '', $priceText);
            $sku = $this->attr($xpath, '//a[contains(@class,"js-buy-btn")]', 'data-sku');
            $categoryName = $this->text($xpath, '(//span[contains(@class,"listing__cat")])[1]');
            $imagePath = $this->attr($xpath, '//img[contains(@class,"pdp__art-img")]', 'src');
            $metaDescription = $this->attr($xpath, '//meta[@name="description"]', 'content');

            $stockText = $this->text($xpath, '//span[contains(@class,"pdp__stock")]');
            preg_match('/(\d+)/', $stockText, $stockMatch);
            $quantityAvailable = isset($stockMatch[1]) ? (int) $stockMatch[1] : 0;

            $soldText = $this->text($xpath, '//span[contains(@class,"pdp__sold-badge")]');
            preg_match('/(\d+)/', $soldText, $soldMatch);
            $quantitySold = isset($soldMatch[1]) ? (int) $soldMatch[1] : 0;

            $body = $this->innerHtmlOfParagraphs($xpath, '//div[contains(@class,"pdp__body")]');

            preg_match('/(\d{6})$/', (string) $sku, $skuDigitsMatch);
            $skuDigits = $skuDigitsMatch[1] ?? null;

            $products[] = [
                'slug' => $slug,
                'name' => $name,
                'sku' => $sku,
                'ebay_item_number' => $skuDigits ? ($this->ebayItemNumberBySku[$skuDigits] ?? null) : null,
                'category_name' => $categoryName,
                'price' => $price,
                'currency' => 'USD',
                'quantity_available' => $quantityAvailable,
                'quantity_sold' => $quantitySold,
                'image_path' => ltrim(preg_replace('#^(\.\./)+#', '', (string) $imagePath), '/'),
                'meta_description' => $metaDescription,
                'body' => $body,
                'is_active' => true,
            ];
        }

        return $products;
    }

    private function scrapeBlogPosts(string $dir, array $products): array
    {
        $productSlugs = collect($products)->pluck('slug')->flip();
        $posts = [];

        foreach (glob($dir.'/*.html') as $file) {
            $slug = basename($file, '.html');

            if (! $productSlugs->has($slug)) {
                // blog/index.html or anything without a matching product slug
                continue;
            }

            $xpath = $this->loadXPath($file);

            $title = $this->text($xpath, '//h1[contains(@class,"article__title")]');
            $metaDescription = $this->attr($xpath, '//meta[@name="description"]', 'content');
            $coverImage = $this->attr($xpath, '//div[contains(@class,"article__cover")]//img', 'src');
            $body = $this->innerHtmlOfChildren($xpath, '//div[contains(@class,"article__body")]');
            $productHref = $this->attr($xpath, '//div[contains(@class,"article__cta")]//a', 'href');
            $productSlug = $productHref ? basename(parse_url($productHref, PHP_URL_PATH)) : $slug;

            $metaText = $this->text($xpath, '//p[contains(@class,"article__meta")]');
            preg_match('/(\d+)\s*min read/', $metaText, $readingMatch);
            $readingMinutes = isset($readingMatch[1]) ? (int) $readingMatch[1] : 2;

            [$category, $publishedAt] = $this->blogJsonLd($xpath);
            $faqs = $this->blogFaqs($xpath);

            $posts[] = [
                'slug' => $slug,
                'product_slug' => $productSlug,
                'title' => $title,
                'excerpt' => $metaDescription,
                'category' => $category,
                'cover_image_path' => ltrim(preg_replace('#^(\.\./)+#', '', (string) $coverImage), '/'),
                'body' => $body,
                'faqs' => $faqs,
                'published_at' => $publishedAt,
                'reading_minutes' => $readingMinutes,
            ];
        }

        return $posts;
    }

    private function blogJsonLd(DOMXPath $xpath): array
    {
        $category = null;
        $publishedAt = null;

        foreach ($xpath->query('//script[@type="application/ld+json"]') as $node) {
            $data = json_decode($node->textContent, true);

            if (($data['@type'] ?? null) === 'BlogPosting') {
                $category = $data['articleSection'] ?? null;
                $publishedAt = $data['datePublished'] ?? null;
            }
        }

        return [$category, $publishedAt];
    }

    private function blogFaqs(DOMXPath $xpath): array
    {
        foreach ($xpath->query('//script[@type="application/ld+json"]') as $node) {
            $data = json_decode($node->textContent, true);

            if (($data['@type'] ?? null) === 'FAQPage') {
                return collect($data['mainEntity'] ?? [])->map(fn ($qa) => [
                    'question' => $qa['name'] ?? '',
                    'answer' => $qa['acceptedAnswer']['text'] ?? '',
                ])->all();
            }
        }

        return [];
    }

    private function loadXPath(string $file): DOMXPath
    {
        $html = file_get_contents($file);

        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="utf-8" ?>'.$html, LIBXML_NOERROR | LIBXML_NOWARNING);
        libxml_clear_errors();

        return new DOMXPath($dom);
    }

    private function text(DOMXPath $xpath, string $query): ?string
    {
        $node = $xpath->query($query)->item(0);

        return $node ? trim($node->textContent) : null;
    }

    private function attr(DOMXPath $xpath, string $query, string $attribute): ?string
    {
        $node = $xpath->query($query)->item(0);

        return $node ? trim($node->getAttribute($attribute)) : null;
    }

    private function innerHtmlOfParagraphs(DOMXPath $xpath, string $containerQuery): string
    {
        $container = $xpath->query($containerQuery)->item(0);

        if (! $container) {
            return '';
        }

        $html = '';

        foreach ($xpath->query('.//p', $container) as $p) {
            $html .= $container->ownerDocument->saveHTML($p)."\n";
        }

        return trim($html);
    }

    private function innerHtmlOfChildren(DOMXPath $xpath, string $containerQuery): string
    {
        $container = $xpath->query($containerQuery)->item(0);

        if (! $container) {
            return '';
        }

        $html = '';

        foreach ($container->childNodes as $child) {
            if ($child->nodeType === XML_ELEMENT_NODE) {
                $html .= $container->ownerDocument->saveHTML($child)."\n";
            }
        }

        return trim($html);
    }

    private function writeFixture(string $filename, array $data): void
    {
        $dir = database_path('seeders/data');

        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents(
            $dir.'/'.$filename,
            json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
        );
    }
}
