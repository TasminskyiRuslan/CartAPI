<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * This method orchestrates the seeding process by calling individual seeders for users, products, carts, and cart items. It ensures that the database is populated with a comprehensive set of data for testing and development purposes, including user accounts, product listings, shopping carts, and the items within those carts.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            ProductSeeder::class,
            CartSeeder::class,
            CartItemSeeder::class,
        ]);
    }
}
