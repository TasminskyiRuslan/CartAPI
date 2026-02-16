<?php

namespace Database\Seeders;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Database\Seeder;

class CartItemSeeder extends Seeder
{
    /**
     * This seeder populates the cart_items table by iterating through each cart and adding a random selection of products as cart items, with random quantities between 1 and 15.
     */
    public function run(): void
    {
        Cart::lazy()->each(function ($cart) {
            Product::lazy()->take(rand(1, Product::count()))->each(function ($product) use ($cart) {
                CartItem::factory()->for($cart)->for($product)->create([
                    'quantity' => rand(1, 15),
                ]);
            });
        });
    }
}
