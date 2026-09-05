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

        $relatedProducts = Product::with('category')
            ->whereIn('slug', $data['related_product_slugs'] ?? [])
            ->where('is_active', true)
            ->get();

        $allProducts = Product::with('category')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('pages.markets.show', [
            'seo' => SeoData::forMarket($market, $data),
            'slug' => $market,
            'market' => $data,
            'relatedProducts' => $relatedProducts,
            'allProducts' => $allProducts,
        ]);
    }
}
