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
        User::lazy()->each(function ($user) {
            Cart::factory()->for($user)->create();
        });

        Cart::factory()->guest()->count(5)->create();
    }
}
