<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Support\SeoData;

class HomeController extends Controller
{
    /**
     * The 16 products originally hand-picked for the homepage "Popular
     * backlink placements" grid, in their original display order.
     */
    private const FEATURED_SLUGS = [
        'premium-custom-website-design-development-modern',
        'premium-website-seo-backlink-power-pack',
        '500-web-2-0-backlinks-keyword-optimierte-backlinks',
        'premium-svenska-backlinks-500-hogkvalitativa-lankar',
        '500-sitemap-indexing-seo-backlinks',
        '500-yt-seo-backlniks-video-embedded-seo-backlink',
        'domain-website-submission-to-320-search-engines',
        'rank-10-keywords-on-google-s-first-page-with-inzra',
        '500-backlinks-mexicanos-web-2-0-seo-linkbuilding',
        '500-deutsche-backlinks-backlinks-kaufen-seo-link',
        'boost-your-website-ranking-with-1000-high-da-dofollow',
        '80-german-backlinks-dofollow-by-top-level-domain',
        '1000-mexico-spanish-seo-backlinks',
        'premium-package-100-usa-backlinks-high-domain',
        '100-permanent-uk-backlinks-with-high-pr-sites',
        'da-90-high-authority-backlinks-boost-your-seo-fast',
    ];

    public function index()
    {
        $products = Product::with('category')
            ->whereIn('slug', self::FEATURED_SLUGS)
            ->get()
            ->sortBy(fn ($product) => array_search($product->slug, self::FEATURED_SLUGS))
            ->values();

        return view('pages.home', [
            'seo' => SeoData::forHome(),
            'products' => $products,
        ]);
    }
}
