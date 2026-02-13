<?php

namespace Database\Factories;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CartItem>
 */
class CartItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'cart_id' => Cart::factory(),
            'product_id' => Product::factory(),
            'price_snapshot' => null,
            'quantity' => fake()->numberBetween(1, 15),
        ];
    }

    /**
     * Configure the factory to set the price snapshot after making a CartItem.
     *
     * @return static
     */
    public function configure(): static
    {
        return $this->afterMaking(function (CartItem $item) {
            $item->price_snapshot ??= $item->product?->price;
        });
    }
}
