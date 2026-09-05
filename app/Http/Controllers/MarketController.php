<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Support\SeoData;

class MarketController extends Controller
{
    public function index()
    {
        return view('pages.markets.index', [
            'seo' => SeoData::forMarketsIndex(),
            'markets' => config('markets'),
        ]);
    }

    public function show(string $market)
    {
        $data = config("markets.{$market}");

        abort_unless($data, 404);

        $relatedSlugs = $data['related_product_slugs'] ?? [];

        $allProducts = Product::with('category')
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->sortByDesc(fn ($product) => in_array($product->slug, $relatedSlugs, true))
            ->values();

        return view('pages.markets.show', [
            'seo' => SeoData::forMarket($market, $data),
            'slug' => $market,
            'market' => $data,
            'allProducts' => $allProducts,
        ]);
    }
}
