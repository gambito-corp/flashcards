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
        DB::table('products')->insert([
            [
                'name' => 'Premium Manual',
                'price' => 0.00,
                'duration_days' => 30, // 1 año por defecto
                'description' => 'Acceso premium otorgado manualmente',
                'referencia' => 'manual_premium',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

    }
};
