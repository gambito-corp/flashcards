<?php

namespace App\Services\Api\Questions;

use App\Models\Exam;
use App\Models\ExamResult;
use App\Services\Api\OpenAI\Questions;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use League\Csv\Reader;
use League\Csv\Statement;

class QuestionsServices
{
    public function __construct(protected Questions $openAiService)
    {
    }

    public function importFromCsv(UploadedFile $file): array
    {
        // Detectar delimitador
        $delimiter = $this->detectDelimiter($file->getRealPath());

        // Procesar CSV
        $csv = Reader::createFromPath($file->getRealPath(), 'r');
        $csv->setDelimiter($delimiter);
        $csv->setHeaderOffset(0);

        $headers = $csv->getHeader();

        // Validar detección de delimitador
        if (count($headers) === 1) {
            throw new \Exception("No se pudo detectar el delimitador correctamente");
        }

        // Procesar registros
        $records = Statement::create()->process($csv);
        $processedData = $this->processRecords($records, $headers);

        // Procesar con OpenAI
        $correctionResults = $this->processQuestionsWithAI($processedData);

        return [
            'message' => 'CSV procesado exitosamente',
            'delimiter_detected' => $delimiter,
            'headers' => $headers,
            'total_rows' => count($processedData),
            'data' => $correctionResults['questions'],
            'sample' => array_slice($correctionResults['questions'], 0, 3),
            'correction_stats' => $correctionResults['stats']
        ];
    }

    private function processQuestionsWithAI(array $questions): array
    {
        $results = [
            'processed' => 0,
            'corrected' => 0,
            'displaced_fixed' => 0,
            'completed' => 0,
            'openai_calls' => 0,
            'errors' => 0,
            'failed_questions' => []
        ];

        foreach ($questions as $index => &$question) {
            try {
                \Log::info("Procesando pregunta {$index}");

                // Usar el nuevo método processQuestion
                $correctedQuestion = $this->openAiService->processQuestion($question);

                // Analizar qué tipo de corrección se aplicó
                $correctionType = $this->analyzeCorrectionType($question, $correctedQuestion);

                // Actualizar estadísticas
                if ($correctionType['was_displaced']) {
                    $results['displaced_fixed']++;
                }
                if ($correctionType['was_incomplete']) {
                    $results['completed']++;
                }

                $question = $correctedQuestion;
                $results['processed']++;
                $results['corrected']++;
                $results['openai_calls'] += $correctionType['openai_calls_used'];

            } catch (\Exception $e) {
                $results['errors']++;
                $results['failed_questions'][] = [
                    'index' => $index,
                    'error' => $e->getMessage(),
                    'original_question' => $question
                ];

                \Log::error("Error procesando pregunta {$index}: " . $e->getMessage());

                // Mantener la pregunta original si falla la corrección
                // O aplicar corrección básica
                $question = $this->applyBasicCorrection($question);
            }

            // Log de progreso cada 10 preguntas
            if (($results['processed'] + $results['errors']) % 10 === 0) {
                \Log::info("Progreso: {$results['processed']} procesadas, {$results['errors']} errores");
            }
        }

        return [
            'questions' => $questions,
            'stats' => $results
        ];
    }

    private function analyzeCorrectionType(array $original, array $corrected): array
    {
        $analysis = [
            'was_displaced' => false,
            'was_incomplete' => false,
            'openai_calls_used' => 0
        ];

        // Detectar si hubo corrección de desplazamiento
        if ($this->wasDisplacementCorrected($original, $corrected)) {
            $analysis['was_displaced'] = true;
            $analysis['openai_calls_used']++;
        }

        // Detectar si hubo completado de campos
        if ($this->wasCompletionApplied($original, $corrected)) {
            $analysis['was_incomplete'] = true;
            $analysis['openai_calls_used']++;
        }

        // Si no hubo cambios significativos, probablemente solo se validó
        if (!$analysis['was_displaced'] && !$analysis['was_incomplete']) {
            $analysis['openai_calls_used'] = 1; // Solo validación
        }

        return $analysis;
    }

    private function wasDisplacementCorrected(array $original, array $corrected): bool
    {
        // Comparar longitudes de contenido para detectar intercambio
        $originalContentLength = strlen($original['content'] ?? '');
        $correctedContentLength = strlen($corrected['content'] ?? '');

        // Si el contenido cambió significativamente, probablemente hubo desplazamiento
        return abs($originalContentLength - $correctedContentLength) > 50;
    }

    private function wasCompletionApplied(array $original, array $corrected): bool
    {
        // Contar campos vacíos en original vs corregido
        $originalEmpty = $this->countEmptyFields($original);
        $correctedEmpty = $this->countEmptyFields($corrected);

        return $originalEmpty > $correctedEmpty;
    }

