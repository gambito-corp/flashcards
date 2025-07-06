<?php
// app/Services/Api/MedBank/MedBankService.php

namespace App\Services\Api\MedBank;

use App\Enums\Api\MedBank\DataTypeEnum;
use App\Enums\Api\MedBank\DifficultyEnum;
use App\Models\AiExamQuestion;
use App\Models\Area;
use App\Models\Category;
use App\Models\Exam;
use App\Models\ExamResult;
use App\Models\ExamUserAnswer;
use App\Models\Question;
use App\Models\Tipo;
use App\Models\Universidad;
use App\Services\Api\MedBank\Factories\DataStrategyFactory;
use App\Services\Api\OpenAI\Exams;
use App\Services\Api\OpenAI\Resume;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpWord\IOFactory;
use Smalot\PdfParser\Parser as PdfParser;


class MedBankService
{
    public function __construct(
        public Exams            $examsService,
        private readonly Resume $resumeService
    )
    {
    }

    public function getAreas(DataTypeEnum $type): Collection
    {
        $strategy = DataStrategyFactory::create($type);
        return $strategy->getAreas();
    }

    public function getCategories(?int $areaId, DataTypeEnum $type): Collection
    {
        $strategy = DataStrategyFactory::create($type);
        return $strategy->getCategories($areaId);
    }

    public function getTipos(?int $categoryId, DataTypeEnum $type): Collection
    {
        $strategy = DataStrategyFactory::create($type);
        return $strategy->getTipos($categoryId);
    }

    public function getUniversities(): Collection
    {
        return Universidad::query()->get();
    }

    public function getDifficulties(): array
    {
        try {
            // Obtener todas las dificultades disponibles
            $availableDifficulties = DifficultyEnum::getAvailable();

            // Convertir a array para la respuesta de la API
            $difficulties = array_map(
                fn(DifficultyEnum $difficulty) => $difficulty->toArray(),
                $availableDifficulties
            );

            // Log para debugging
            \Log::info('Dificultades obtenidas', [
                'total_difficulties' => count($difficulties),
                'unlocked_count' => count(array_filter($difficulties, fn($d) => $d['unlocked']))
            ]);

            return array_values($difficulties); // Reindexar el array

        } catch (\Exception $e) {
            \Log::error('Error en MedBankService::getDifficulties: ' . $e->getMessage());
            throw $e;
        }
    }

    public function generatePdfSummary($pdfFile)
    {
        $extractedText = $this->convertDocumentToText($pdfFile);
        return $this->resumeService->generateResume($extractedText['extracted_text']);
    }

    public function generateExamFromText(string $extractedText, array $options = []): array
    {
        try {
            $examData = $this->examsService->generateExam($extractedText, $options);
            return [
                'success' => true,
                'data' => $examData,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al generar el examen: ' . $e->getMessage(),
            ];
        }
    }

    public function convertDocumentToText(UploadedFile $file): array
    {
        $extension = strtolower($file->getClientOriginalExtension());

        $extractedText = match ($extension) {
            'pdf' => $this->extractFromPdf($file),
            'txt' => $this->extractFromTxt($file),
            'doc', 'docx' => $this->extractFromWord($file),
            default => throw new \InvalidArgumentException('Tipo de archivo no soportado')
        };

        $cleanText = $this->cleanText($extractedText);
        $tokenCount = $this->estimateTokens($cleanText);

        return [
            'original_name' => $file->getClientOriginalName(),
            'file_type' => $extension,
            'file_size' => $file->getSize(),
            'file_size_mb' => round($file->getSize() / 1024 / 1024, 2),
            'extracted_text' => $cleanText,
            'estimated_tokens' => $tokenCount,
            'word_count' => str_word_count($cleanText),
            'character_count' => strlen($cleanText),
            'can_process' => $tokenCount <= 1000000, // Límite para GPT-4.1-nano
        ];
    }

