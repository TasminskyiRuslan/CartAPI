<?php

namespace Database\Seeders;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Database\Seeder;

class CartItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = Product::all();

        Cart::query()->each(function ($cart) use ($products) {
            CartItem::factory()
                ->count(rand(1, 4))
                ->for($cart)
                ->state(fn () => [
                    'product_id' => $products->random()->id,
                ])
                ->create();
        });
    }
}
