<?php

namespace App\Services\Api\MedBank\Strategies;

use Illuminate\Support\Facades\DB;

class PDFExamGeneration implements ExamGenerationStrategyInterface
{

    public function generateExam(array $data): array
    {
        $medBankService = app('App\Services\Api\MedBank\MedBankService');
        $examsService = app('App\Services\Api\OpenAI\Exams');

        $data['pdf_file'] = $medBankService->convertDocumentToText($data['pdf_file'])['extracted_text'];

        $allQuestions = [];
        $examTotalCount = $data['num_questions'] ?? 10;
        $iterations = 0;

        do {
            $faltantes = $examTotalCount - count($allQuestions);
            $data['num_questions'] = $faltantes;

            // Construye el contexto de preguntas previas para el prompt
            $preguntasPrevias = "";
            foreach ($allQuestions as $q) {
                $preguntasPrevias .= "- " . trim($q['enunciado'] ?? '') . PHP_EOL;
            }
            $contexto = "";
            if (!empty($preguntasPrevias)) {
                $contexto = "ATENCIÓN: Ya se han generado las siguientes preguntas, NO las repitas ni generes variantes ni preguntas similares:\n" . $preguntasPrevias . "\n";
            }

            // Genera el prompt dinámico con contexto
            $prompt = $examsService->setBuildPromptForPDF($data, $contexto);

            $exam = $examsService->generateExam($prompt, $data);
            $questions = $exam['exam']['questions'] ?? [];

            $allQuestions = array_merge($allQuestions, $questions);

            $iterations++;

        } while (count($allQuestions) < $examTotalCount);

        // Corta el array a la cantidad exacta
        $allQuestions = array_slice($allQuestions, 0, $examTotalCount);

        $exam['exam']['questions'] = $allQuestions;

        $exam = $exam['exam'] ?? [];
        try {
            DB::beginTransaction();
            $examModel = \App\Models\Exam::create([
                'title' => $exam['title'] ?? 'Examen AI',
                'description' => $exam['description'] ?? 'Generado por AI',
                'time_limit' => $data['duration'] ?? null,
            ]);

            foreach ($exam['questions'] as $q) {
                $pregunta = \App\Models\AiQuestion::create([
                    'user_id' => auth()->id(),
                    'exam_id' => $examModel->id,
                    'type' => $q['tipo'] ?? 'multiple_choice',
                    'content' => $q['enunciado'] ?? '',
                    'explicacion' => $q['explicacion'] ?? '',
                ]);
                if (!empty($q['opciones']) && is_array($q['opciones'])) {
                    foreach ($q['opciones'] as $index => $option) {
                        \App\Models\AiOption::create([
                            'question_id' => $pregunta->id,
                            'content' => $option,
                            'is_correct' => ($index === $q['respuesta_correcta']),
                            'points' => 1,
                        ]);
                    }
                }
            }
            DB::commit();
            return [
                'exam_id' => $examModel->id,
            ];
        } catch (\Exception $e) {
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
