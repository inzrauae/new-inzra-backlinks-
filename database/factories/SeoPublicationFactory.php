<?php

namespace Database\Factories;

use App\Enums\PublicationStatus;
use App\Models\SeoOrder;
use App\Models\SeoPublication;
use Illuminate\Database\Eloquent\Factories\Factory;

class SeoPublicationFactory extends Factory
{
    protected $model = SeoPublication::class;

    public function definition(): array
    {
        return [
            'seo_order_id' => SeoOrder::factory(),
            'publisher_name' => fake()->company(),
            'publisher_url' => fake()->url(),
            'published_url' => fake()->url(),
            'target_url' => 'https://example.com',
            'anchor_text' => fake()->words(3, true),
            'country' => 'United States',
            'publication_date' => now()->toDateString(),
            'status' => PublicationStatus::Verified,
        ];
    }
}
