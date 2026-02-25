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
            'expires_at' => now()->addDays(config('cart.expiration_days.user')),
        ];
    }

    /**
     * Indicate that the cart belongs to a guest user.
     *
     * @param string|null $guestToken
     * @return static
     */
    public function guest(?string $guestToken = null): static
    {
        return $this->state(fn () => [
            'user_id' => null,
            'guest_token' => $guestToken ?? fake()->uuid(),
            'expires_at' => now()->addDays(config('cart.expiration_days.guest')),
        ]);
    }
}
