<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Support\SeoData;

class ToolController extends Controller
{
    public function index()
    {
        return view('pages.tools.index', [
            'seo' => SeoData::forToolsIndex(),
            'tools' => config('tools'),
            'products' => $this->crossPromoProducts(),
        ]);
    }

    public function imageConverter()
    {
        return view('pages.tools.image-converter', [
            'seo' => SeoData::forImageConverter(),
            'products' => $this->crossPromoProducts(),
        ]);
    }

    public function pdfEditor()
    {
        return view('pages.tools.pdf-editor', [
            'seo' => SeoData::forPdfEditor(),
            'products' => $this->crossPromoProducts(),
        ]);
    }

    private function crossPromoProducts()
    {
        $slugs = array_slice(config('inzra.featured_product_slugs'), 0, 4);

        return Product::with('category')
            ->whereIn('slug', $slugs)
            ->where('is_active', true)
            ->get()
            ->sortBy(fn ($product) => array_search($product->slug, $slugs))
            ->values();
    }
}
