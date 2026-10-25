<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    public function definition(): array
    {
        return [
            'category_id' => Category::inRandomOrder()->value('id') ?? Category::factory(),
            'sku' => fake()->unique()->bothify('SKU-####'),
            'name' => ucwords(fake()->words(3, true)),
            'price' => fake()->randomFloat(2, 15000, 350000),
            'stock' => fake()->numberBetween(2, 50),
            'minimum_stock' => 5,
        ];
    }
}