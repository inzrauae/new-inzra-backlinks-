<?php

namespace Database\Seeders;

use App\Models\BlogPost;
use App\Models\Product;
use Illuminate\Database\Seeder;

class BlogPostSeeder extends Seeder
{
    public function run(): void
    {
        $rows = json_decode(file_get_contents(database_path('seeders/data/blog_posts.json')), true);

        $productIds = Product::pluck('id', 'slug');

        foreach ($rows as $row) {
            BlogPost::updateOrCreate(
                ['slug' => $row['slug']],
                [
                    'product_id' => $productIds[$row['product_slug']] ?? null,
                    'title' => $row['title'],
                    'excerpt' => $row['excerpt'],
                    'category' => $row['category'],
                    'cover_image_path' => $row['cover_image_path'],
                    'body' => $row['body'],
                    'faqs' => $row['faqs'],
                    'published_at' => $row['published_at'],
                    'reading_minutes' => $row['reading_minutes'],
                ]
            );
        }
    }
}