    private function countEmptyFields(array $question): int
    {
        $emptyCount = 0;
        $fieldsToCheck = ['content', 'answer1', 'answer2', 'answer3', 'answer4', 'explicacion'];

        foreach ($fieldsToCheck as $field) {
            if (empty(trim($question[$field] ?? ''))) {
                $emptyCount++;
            }
        }

        return $emptyCount;
    }

    private function applyBasicCorrection(array $question): array
    {
        // Corrección básica sin OpenAI como fallback
        $corrected = $question;

        // Limpiar encoding
        foreach ($corrected as $key => $value) {
            if (is_string($value)) {
                $corrected[$key] = $this->cleanValue($value);
            }
        }

        // Completar campos críticos faltantes
        if (empty($corrected['answer1']) && !empty($corrected['answer2'])) {
            $corrected['answer1'] = 'Opción A';
        }

        if (empty($corrected['explicacion'])) {
            $corrected['explicacion'] = 'Explicación pendiente de completar.';
        }

        // Asegurar que hay una respuesta correcta
        $hasCorrect = false;
        for ($i = 1; $i <= 4; $i++) {
            if (($corrected["is_correct{$i}"] ?? '') === 'true') {
                $hasCorrect = true;
                break;
            }
        }

        if (!$hasCorrect && !empty($corrected['answer1'])) {
            $corrected['is_correct1'] = 'true';
        }

        return $corrected;
    }

    private function processRecords($records, array $headers): array
    {
        $data = [];

        foreach ($records as $record) {
            $processedRecord = $this->transformRecord($record, $headers);

            if ($processedRecord) {
                $data[] = $processedRecord;
            }
        }

        return $data;
    }

    private function transformRecord(array $record, array $headers): ?array
    {
        // Validar que el registro tenga datos
        if (empty(array_filter($record))) {
            return null;
        }

        $transformed = [];

        foreach ($headers as $index => $header) {
            $value = $record[$header] ?? null;
            $transformed[$header] = $this->cleanValue($value);
        }

        return $transformed;
    }

    private function cleanValue($value): ?string
    {
        if (is_null($value)) {
            return null;
        }

        $cleaned = trim($value);

        if ($cleaned === '') {
            return null;
        }

        $cleaned = $this->fixStringEncoding($cleaned);

        return $cleaned;
    }

    private function fixStringEncoding(string $input): string
    {
        $encoding = mb_detect_encoding($input, ['UTF-8', 'ISO-8859-1', 'Windows-1252', 'CP1252'], true);

        if ($encoding && $encoding !== 'UTF-8') {
            $input = mb_convert_encoding($input, 'UTF-8', $encoding);
        }

        if (!mb_check_encoding($input, 'UTF-8')) {
            $input = utf8_encode($input);
        }

        return $input;
    }

    private function detectDelimiter(string $filePath): string
    {
        $delimiters = [';', ',', '\t', '|', ':'];

        $handle = fopen($filePath, 'r');
        $firstLine = fgets($handle);
        $secondLine = fgets($handle);
        fclose($handle);

        $maxCount = 0;
        $bestDelimiter = ',';

        foreach ($delimiters as $delimiter) {
            $count1 = substr_count($firstLine, $delimiter);
            $count2 = substr_count($secondLine, $delimiter);

            if ($count1 > 0 && $count1 === $count2 && $count1 > $maxCount) {
                $maxCount = $count1;
                $bestDelimiter = $delimiter;
            }
        }

        return $bestDelimiter;
    }

    public function getLastResults()
    {
        return Exam::query()
            ->with('examResults')
            ->whereHas('examResults', function ($query) {
                $query->where('user_id', auth()->id());
            })
            ->latest()
            ->take(5)
            ->get();
    }

    public function getGraphExamsDataResults()
    {
        $userId = auth()->id();
        $results = ExamResult::query()->where('user_id', $userId)->orderBy('created_at')->get();

        // Agrupación diaria
        $daily = $results->groupBy(function ($item) {
            return Carbon::parse($item->created_at)->format('d/m/Y');
        })->map(function ($group) {
            return round($group->avg('total_score'), 2);
        });

        // Agrupación semanal (por semana del año y año)
        $weekly = $results->groupBy(function ($item) {
            return Carbon::parse($item->created_at)->format('W/Y');
        })->map(function ($group) {
            return round($group->avg('total_score'), 2);
        });

        // Agrupación mensual
        $monthly = $results->groupBy(function ($item) {
            return Carbon::parse($item->created_at)->format('m/Y');
        })->map(function ($group) {
            return round($group->avg('total_score'), 2);
        });

        return [
            'daily' => [
                'labels' => $daily->keys(),
                'data' => $daily->values(),
            ],
            'weekly' => [
                'labels' => $weekly->keys(),
                'data' => $weekly->values(),
            ],
            'monthly' => [
                'labels' => $monthly->keys(),
                'data' => $monthly->values(),
            ]
        ];
    }
}