    private function extractFromPdf(UploadedFile $file): string
    {
        try {
            $parser = new PdfParser();
            $pdf = $parser->parseFile($file->getPathname());
            $text = $pdf->getText();

            if (empty(trim($text))) {
                throw new \Exception('El PDF no contiene texto extraíble o está compuesto solo de imágenes');
            }

            return $text;

        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();

            // Manejar errores específicos de PDFs
            if (strpos($errorMessage, 'Secured pdf') !== false) {
                throw new \Exception('❌ PDF Protegido: Este archivo PDF está protegido con contraseña o tiene restricciones de seguridad. Por favor, desbloquea el PDF o usa una versión sin protección.');
            }

            if (strpos($errorMessage, 'Unable to find') !== false) {
                throw new \Exception('❌ PDF Corrupto: El archivo PDF parece estar dañado o corrupto. Intenta con otro archivo.');
            }

            if (strpos($errorMessage, 'not supported') !== false) {
                throw new \Exception('❌ Formato No Soportado: Este tipo de PDF no es compatible. Intenta convertirlo a un formato estándar.');
            }

            // Error genérico
            throw new \Exception('❌ Error al procesar PDF: ' . $errorMessage . '. Verifica que sea un PDF válido y no esté protegido.');
        }
    }

    private function extractFromTxt(UploadedFile $file): string
    {
        $content = file_get_contents($file->getPathname());

        if ($content === false) {
            throw new \Exception('Error al leer el archivo de texto');
        }

        // Detectar encoding y convertir a UTF-8
        $encoding = mb_detect_encoding($content, ['UTF-8', 'ISO-8859-1', 'Windows-1252'], true);
        if ($encoding && $encoding !== 'UTF-8') {
            $content = mb_convert_encoding($content, 'UTF-8', $encoding);
        }

        return $content;
    }

    private function extractFromWord(UploadedFile $file): string
    {
        try {
            $phpWord = IOFactory::load($file->getPathname());
            $text = '';

            foreach ($phpWord->getSections() as $section) {
                foreach ($section->getElements() as $element) {
                    $text .= $this->extractTextFromElement($element) . "\n";
                }
            }

            if (empty(trim($text))) {
                throw new \Exception('No se pudo extraer texto del documento Word');
            }

            return $text;
        } catch (\Exception $e) {
            throw new \Exception('Error al procesar documento Word: ' . $e->getMessage());
        }
    }

    private function extractTextFromElement($element): string
    {
        $text = '';

        if (method_exists($element, 'getText')) {
            $text = $element->getText();
        } elseif (method_exists($element, 'getElements')) {
            foreach ($element->getElements() as $subElement) {
                $text .= $this->extractTextFromElement($subElement);
            }
        }

        return $text;
    }

    private function cleanText(string $text): string
    {
        // Eliminar caracteres de control
        $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $text);

        // Normalizar espacios
        $text = preg_replace('/\s+/', ' ', $text);

        // Eliminar líneas vacías múltiples
        $text = preg_replace('/\n\s*\n\s*\n/', "\n\n", $text);

