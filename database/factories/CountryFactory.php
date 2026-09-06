<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CountryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->country(),
            'code' => fake()->unique()->countryCode(),
            'sort_order' => 0,
        ];
    }
}
