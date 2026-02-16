<?php

namespace Database\Seeders;

use App\Models\Cart;
use App\Models\User;
use Illuminate\Database\Seeder;

class CartSeeder extends Seeder
{
    /**
     * This seeder populates the carts table by creating a cart for each existing user and also creating a few carts for guest users. It uses the Cart factory to generate the cart records, associating them with users where applicable and assigning a unique guest token for guest carts.
     */
    public function run(): void
    {
        User::lazy()->each(function ($user) {
            Cart::factory()->for($user)->create();
        });

        Cart::factory()->guest()->count(5)->create();
    }
}
