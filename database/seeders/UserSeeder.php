<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * This seeder populates the users table by creating 50 user records using the User factory. Each user will have a unique name and email, and a default password as defined in the User factory.
     */
    public function run(): void
    {
        User::factory(7)->create();
    }
}
