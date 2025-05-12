<?php

namespace Database\Seeders;

use App\Models\Feature;
use Illuminate\Database\Seeder;

class FeatureSeeder extends Seeder
{
    public function run()
    {
        $features = [
            [
                'code' => 'MedCourses',
                'name' => 'MedCourses',
                'description' => 'Acceso a MedCourses'
            ],
            [
                'code' => 'MedBank',
                'name' => 'MedBank',
                'description' => 'Acceso a Medbank'
            ],
            [
                'code' => 'MedFlash',
                'name' => 'MedFlash',
                'description' => 'Acceso a MedFlash'
            ],
            [
                'code' => 'MedChat',
                'name' => 'MedChat',
                'description' => 'Acceso a MedChat'
            ],
        ];

        foreach ($features as $feature) {
            Feature::create($feature);
        }
    }
}
