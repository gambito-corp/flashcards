<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductsTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('products')->insert([
            [
                'name' => 'Suscripción Mensual',
                'price' => 49.90,       // Precio de ejemplo
                'duration_days' => 30,
                'description' => 'Acceso mensual a nuestros servicios.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Suscripción Semestral',
                'price' => 249.90,      // Precio de ejemplo
                'duration_days' => 180,
                'description' => 'Acceso semestral a nuestros servicios.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
