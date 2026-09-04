<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Support\SeoData;

class MarketplaceController extends Controller
{
    public function index()
    {
        $products = Product::with('category')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('pages.marketplace', [
            'seo' => SeoData::forMarketplace($products),
            'products' => $products,
            'categories' => ProductCategory::orderBy('name')->get(),
        ]);
    }
}
