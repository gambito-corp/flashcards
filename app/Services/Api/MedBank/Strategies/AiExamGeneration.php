<?php

namespace App\Services\Api\MedBank\Strategies;

use App\Models\AiOption;
use App\Models\AiQuestion;
use App\Models\Area;
use App\Models\Category;
use App\Models\Exam;
use App\Models\Tipo;
use App\Models\Universidad;
use Illuminate\Support\Facades\DB;

class AiExamGeneration implements ExamGenerationStrategyInterface
{

    public function generateExam(array $data): array
    {
        $config = (!empty($data['saved_configs']) && is_array($data['saved_configs']))
            ? array_merge($data['saved_configs'], [$data['current_config']])
            : [$data['current_config']];

        $areas = Area::query()->get()->keyBy('id');
        $categorias = Category::query()->get()->keyBy('id');
        $tipos = Tipo::query()->get()->keyBy('id');
        $universidades = Universidad::query()->get()->keyBy('id');

        $requestBlocks = [];
        foreach ($config as $c) {
            $requestBlocks[] = [
                'area' => optional($areas->get($c['area_id'] ?? null))->name,
                'category' => optional($categorias->get($c['category_id'] ?? null))->name,
                'tipo' => optional($tipos->get($c['tipo_id'] ?? null))->name,
                'num_questions' => $c['num_questions'] ?? 0,
                'university' => optional($universidades->get($c['university_id'] ?? null))->name,
                'difficulty' => $c['difficulty'] ?? null,
            ];
        }

        $examsService = app('App\Services\Api\OpenAI\Exams');
        $prompt = $examsService->setBuildPrompt($requestBlocks);
        $exam = $examsService->generateExam($prompt, $requestBlocks);

        DB::beginTransaction();
        try {
            $examen = Exam::create([
                'title' => $exam['exam']['title'] ?? 'Examen AI',
                'description' => $exam['exam']['description'] ?? 'Generado por AI',
                'time_limit' => $data['duration'] ?? null,
            ]);

            // Guarda cada bloque de preguntas recibido de la IA
            foreach ($exam['questions'] as $questionBlock) {
                if (empty($questionBlock['questions']) || !is_array($questionBlock['questions'])) {
                    continue;
                }
                foreach ($questionBlock['questions'] as $q) {
                    $pregunta = AiQuestion::create([
                        'user_id' => auth()->id(),
                        'exam_id' => $examen->id,
                        'type' => $q['tipo'] ?? ($questionBlock['tipo'] ?? 'multiple_choice'),
                        'content' => $q['enunciado'] ?? '',
                        'explicacion' => $q['explicacion'] ?? $q['justificacion'] ?? null,
                    ]);
                    foreach ($q['opciones'] as $key => $optionContent) {
                        AiOption::create([
                            'question_id' => $pregunta->id,
                            'content' => is_array($optionContent) ? ($optionContent['content'] ?? '') : $optionContent,
                            'is_correct' => isset($q['respuesta_correcta']) && $q['respuesta_correcta'] == $key ? 1 : 0,
                            'points' => 1,
                        ]);
                    }
                }
            }

            DB::commit();
            return [
                'exam_id' => $examen->id,
            ];
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'trace' => config('app.debug') ? $e->getTraceAsString() : null,
            ];
        }
    }

}
