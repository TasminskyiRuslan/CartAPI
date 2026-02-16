<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * This seeder populates the products table by creating 50 product records using the Product factory. Each product will have randomly generated attributes such as name, description, price, and stock quantity, providing a diverse set of products for testing and development purposes.
     */
    public function run(): void
    {
        Product::factory(50)->create();
    }
}
