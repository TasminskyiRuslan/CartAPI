<?php

namespace Database\Seeders;

use App\Models\Cart;
use App\Models\User;
use Illuminate\Database\Seeder;

class CartSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::query()->each(function ($user) {
            Cart::factory()->forUser($user)->create();
        });

        Cart::factory()->count(5)->create();
    }
}
