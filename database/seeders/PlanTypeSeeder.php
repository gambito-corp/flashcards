<?php

namespace Database\Seeders;

use App\Models\PlanType;
use Illuminate\Database\Seeder;

class PlanTypeSeeder extends Seeder
{
    public function run()
    {
        $planTypes = [
            [
                'name' => 'Freemium',
                'description' => 'Plan gratuito con características básicas'
            ],
            [
                'name' => 'Premium',
                'description' => 'Plan con características avanzadas'
            ],
            [
                'name' => 'Profesional',
                'description' => 'Plan completo para uso profesional'
            ],
        ];

        foreach ($planTypes as $planType) {
            PlanType::create($planType);
        }
    }
}
