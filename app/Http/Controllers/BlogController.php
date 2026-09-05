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
            'posts' => BlogPost::where('published_at', '<=', now())
                ->orderByDesc('published_at')
                ->paginate(12),
        ]);
    }

    public function show(BlogPost $post)
    {
        abort_unless($post->published_at && $post->published_at->lte(now()), 404);

        $related = BlogPost::where('id', '!=', $post->id)
            ->where('published_at', '<=', now())
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
