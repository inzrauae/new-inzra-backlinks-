<?php

namespace App\Http\Controllers;

use App\Support\SeoData;

class ToolController extends Controller
{
    public function imageConverter()
    {
        return view('pages.tools.image-converter', [
            'seo' => SeoData::forImageConverter(),
        ]);
    }
}
