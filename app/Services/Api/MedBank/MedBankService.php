<?php
// app/Services/Api/MedBank/MedBankService.php

namespace App\Services\Api\MedBank;

use App\Enums\Api\MedBank\DataTypeEnum;
use App\Enums\Api\MedBank\DifficultyEnum;
use App\Models\Area;
use App\Models\Category;
use App\Models\Exam;
use App\Models\ExamResult;
use App\Models\ExamTeam;
use App\Models\ExamUserAnswer;
use App\Models\Question;
use App\Models\Tipo;
use App\Models\Universidad;
use App\Services\Api\MedBank\Factories\DataStrategyFactory;
use App\Services\Api\OpenAI\Exams;
use App\Services\Api\OpenAI\Resume;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpWord\IOFactory;
use Smalot\PdfParser\Parser as PdfParser;


class MedBankService
{
    public function __construct(
        private readonly Exams  $examsService,
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
        if (!empty($filters['area_id'])) {
            // Contar preguntas por área usando relaciones
            $area = Area::find($filters['area_id']);

            if (!$area) {
                throw new \InvalidArgumentException("Área no encontrada: {$filters['area_id']}");
            }

            $count = Question::whereHas('tipos', function ($tiposQuery) use ($filters) {
                $tiposQuery->whereHas('category', function ($categoryQuery) use ($filters) {
                    $categoryQuery->where('area_id', $filters['area_id']);
                });
            })
                ->where('approved', true)
                ->count();

            \Log::info('✅ Conteo por área completado', [
                'area_id' => $filters['area_id'],
                'area_name' => $area->name,
                'total_questions' => $count
            ]);

            return [
                'count' => $count,
                'filters' => array_filter($filters),
            ];

        } elseif (!empty($filters['category_id'])) {
            // Contar preguntas por categoría usando relaciones
            $category = Category::find($filters['category_id']);

            if (!$category) {
                throw new \InvalidArgumentException("Categoría no encontrada: {$filters['category_id']}");
            }

            $count = Question::whereHas('tipos', function ($tiposQuery) use ($filters) {
                $tiposQuery->where('category_id', $filters['category_id']);
            })
                ->where('approved', true)
                ->count();

            \Log::info('✅ Conteo por categoría completado', [
                'category_id' => $filters['category_id'],
                'category_name' => $category->name,
                'total_questions' => $count
            ]);

            return [
                'count' => $count,
                'filters' => array_filter($filters),
            ];
        }

        throw new \InvalidArgumentException('Se requiere area_id o category_id cuando se usa tipo');
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

        // Contar preguntas del tipo usando relaciones
        $query = Question::whereHas('tipos', function ($tiposQuery) use ($filters) {
            $tiposQuery->where('tipos.id', $filters['tipo_id']);
        })->where('approved', true);

        // Aplicar filtro de universidad si existe
        if (!empty($filters['university_id'])) {
            $universidad = Universidad::find($filters['university_id']);

            if (!$universidad) {
                throw new \InvalidArgumentException("Universidad no encontrada: {$filters['university_id']}");
            }

            $query->whereHas('universidades', function ($universidadesQuery) use ($filters) {
                $universidadesQuery->where('universidades.id', $filters['university_id']);
            });
        }

        $count = $query->count();

        \Log::info('✅ Conteo por tipo completado', [
            'tipo_id' => $filters['tipo_id'],
            'tipo_name' => $tipo->name,
            'university_id' => $filters['university_id'] ?? null,
            'total_questions' => $count
        ]);

        return [
            'count' => $count,
            'filters' => array_filter($filters),
        ];
    }

    public function getExam($examId)
    {
        return Exam::query()
            ->with(['questions', 'questions.options', 'questions.optionsCorrectas'])
            ->findOrFail($examId);
    }

    public function resolveExam(mixed $examId, mixed $answers)
    {
        $userId = auth()->id();
        $answers = collect($answers);

        // 1. Obtener todas las preguntas del examen
        $exam = Exam::query()->with('questions.options')->findOrFail($examId);

        $correcciones = [];
        $total = $exam->questions->count();
        $aciertos = 0;

        foreach ($exam->questions as $pregunta) {
            $qid = $pregunta->id;
            $opcion_correcta = $pregunta->options->first(fn($opt) => $opt->is_correct);
            $respuesta = $answers->firstWhere('question_id', $qid);

            $seleccionada = $respuesta['option_id'] ?? null;
            $es_correcta = $seleccionada && $opcion_correcta && ($seleccionada == $opcion_correcta->id);

            $correcciones[$qid] = [
                'correcta' => $opcion_correcta ? $opcion_correcta->id : null,
                'seleccionada' => $seleccionada,
                'es_correcta' => $es_correcta,
            ];

            if ($es_correcta) $aciertos++;

            // Guardar o actualizar ExamUserAnswer
            $examUserAnswer = ExamUserAnswer::query()->updateOrCreate(
                [
                    'user_id' => $userId,
                    'exam_id' => $examId,
                    'question_id' => $qid,
                ],
                [
                    'option_id' => $seleccionada,
                    'is_correct' => $es_correcta,
                ]
            );

            // Actualizar fail_weight en ExamUserAnswer y en Question
            if ($es_correcta) {
                $examUserAnswer->fail_weight = max(0, $examUserAnswer->fail_weight - 1);
                $pregunta->fail_weight = max(0, $pregunta->fail_weight - 1);
            } else {
                $examUserAnswer->fail_weight = $examUserAnswer->fail_weight + 1;
                $pregunta->fail_weight = $pregunta->fail_weight + 1;
            }
            $examUserAnswer->save();
            $pregunta->save();
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

        // 5. (Opcional) Guardar en ExamTeam si usas equipos
        if (method_exists(ExamTeam::class, 'create')) {
            ExamTeam::query()->updateOrCreate(
                [
                    'team_id' => auth()->user()->current_team_id,
                    'exam_id' => $examId,
                ]
            );
        }

        // 6. Devuelve la corrección y el score
        return [
            'success' => true,
            'score' => $score,
            'aciertos' => $aciertos,
            'total' => $total,
            'correcciones' => $correcciones,
        ];
    }

}
