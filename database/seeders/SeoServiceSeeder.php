<?php

namespace Database\Seeders;

use App\Models\SeoService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SeoServiceSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $services = [
            [
                'name' => 'Wiki / Editorial Publication',
                'slug' => 'wiki-editorial-publication',
                'description' => 'Manually placed editorial and wiki-style publications from our publisher network.',
                'unit_price' => 0.05,
                'min_quantity' => 10,
                'max_quantity' => 5000,
                'sort_order' => 1,
            ],
            [
                'name' => 'Web 2.0 / Web Publication',
                'slug' => 'web-2-0-web-publication',
                'description' => 'Manually created Web 2.0 property publications pointing to your target URL.',
                'unit_price' => 0.01,
                'min_quantity' => 10,
                'max_quantity' => 5000,
                'sort_order' => 2,
            ],
            [
                'name' => 'DA 70+ Publication',
                'slug' => 'da-70-publication',
                'description' => 'Manually placed publications on sites with Domain Authority 70 or higher.',
                'unit_price' => 0.10,
                'min_quantity' => 10,
                'max_quantity' => 5000,
                'sort_order' => 3,
            ],
            [
                'name' => 'Redirect Placement',
                'slug' => 'redirect-placement',
                'description' => 'Manually configured domain redirect placements to your target URL.',
                'unit_price' => 0.001,
                'min_quantity' => 100,
                'max_quantity' => 50000,
                'sort_order' => 4,
            ],
        ];

        foreach ($services as $service) {
            SeoService::updateOrCreate(['slug' => $service['slug']], $service + ['is_active' => true]);
        }
    }
}
