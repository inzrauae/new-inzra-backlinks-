<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class SeoServiceFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->words(3, true).' Publication';

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1000, 9999),
            'description' => fake()->sentence(),
            'unit_price' => 0.10,
            'min_quantity' => 10,
            'max_quantity' => 5000,
            'is_active' => true,
            'sort_order' => 0,
        ];
    }
}
