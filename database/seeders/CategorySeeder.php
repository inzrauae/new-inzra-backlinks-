<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Hand-seeded from categories.html's ItemList JSON-LD + visible .cat-grid cards
     * (icon classes / stat labels aren't in the schema, only in the DOM).
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Guest posts', 'icon' => 'fa-solid fa-feather-pointed', 'description' => 'Original 1,000-word articles published on real editorial sites with in-content dofollow links.', 'stat_label' => '2,140 sites'],
            ['name' => 'PBN backlinks', 'icon' => 'fa-solid fa-server', 'description' => 'Aged private network domains with clean history, unique hosting and no footprints between sites.', 'stat_label' => '960 domains'],
            ['name' => 'Niche edits', 'icon' => 'fa-solid fa-pen-nib', 'description' => 'Your link inserted into an existing aged article that already ranks and already has traffic.', 'stat_label' => '1,480 sites'],
            ['name' => 'Contextual links', 'icon' => 'fa-solid fa-quote-left', 'description' => 'Editorially placed mentions inside body copy, surrounded by topically relevant text.', 'stat_label' => '1,725 sites'],
            ['name' => 'EDU backlinks', 'icon' => 'fa-solid fa-graduation-cap', 'description' => 'University resource pages, scholarship listings and student blogs from verified institutions.', 'stat_label' => '310 sites'],
            ['name' => 'GOV backlinks', 'icon' => 'fa-solid fa-landmark-dome', 'description' => 'Municipal directories, chamber listings and public-sector partner pages with genuine authority.', 'stat_label' => '118 sites'],
            ['name' => 'Local citations', 'icon' => 'fa-solid fa-location-dot', 'description' => 'NAP-consistent listings across maps, directories and aggregators for local search visibility.', 'stat_label' => '640 directories'],
            ['name' => 'Press releases', 'icon' => 'fa-solid fa-bullhorn', 'description' => 'Syndicated announcements picked up by news networks and regional media outlets.', 'stat_label' => '240 outlets'],
        ];

        foreach ($categories as $i => $category) {
            Category::updateOrCreate(
                ['slug' => Str::slug($category['name'])],
                [
                    'name' => $category['name'],
                    'icon' => $category['icon'],
                    'description' => $category['description'],
                    'stat_label' => $category['stat_label'],
                    'sort_order' => $i + 1,
                ]
            );
        }
    }
}
