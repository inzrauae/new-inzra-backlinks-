<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->sentence(4);

        return [
            'product_category_id' => null,
            'sku' => 'AB-'.fake()->unique()->numerify('######'),
            'ebay_item_number' => null,
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1000, 9999),
            'name' => $name,
            'meta_description' => fake()->sentence(),
            'price' => fake()->randomFloat(2, 5, 300),
            'currency' => 'USD',
            'quantity_available' => fake()->numberBetween(1, 20),
            'quantity_sold' => fake()->numberBetween(0, 10),
            'image_path' => 'images/products/placeholder.png',
            'body' => '<p>'.fake()->paragraph().'</p>',
            'is_active' => true,
        ];
    }
}
