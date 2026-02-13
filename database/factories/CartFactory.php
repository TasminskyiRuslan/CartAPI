<?php

namespace Database\Factories;

use App\Models\Cart;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Cart>
 */
class CartFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'guest_token' => null,
        ];
    }

    /**
     * Indicate that the cart belongs to a guest user.
     *
     * @return static
     */
    public function guest(): static
    {
        return $this->state(fn () => [
            'user_id' => null,
            'guest_token' => fake()->uuid(),
        ]);
    }
}
