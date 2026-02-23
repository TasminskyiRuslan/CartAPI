<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('
            ALTER TABLE carts
            ADD CONSTRAINT carts_owner_check
            CHECK (
                (user_id IS NOT NULL AND guest_token IS NULL)
                OR
                (user_id IS NULL AND guest_token IS NOT NULL)
             )
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('
            ALTER TABLE carts
            DROP CONSTRAINT carts_owner_check
        ');
    }
};
