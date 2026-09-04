<?php

namespace App\Http\Controllers;

use App\Models\PaymentSetting;
use App\Models\Product;
use App\Support\SeoData;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function show(Product $product): View
    {
        $related = Product::where('id', '!=', $product->id)
            ->where('is_active', true)
            ->inRandomOrder()
            ->take(3)
            ->get();

        return view('pages.products.show', [
            'seo' => SeoData::forProduct($product),
            'product' => $product,
            'related' => $related,
            'paypal' => PaymentSetting::paypal(),
        ]);
    }
}
