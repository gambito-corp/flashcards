<?php

namespace App\Services\Api\MedBank\Strategies;

use App\Models\Exam;
use App\Models\Question;
use Illuminate\Support\Facades\DB;

class StandardExamGenerationStrategy implements ExamGenerationStrategyInterface
{

    public function generateExam(array $data): array
    {
        try {
            DB::beginTransaction();
            $exam = Exam::query()->create([
                'title' => $data['title'],
                'description' => 'standard exam',
                'time_limit' => $data['duration'] ?? null,
            ]);
            $allSelectedQuestions = collect();
            // Unifica configs: siempre será un array de configs
            $configs = [];
            if (!empty($data['saved_configs']) && is_array($data['saved_configs'])) {
                // Modo mixto: saved_configs + current_config
                $configs = array_merge($data['saved_configs'], [$data['current_config']]);
            } else {
                // Modo normal: solo current_config
                $configs = [$data['current_config']];
            }
            // Recorre cada configuración y agrega las preguntas seleccionadas
            foreach ($configs as $config) {
                $questions = Question::select('questions.*')
                    ->where('questions.approved', 1)
                    ->when(!empty($config['university_id']), function ($q) use ($config) {
                        $q->whereHas('universidades', function ($q2) use ($config) {
                            $q2->where('universidades.id', $config['university_id']);
                        });
                    })
                    ->whereHas('tipos', function ($q) use ($config) {
                        $q->where('tipos.id', $config['tipo_id']);
                    })
                    ->inRandomOrder()
                    ->take($config['num_questions'])
                    ->pluck('id');

                $allSelectedQuestions = $allSelectedQuestions->merge($questions);
            }
            // Elimina duplicados
            $allSelectedQuestions = $allSelectedQuestions->unique()->values();
            // Asocia las preguntas al examen
            $exam->questions()->attach($allSelectedQuestions);
            DB::commit();
        } catch (\Exception $e) {
            \Log::error('Error al generar el examen: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            DB::rollBack();
            throw new \RuntimeException('Error al generar el examen: ' . $e->getMessage());
        }
        return [
            'exam_id' => $exam->id,
        ];
    }
}
