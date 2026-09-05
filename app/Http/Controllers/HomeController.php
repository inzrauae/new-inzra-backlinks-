<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Support\SeoData;

class HomeController extends Controller
{
    public function index()
    {
        $featuredSlugs = config('inzra.featured_product_slugs');

        $products = Product::with('category')
            ->whereIn('slug', $featuredSlugs)
            ->get()
            ->sortBy(fn ($product) => array_search($product->slug, $featuredSlugs))
            ->values();

        return view('pages.home', [
            'seo' => SeoData::forHome(),
            'products' => $products,
        ]);
    }
}
