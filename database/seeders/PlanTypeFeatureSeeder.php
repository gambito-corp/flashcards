<?php

namespace Database\Seeders;

use App\Models\PlanType;
use App\Models\Feature;
use Illuminate\Database\Seeder;

class PlanTypeFeatureSeeder extends Seeder
{
    public function run()
    {
        // Freemium
        $freemium = PlanType::where('name', 'Freemium')->first();
        $features = [
            'MedCourses' => json_encode([
                'acceso' => true,
                'comentar' => false,
                'interactuar' => false,
                'cursos_pago' => true
            ]),
            'MedBank' => json_encode([
                'examenes_semana' => 10
            ]),
            'MedFlash' => json_encode([
                'max_flashcards' => 50,
                'borrar' => false
            ]),
            'MedChat' => json_encode([
                'limite_mensual' => 50,
                'modelos' => ['basic', 'investigacion_profunda']
            ])
        ];
        foreach ($features as $code => $value) {
            $featureId = Feature::where('code', $code)->first()->id;
            $freemium->features()->attach($featureId, ['value' => $value]);
        }

        // Premium
        $premium = PlanType::where('name', 'Premium')->first();
        $featuresPremium = [
            'MedCourses' => json_encode([
                'acceso' => true,
                'comentar' => true,
                'editar_comentarios' => true,
                'reaccionar' => true
            ]),
            'MedBank' => json_encode([
                'examenes_semana' => 'ilimitado'
            ]),
            'MedFlash' => json_encode([
                'max_flashcards' => 'ilimitado',
                'borrar' => false
            ]),
            'MedChat' => json_encode([
                'modelos' => [
                    'basic' => 'ilimitado',
                    'advance' => 50,
                    'professional' => 5
                ]
            ])
        ];
        foreach ($featuresPremium as $code => $value) {
            $featureId = Feature::where('code', $code)->first()->id;
            $premium->features()->attach($featureId, ['value' => $value]);
        }

        // Profesional (Pro)
        $pro = PlanType::where('name', 'Profesional')->first();
        $featuresPro = [
            'MedCourses' => json_encode(['acceso_ilimitado' => true]),
            'MedBank' => json_encode(['examenes_ilimitados' => true]),
            'MedFlash' => json_encode(['flashcards_ilimitadas' => true]),
            'MedChat' => json_encode(['modelos' => [
                'basic' => 'ilimitado',
                'advance' => 'ilimitado',
                'professional' => 'ilimitado'
            ]])
        ];
        foreach ($featuresPro as $code => $value) {
            $featureId = Feature::where('code', $code)->first()->id;
            $pro->features()->attach($featureId, ['value' => $value]);
        }
    }
}
