<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Support\SeoData;

class BlogController extends Controller
{
    public function index()
    {
        return view('pages.blog.index', [
            'seo' => SeoData::forBlogIndex(),
            'posts' => BlogPost::orderByDesc('published_at')->paginate(12),
        ]);
    }

    public function show(BlogPost $post)
    {
        $related = BlogPost::where('id', '!=', $post->id)
            ->inRandomOrder()
            ->take(3)
            ->get();

        return view('pages.blog.show', [
            'seo' => SeoData::forBlogPost($post),
            'post' => $post,
            'related' => $related,
        ]);
    }
}
