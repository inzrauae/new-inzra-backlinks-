<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Support\SeoData;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::orderBy('sort_order')->get();

        return view('pages.categories', [
            'seo' => SeoData::forCategories($categories),
            'categories' => $categories,
        ]);
    }
}
