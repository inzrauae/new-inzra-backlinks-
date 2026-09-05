<?php

namespace App\Http\Controllers;

use App\Support\SeoData;

class ToolController extends Controller
{
    public function index()
    {
        return view('pages.tools.index', [
            'seo' => SeoData::forToolsIndex(),
            'tools' => config('tools'),
        ]);
    }

    public function imageConverter()
    {
        return view('pages.tools.image-converter', [
            'seo' => SeoData::forImageConverter(),
        ]);
    }
}
