<?php

namespace Database\Seeders;

use App\Models\Plan;
use App\Models\PlanType;
use App\Models\PlanPeriod;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run()
    {
        $planPeriods = PlanPeriod::all();

        // Freemium (Gratis)
        $freemium = PlanType::where('name', 'Freemium')->first();
        foreach ($planPeriods as $period) {
            Plan::create([
                'plan_type_id' => $freemium->id,
                'plan_period_id' => $period->id,
                'price' => 0,
                'active' => true
            ]);
        }

        // Premium
        $premium = PlanType::where('name', 'Premium')->first();
        $premiumPrices = [
            'Mensual' => 49.90,
            'Semestral' => 249.90,
            'Anual' => 450.00
        ];
        foreach ($planPeriods as $period) {
            Plan::create([
                'plan_type_id' => $premium->id,
                'plan_period_id' => $period->id,
                'price' => $premiumPrices[$period->name],
                'active' => true
            ]);
        }

        // Profesional (Pro)
        $pro = PlanType::where('name', 'Profesional')->first();
        $proPrices = [
            'Mensual' => 150.00,
            'Semestral' => 700.00,
            'Anual' => 1000.00
        ];
        foreach ($planPeriods as $period) {
            Plan::create([
                'plan_type_id' => $pro->id,
                'plan_period_id' => $period->id,
                'price' => $proPrices[$period->name],
                'active' => true
            ]);
        }
    }
}
