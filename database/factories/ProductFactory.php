<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => Str::title(fake()->words(rand(1, 3), true)),
            'description' => fake()->sentence(),
            'price' => fake()->randomFloat(2, 0, 1000),
            'image_path' => 'products/' . fake()->sha1() . '.jpg',
        ];
    }
}