        return trim($text);
    }

    private function estimateTokens(string $text): int
    {
        // Estimación: 1 token ≈ 4 caracteres
        return intval(strlen($text) / 4);
    }

    public function countAvailableQuestions(array $filters): array
    {
        // Crear clave de cache
        $cacheKey = 'question_count_' . md5(serialize(array_filter($filters)));
        // Intentar obtener del cache (2 minutos)
        return \Cache::remember($cacheKey, 120, function () use ($filters) {
            try {
                if (!empty($filters['tipo'])) {
                    return $this->countByAreaOrCategory($filters);
                } else {
                    return $this->countByTipoId($filters);
                }
            } catch (\Exception $e) {
                \Log::error('Error en countAvailableQuestions: ' . $e->getMessage(), [
                    'filters' => $filters,
                    'trace' => $e->getTraceAsString()
                ]);
                throw $e;
            }
        });
    }

    private function countByAreaOrCategory(array $filters): array
    {
        $query = Question::query();

        // Si es conteo de fallos personales o globales
        if (!empty($filters['failed_type'])) {
            $failedQuery = ExamUserAnswer::query()->where('is_correct', false);
            if ($filters['failed_type'] === 'personal') {
                $failedQuery->where('user_id', auth()->id());
            }
            $failedQuestionIds = $failedQuery->pluck('question_id');
            $query->whereIn('id', $failedQuestionIds);
        }

        if (!empty($filters['area_id'])) {
            $area = Area::find($filters['area_id']);
            if (!$area) {
                throw new \InvalidArgumentException("Área no encontrada: {$filters['area_id']}");
            }
            $query->whereHas('tipos', function ($tiposQuery) use ($filters) {
                $tiposQuery->whereHas('category', function ($categoryQuery) use ($filters) {
                    $categoryQuery->where('area_id', $filters['area_id']);
                });
            });
        } elseif (!empty($filters['category_id'])) {
            $category = Category::find($filters['category_id']);
            if (!$category) {
                throw new \InvalidArgumentException("Categoría no encontrada: {$filters['category_id']}");
            }
            $query->whereHas('tipos', function ($tiposQuery) use ($filters) {
                $tiposQuery->where('category_id', $filters['category_id']);
            });
        } else {
            throw new \InvalidArgumentException('Se requiere area_id o category_id cuando se usa tipo');
        }

        $query->where('approved', true);

        $count = $query->count();

        return [
            'count' => $count,
            'filters' => array_filter($filters),
        ];
    }

    private function countByTipoId(array $filters): array
    {
        if (empty($filters['tipo_id'])) {
            throw new \InvalidArgumentException('tipo_id es requerido');
        }
        // Buscar el tipo
        $tipo = Tipo::find($filters['tipo_id']);

        if (!$tipo) {
            throw new \InvalidArgumentException("Tipo no encontrado: {$filters['tipo_id']}");
        }

        $count = Question::query()
            ->where('approved', true)
            ->whereHas('tipos', function ($tiposQuery) use ($filters) {
                $tiposQuery->where('tipos.id', $filters['tipo_id']);
            })
            ->when(
                isset($filters['failed_type']) && in_array($filters['failed_type'], ['personal-failed', 'global-failed']),
                function ($q) use ($filters) {
                    $q->join('exam_user_answers as eua', 'questions.id', '=', 'eua.question_id')
                        ->where('eua.is_correct', false);

                    if ($filters['failed_type'] === 'personal-failed') {
                        $q->where('eua.user_id', auth()->id());
                    }
                }
            )
            ->when(!empty($filters['university_id']), function ($q) use ($filters) {
                $q->whereHas('universidades', function ($universidadesQuery) use ($filters) {
                    $universidadesQuery->where('universidades.id', $filters['university_id']);
                });
            })
            ->distinct()
            ->count('questions.id');

        return [
            'count' => $count,
            'filters' => array_filter($filters),
        ];
    }

    public function getExam($examId)
    {
        $exam = Exam::query()
            ->with([
                'questions',
                'questions.options',
                'questions.optionsCorrectas',
                'aiQuestions.options',
                'aiQuestions.optionsCorrectas'
            ])
            ->findOrFail($examId);

        // Si hay preguntas "reales", las mezclamos y devolvemos
        if ($exam->questions->isNotEmpty()) {
            $exam->setRelation('questions', $exam->questions->shuffle()->values());
            $exam->questions->each(function ($question) {
                $question->setRelation('options', $question->options->shuffle()->values());
            });
            // Para mantener compatibilidad, puedes dejar ai_questions vacío si lo prefieres
            $exam->setRelation('aiQuestions', collect());
        } // Si no hay preguntas "reales", usamos las AI
        else if ($exam->aiQuestions->isNotEmpty()) {
            $exam->setRelation('aiQuestions', $exam->aiQuestions->shuffle()->values());
            $exam->aiQuestions->each(function ($question) {
                $question->setRelation('options', $question->options->shuffle()->values());
            });
            // Para mantener compatibilidad, puedes dejar questions vacío
            $exam->setRelation('questions', collect());
        }

        return $exam;
    }

    public function resolveExam(mixed $examId, mixed $answers, $ai = false)
    {
        try {
            DB::beginTransaction();
            $userId = auth()->id();
            $answers = collect($answers);

            // 1. Selecciona la relación y modelo según el tipo de examen
            $relation = $ai ? 'aiQuestions.options' : 'questions.options';
            $questionKey = $ai ? 'aiQuestions' : 'questions';
            $questionDate = $ai ? 'ai_question_id' : 'question_id';
            $answerModel = $ai ? AiExamQuestion::class : ExamUserAnswer::class;
            $answerWhere = $ai
                ? fn($qid) => ['user_id' => $userId, 'exam_id' => $examId, 'question_id' => $qid]
                : fn($qid) => ['user_id' => $userId, 'exam_id' => $examId, 'question_id' => $qid];

            // 2. Carga el examen y sus preguntas
            $exam = Exam::query()->with($relation)->findOrFail($examId);

            $correcciones = [];
            $questions = $exam->{$questionKey};
            $total = $questions->count();
            $aciertos = 0;

            foreach ($answers as $answer) {
                $correcta = false;
                $pregunta = $questions->firstWhere('id', $answer[$questionDate])->loadMissing('optionsCorrectas');
                if ($answer['option_id'] === $pregunta?->optionsCorrectas->first()?->id) {
                    $aciertos++;
                    $correcta = true;
                }
                if ($ai) {
                    $model = AiExamQuestion::query()->updateOrCreate(
                        [
                            'user_id' => $userId,
                            'exam_id' => $examId,
                            'question_id' => $pregunta?->id,
                        ],
                        [
                            'created_at' => now(),
                        ]
                    );
                } else {
                    $examUserAnswer = ExamUserAnswer::query()
                        ->where('exam_id', $examId)
                        ->where('user_id', $userId)
                        ->where('question_id', $pregunta?->id)
                        ->first();
                    if (!$examUserAnswer) {
                        $examUserAnswer = new ExamUserAnswer();
                        $examUserAnswer->user_id = $userId;
                        $examUserAnswer->exam_id = $examId;
                        $examUserAnswer->question_id = $pregunta?->id;
                        $examUserAnswer->fail_weight = 0; // Peso inicial de fallo
                    } else {
                        if ($correcta) {
                            $examUserAnswer->fail_weight = max(0, $examUserAnswer->fail_weight - 1);
                        } else {
                            $examUserAnswer->fail_weight = $examUserAnswer->fail_weight + 1;
                        }
                        $examUserAnswer->is_correct = $correcta;
                        $examUserAnswer->option_id = $pregunta?->optionsCorrectas->first()?->id;
                        $examUserAnswer->save();
                    }
                }
            }

            // 3. Puntuación en base 100
            $score = $total > 0 ? round(($aciertos / $total) * 100) : 0;
            // 4. Guardar resultado global
            ExamResult::query()->updateOrCreate(
                [
                    'user_id' => $userId,
                    'exam_id' => $examId,
                ],
                [
                    'total_score' => $score,
                ]
            );
            DB::commit();
            // 6. Devuelve la corrección y el score
            return [
                'success' => true,
                'score' => $score,
                'aciertos' => $aciertos,
                'total' => $total,
                'correcciones' => $correcciones,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            dd('Error al resolver el examen', [
                'exam_id' => $examId,
                'answers' => $answers,
                'ai' => $ai,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw new \Exception('Error al resolver el examen: ' . $e->getMessage());
        }
    }

    public function generateExamAI(array $all)
    {
        $exam = $this->examsService->generateExam();
        dd('all', $all);
    }

    public function generateArrayConfig(mixed $current_config, mixed $saved_configs = [])
    {
        return (!empty($saved_configs) && is_array($saved_configs))
            ? array_merge($saved_configs, [$current_config])
            : [$current_config];
    }

}
