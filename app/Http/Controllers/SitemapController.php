<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\Product;
use App\Models\SeoService;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $xml = Cache::remember('sitemap.xml', now()->addHours(6), function () {
            $urls = [
                ['loc' => url('/'), 'priority' => '1.0', 'changefreq' => 'weekly'],
                ['loc' => route('marketplace'), 'priority' => '0.9', 'changefreq' => 'daily'],
                ['loc' => route('categories'), 'priority' => '0.8', 'changefreq' => 'weekly'],
                ['loc' => route('markets.index'), 'priority' => '0.7', 'changefreq' => 'monthly'],
                ['loc' => route('tools.index'), 'priority' => '0.6', 'changefreq' => 'monthly'],
                ['loc' => route('tools.image-converter'), 'priority' => '0.6', 'changefreq' => 'monthly'],
                ['loc' => route('tools.pdf-editor'), 'priority' => '0.6', 'changefreq' => 'monthly'],
                ['loc' => route('pricing'), 'priority' => '0.8', 'changefreq' => 'monthly'],
                ['loc' => route('blog.index'), 'priority' => '0.7', 'changefreq' => 'weekly'],
                ['loc' => route('contact'), 'priority' => '0.6', 'changefreq' => 'monthly'],
                ['loc' => route('seo-backlink-services.index'), 'priority' => '0.8', 'changefreq' => 'monthly'],
            ];

            foreach (array_keys(config('markets')) as $marketSlug) {
                $urls[] = [
                    'loc' => route('markets.show', $marketSlug),
                    'priority' => '0.6',
                    'changefreq' => 'monthly',
                ];
            }

            foreach (SeoService::where('is_active', true)->get() as $service) {
                $urls[] = [
                    'loc' => route('seo-backlink-services.show', $service),
                    'priority' => '0.7',
                    'changefreq' => 'monthly',
                    'lastmod' => $service->updated_at->toDateString(),
                ];
            }

            foreach (Product::where('is_active', true)->get() as $product) {
                $urls[] = [
                    'loc' => route('products.show', $product),
                    'priority' => '0.7',
                    'changefreq' => 'weekly',
                    'lastmod' => $product->updated_at->toDateString(),
                ];
            }

            foreach (BlogPost::where('published_at', '<=', now())->get() as $post) {
                $urls[] = [
                    'loc' => route('blog.show', $post),
                    'priority' => '0.6',
                    'changefreq' => 'monthly',
                    'lastmod' => $post->updated_at->toDateString(),
                ];
            }

            return view('sitemap', ['urls' => $urls])->render();
        });

        return response($xml, 200)->header('Content-Type', 'application/xml');
    }
}
