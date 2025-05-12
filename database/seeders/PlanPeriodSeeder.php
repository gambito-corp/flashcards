<?php

namespace Database\Seeders;

use App\Models\PlanPeriod;
use Illuminate\Database\Seeder;

class PlanPeriodSeeder extends Seeder
{
    public function run()
    {
        $planPeriods = [
            [
                'name' => 'Mensual',
                'duration_months' => 1
            ],
            [
                'name' => 'Semestral',
                'duration_months' => 6
            ],
            [
                'name' => 'Anual',
                'duration_months' => 12
            ],
        ];

        foreach ($planPeriods as $planPeriod) {
            PlanPeriod::create($planPeriod);
        }
    }
}
