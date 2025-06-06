<?php

namespace App\Services\Api\Questions;

use App\Models\Category;
use App\Models\Question;
use App\Models\Universidad;
use App\Services\Api\Commons\ImageService;
use App\Services\Api\OpenAI\Questions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Smalot\PdfParser\Parser;
use Smalot\PdfParser\XObject\Image;

class PdfExtractionService
{
    public function __construct(
        protected Questions         $openAiService,
        protected QuestionsServices $questionsService,
        protected ImageService      $imageService
    )
    {
    }

    public function extractAndProcessQuestions(
        UploadedFile $file,
        bool         $saveToDatabase = false,
        bool         $generateExplanations = true
    ): array
    {

        // 1. Extraer texto del PDF
        $pdfText = $this->extractTextFromPdf($file);

        // 2. Dividir en bloques de preguntas
        $questionBlocks = $this->splitIntoQuestionBlocks($pdfText);
        $questionBlocks = $this->formatQuestionBlocks($questionBlocks);
        // 3. Identificar preguntas que necesitan imágenes
        $questionsWithImages = $this->identifyQuestionsWithImages($questionBlocks);
        $questionsWithoutImages = $this->filterQuestionsWithoutImages($questionBlocks, $questionsWithImages);

        // 4. Procesar cada bloque
        $extractedQuestions = [];
        $stats = [
            'total_blocks' => count($questionsWithoutImages),
            'questions_with_image_references' => count($questionsWithImages),
            'processed' => 0,
            'extracted' => 0,
            'with_images' => 0,
            'images_uploaded_to_s3' => 0,
            'with_explanations' => 0,
            'saved_to_db' => 0,
            'errors' => 0,
            'openai_calls' => 0
        ];

        foreach ($questionBlocks as $index => $block) {
            try {
                $extractedQuestion = $this->extractQuestionFromBlock($block);
                $stats['openai_calls']++;

                if ($extractedQuestion) {
                    // Generar explicación si se solicita
                    if ($generateExplanations && empty($extractedQuestion['explicacion'])) {
                        $extractedQuestion = $this->generateExplanation($extractedQuestion);
                        $stats['openai_calls']++;
                        $stats['with_explanations']++;
                    }
                    // Validar y limpiar pregunta
                    dd($extractedQuestion);
                    $cleanedQuestion = $this->openAiService->processQuestion($extractedQuestion);
                    $extractedQuestions[] = $cleanedQuestion;
                    $stats['extracted']++;

                    dd($cleanedQuestion);

                    // Guardar en base de datos si se solicita
                    if ($saveToDatabase) {
                        $this->saveQuestionToDatabase($cleanedQuestion);
                        $stats['saved_to_db']++;
                    }
                }

                $stats['processed']++;

            } catch (\Exception $e) {
                $stats['errors']++;
                dd($e->getMessage());
                \Log::error("Error procesando bloque {$index}: " . $e->getMessage());
            }
        }
        dd([
            'message' => 'PDF procesado exitosamente',
            'stats' => $stats,
            'questions' => $extractedQuestions,
            'sample' => array_slice($extractedQuestions, 0, 3)
        ]);
        return [
            'message' => 'PDF procesado exitosamente',
            'stats' => $stats,
            'questions' => $extractedQuestions,
            'sample' => array_slice($extractedQuestions, 0, 3)
        ];
    }

    private function filterQuestionsWithoutImages(array $questions, array $questionsWithImages): array
    {
        $filteredQuestions = [];

        foreach ($questions as $index => $question) {
            // ✅ SOLO AGREGAR SI NO ESTÁ EN LA LISTA DE PREGUNTAS CON IMAGEN
            if (!in_array($index, $questionsWithImages)) {
                $filteredQuestions[] = $question;
            }
        }

        \Log::info("Preguntas filtradas: " . count($filteredQuestions) . " de " . count($questions) . " (excluidas " . count($questionsWithImages) . " con imagen)");

        return $filteredQuestions;
    }


    private function identifyQuestionsWithImages(array $questionBlocks): array
    {
        $questionsWithImages = [];

        foreach ($questionBlocks as $index => $block) {
            // ✅ CORREGIR: Ahora $block es un array, no un string
            if (is_array($block) && isset($block['pregunta'])) {
                // Buscar referencias de imagen en la pregunta
                if ($this->blockHasImageReference($block['pregunta'])) {
                    $questionsWithImages[] = $index;
                }
            } elseif (is_string($block)) {
                // Fallback para el formato anterior
                if ($this->blockHasImageReference($block)) {
                    $questionsWithImages[] = $index;
                }
            }
        }

        \Log::info("Preguntas con referencias a imágenes: " . count($questionsWithImages));

        return $questionsWithImages;
    }


    private function blockHasImageReference(string $block): bool
    {
        $blockLower = strtolower($block);

        // Patrones regex más flexibles
        $patterns = [
            '/\bver\s+imagen\b/',           // "ver imagen" con límites de palabra
            '/\(ver\s+imagen\)/',           // "(ver imagen)" con paréntesis
            '/\bobserve\s+la\s+imagen\b/',  // "observe la imagen"
            '/\ben\s+la\s+imagen\b/',       // "en la imagen"
            '/\bimagen\s+adjunta\b/',       // "imagen adjunta"
            '/\bver\s+figura\b/',           // "ver figura"
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $blockLower)) {
                return true;
            }
        }

        return false;
    }

    private function extractImagesFromPdf(UploadedFile $file): array
    {
        try {
            $parser = new Parser();
            $pdf = $parser->parseFile($file->getRealPath());

            $images = array_filter(
                $pdf->getObjectsByType('XObject'),
                fn($object) => $object instanceof Image
            );

            $extractedImages = [];
            $imageCounter = 0;

            foreach ($images as $image) {
                $imageCounter++;

                // ✅ CONVERTIR BINARIO A ARCHIVO TEMPORAL
                $tempImagePath = $this->saveBinaryAsImage($image->getContent(), $imageCounter);
                dd($tempImagePath);

                // Analizar imagen con OpenAI Vision
                $imageDescription = $this->analyzeImageContent($image->getContent());

                $extractedImages[] = [
                    'index' => $imageCounter,
                    'content' => $image->getContent(),
                    'temp_path' => $tempImagePath, // ✅ Ruta del archivo temporal
                    'description' => $imageDescription,
                    'size' => strlen($image->getContent())
                ];

                \Log::info("Imagen {$imageCounter} extraída y guardada en: {$tempImagePath}");
            }

            return $extractedImages;

        } catch (\Exception $e) {
            \Log::error("Error extrayendo imágenes del PDF: " . $e->getMessage());
            return [];
        }
    }

    private function saveBinaryAsImage(string $binaryContent, int $imageCounter): string
    {
        // ✅ GUARDAR EN CARPETA STORAGE PARA FÁCIL ACCESO
        $fileName = 'pdf_image_' . time() . '_' . $imageCounter . '.jpg';
        $storagePath = storage_path('app/temp_images');

        // Crear directorio si no existe
        if (!file_exists($storagePath)) {
            mkdir($storagePath, 0755, true);
        }

        $fullPath = $storagePath . '/' . $fileName;
        file_put_contents($fullPath, $binaryContent);

        \Log::info("Imagen guardada en: {$fullPath}");

        return $fullPath;
    }

    private function analyzeImageContent(string $imageContent): string
    {
        try {
            $base64Image = base64_encode($imageContent);

            $prompt = "
                Analiza esta imagen médica y describe qué tipo de estudio contiene.

                Identifica específicamente si es:
                - EKG/Electrocardiograma
                - Radiografía (especifica la región)
                - Ecografía
                - Tomografía/TAC
                - Resonancia magnética
                - Análisis de laboratorio
                - Diagrama anatómico

                Responde en español, máximo 80 palabras.
            ";

            $response = $this->openAiService->analyzeImageWithVision($prompt, $base64Image);

            return $response ?: 'Imagen médica';

        } catch (\Exception $e) {
            \Log::error("Error analizando imagen con IA: " . $e->getMessage());
            return 'Imagen médica';
        }
    }


    private function findBestImageMatch(array $question, array $images, int $questionIndex): ?array
    {
        $bestMatch = null;
        $bestScore = 0;

        $questionContent = strtolower($question['content'] ?? '');

        foreach ($images as $image) {
            $score = $this->calculateImageQuestionMatch($questionContent, $image, $questionIndex);

            if ($score > $bestScore) {
                $bestScore = $score;
                $bestMatch = $image;
            }
        }

        // Solo devolver si hay una coincidencia razonable
        return $bestScore > 0.4 ? $bestMatch : null;
    }

    private function calculateImageQuestionMatch(string $questionContent, array $image, int $questionIndex): float
    {
        $score = 0;
        $imageDescription = strtolower($image['description'] ?? '');

        // Puntuación por proximidad de índice
        $indexDifference = abs($image['index'] - $questionIndex);
        $proximityScore = max(0, 1 - ($indexDifference / 5));
        $score += $proximityScore * 0.3;

        // Puntuación por coincidencia de términos médicos
        $medicalTerms = [
            'ekg' => ['electrocardiograma', 'ekg', 'ecg', 'ritmo'],
            'radiografía' => ['radiografía', 'rx', 'rayos x', 'tórax'],
            'ecografía' => ['ecografía', 'ultrasonido', 'eco'],
            'tomografía' => ['tomografía', 'tc', 'tac'],
            'resonancia' => ['resonancia', 'rm', 'rmn']
        ];

        foreach ($medicalTerms as $category => $terms) {
            $questionHasTerm = false;
            $imageHasTerm = false;

            foreach ($terms as $term) {
                if (strpos($questionContent, $term) !== false) {
                    $questionHasTerm = true;
                }
                if (strpos($imageDescription, $term) !== false) {
                    $imageHasTerm = true;
                }
            }

            if ($questionHasTerm && $imageHasTerm) {
                $score += 0.7;
            }
        }

        return min(1.0, $score);
    }


    private function uploadImageWithImageService(array $image, int $questionIndex): ?string
    {
        try {
            // Crear archivo temporal
            $tempFileName = 'temp_pdf_image_' . $questionIndex . '_' . time() . '.jpg';
            $tempPath = sys_get_temp_dir() . '/' . $tempFileName;

            // Escribir contenido de imagen al archivo temporal
            file_put_contents($tempPath, $image['content']);

            // Crear UploadedFile desde el archivo temporal
            $uploadedFile = new \Illuminate\Http\UploadedFile(
                $tempPath,
                $tempFileName,
                'image/jpeg',
                null,
                true
            );

            // Generar nombre único para la imagen
            $imageName = 'pdf_question_' . $questionIndex . '_' . time() . '.jpg';

            // Usar tu ImageService para subir
            $path = 'questions/images'; // Ruta donde quieres guardar las imágenes
            $uploadedPath = $this->imageService->upload($uploadedFile, $path, $imageName);

            // Limpiar archivo temporal
            unlink($tempPath);

            // Construir URL completa
            $fullUrl = Storage::disk('new_s3')->url($uploadedPath);

            \Log::info("Imagen subida exitosamente: {$fullUrl}");

            return $fullUrl;

        } catch (\Exception $e) {
            \Log::error("Error subiendo imagen con ImageService: " . $e->getMessage());
            return null;
        }
    }

    private function extractTextFromPdf(UploadedFile $file): string
    {
        try {
            $parser = new Parser();
            $pdf = $parser->parseFile($file->getRealPath());
            $text = $pdf->getText();

            // Limpiar y normalizar texto
            $text = $this->cleanPdfText($text);

            return $text;

        } catch (\Exception $e) {
            throw new \Exception("Error extrayendo texto del PDF: " . $e->getMessage());
        }
    }

    private function cleanPdfText(string $text): string
    {
        // Normalizar espacios y saltos de línea
        $text = preg_replace('/\s+/', ' ', $text);
        $text = preg_replace('/\n+/', "\n", $text);

        // Corregir encoding
        $text = mb_convert_encoding($text, 'UTF-8', 'UTF-8');

        return trim($text);
    }

    private function splitIntoQuestionBlocks(string $text): array
    {
        // Agregar debug específico
        $this->debugSpecificMissingQuestions($text);

        // Intentar con regex mejorado
        $blocks = $this->splitWithRegex($text);

        // Si aún faltan preguntas, usar método alternativo
        if (count($blocks) < 80) {
            \Log::warning("Solo se encontraron " . count($blocks) . " preguntas, intentando método alternativo");
            $alternativeBlocks = $this->extractAllQuestions($text);

            if (count($alternativeBlocks) > count($blocks)) {
                $blocks = $alternativeBlocks;
            }
        }

        return $blocks;
        // ✅ Solo si regex falla, usar OpenAI
//        \Log::warning("Regex falló, intentando con OpenAI");
//        return $this->splitWithOpenAI($text);
    }


    private function extractAllQuestions(string $text): array
    {
        $questions = [];

        // Dividir por "La opción marcada es correcta/incorrecta"
        $parts = preg_split('/(La opción marcada es (?:correcta|incorrecta))/', $text, -1, PREG_SPLIT_DELIM_CAPTURE);

        for ($i = 0; $i < count($parts) - 1; $i += 2) {
            $questionPart = trim($parts[$i]);
            $resultPart = isset($parts[$i + 1]) ? trim($parts[$i + 1]) : '';

            // Buscar número de pregunta al final del bloque
            if (preg_match('/(\d+)\.\s+.*$/s', $questionPart, $matches)) {
                $fullQuestion = $questionPart . ' ' . $resultPart;

                if (strlen($fullQuestion) > 50) {
                    $questions[] = $fullQuestion;
                }
            }
        }

        return $questions;
    }


    private function splitWithRegex(string $text): array
    {
        // Normalizar texto
        $text = preg_replace('/\s+/', ' ', $text);
        $text = trim($text);

        // ✅ Regex mejorado que captura TODAS las preguntas
        preg_match_all('/(\d+)\.\s+([^0-9]*?)(?=\d+\.\s+|$)/s', $text, $matches, PREG_SET_ORDER);

        $questionBlocks = [];

        foreach ($matches as $match) {
            $questionNumber = (int)$match[1];
            $questionContent = trim($match[0]);

            // Solo procesar preguntas del 1 al 86
            if ($questionNumber >= 1 && $questionNumber <= 86) {
                // Validar que sea una pregunta válida
                if ($this->isValidCompleteQuestion($questionContent)) {
                    $questionBlocks[] = $questionContent;
                    \Log::info("Pregunta {$questionNumber} extraída correctamente");
                } else {
                    \Log::warning("Pregunta {$questionNumber} no válida: " . substr($questionContent, 0, 100));
                }
            }
        }

        \Log::info("Total preguntas extraídas: " . count($questionBlocks) . " (esperadas: 86)");

        return $questionBlocks;
    }

    private function isValidCompleteQuestion(string $content): bool
    {
        // Debe tener al menos 30 caracteres (más permisivo)
        if (strlen($content) < 30) {
            return false;
        }

        // Debe contener signo de interrogación O ser una pregunta de relacionar O tener opciones
        $hasQuestion = strpos($content, '?') !== false ||
            strpos($content, '¿') !== false ||
            stripos($content, 'relacione') !== false ||
            stripos($content, 'identifique') !== false ||
            preg_match('/[A-E]\./', $content) ||
            strpos($content, 'La opción marcada') !== false;

        return $hasQuestion;
    }

    private function debugSpecificMissingQuestions(string $text): void
    {
        $expectedQuestions = [1, 5, 9, 15, 21, 27, 33, 39, 45, 51, 57, 65, 73, 79, 85];

        foreach ($expectedQuestions as $questionNum) {
            $pattern = "/\b{$questionNum}\.\s+([^0-9]*?)(?=\d+\.\s+|$)/s";

            if (preg_match($pattern, $text, $match)) {
                $content = trim($match[0]);
                \Log::info("Pregunta {$questionNum} ENCONTRADA: " . substr($content, 0, 100) . "...");

                // Verificar por qué no se está capturando
                if (!$this->isValidCompleteQuestion($content)) {
                    \Log::warning("Pregunta {$questionNum} no pasa validación");
                }
            } else {
                \Log::error("Pregunta {$questionNum} NO ENCONTRADA en el texto");
            }
        }
    }


    private function splitWithOpenAI(string $text): array
    {
        try {
            // ✅ Prompt más simple y directo
            $prompt = "
            Divide este texto en preguntas individuales.

            Cada pregunta empieza con un número seguido de punto (ej: 1., 2., 3.).
            Cada pregunta termina con 'La opción marcada es correcta/incorrecta'.

            Devuelve un array JSON simple:
            [
                \"1. Primera pregunta completa...\",
                \"2. Segunda pregunta completa...\"
            ]

            Solo el array JSON, sin explicaciones.
        ";

            // ✅ Dividir en chunks MÁS PEQUEÑOS (2000 caracteres)
            $textChunks = $this->splitTextIntoChunks($text, 2000);
            $allQuestions = [];

            foreach ($textChunks as $chunkIndex => $chunk) {
                \Log::info("Procesando chunk " . ($chunkIndex + 1) . "/" . count($textChunks));

                try {
                    $response = $this->openAiService->callOpenAI($prompt, $chunk, 'text_splitting');

                    // Parsear respuesta directa como array
                    $questions = json_decode($response, true);

                    if (is_array($questions)) {
                        $allQuestions = array_merge($allQuestions, $questions);
                    }

                    // ✅ Pausa entre llamadas
                    sleep(1);

                } catch (\Exception $e) {
                    \Log::error("Error en chunk {$chunkIndex}: " . $e->getMessage());
                    continue;
                }
            }

            return $allQuestions;

        } catch (\Exception $e) {
            \Log::error("Error dividiendo texto con OpenAI: " . $e->getMessage());
            return [];
        }
    }

    private function splitTextIntoChunks(string $text, int $maxChunkSize): array
    {
        $chunks = [];
        $currentChunk = '';
        $lines = explode("\n", $text);

        foreach ($lines as $line) {
            // Si agregar esta línea excede el límite, guardar chunk actual
            if (strlen($currentChunk . $line) > $maxChunkSize && !empty($currentChunk)) {
                $chunks[] = trim($currentChunk);
                $currentChunk = $line . "\n";
            } else {
                $currentChunk .= $line . "\n";
            }
        }

        // Agregar el último chunk
        if (!empty(trim($currentChunk))) {
            $chunks[] = trim($currentChunk);
        }

        return $chunks;
    }


    private function extractQuestionFromBlock(array $block): ?array
    {
        $prompt = "
            Eres un experto en extracción de preguntas médicas de texto. Tu tarea es extraer y estructurar preguntas de examen médico.

            INSTRUCCIONES:
            1. Extrae la pregunta principal del texto
            2. Identifica las n opciones de respuesta (A, B, C, D ... o 1, 2, 3, 4 ...)
            3. Determina cuál es la respuesta correcta
            4. Extrae la explicación si existe
            5. Identifica el tema/especialidad médica

            FORMATO DE RESPUESTA:
            Devuelve ÚNICAMENTE un JSON con esta estructura:
            {
                \"content\": \"Pregunta principal completa...\",
                \"answer1\": \"Opción A\",
                \"is_correct1\": \"true/false\",
                \"answer2\": \"Opción B\",
                \"is_correct2\": \"true/false\",
                \"answer3\": \"Opción C\",
                \"is_correct3\": \"true/false\",
                \"answer4\": \"Opción D\",
                \"is_correct4\": \"true/false\",
                \"answerN\": \"Opción ...\",
                \"is_correctN\": \"true/false\",
                \"explicacion\": \"Explicación si existe o null\",
                \"tipos\": \"Especialidad médica\",
                \"universidades\": \"1,2,3,4\",
                \"iframe\": null,
                \"url\": null
            }

            TEXTO A PROCESAR:
        ";

        try {
            $response = $this->openAiService->callOpenAI($prompt, $block, 'pdf_extraction');
            $response = $this->parseQuestionResponse($response);
            $response = $this->getRealCategoryOfQuestion($response);
            $response = $this->setUniversities($response);
            dd($response);
            return $response;

        } catch (\Exception $e) {
            \Log::error("Error extrayendo pregunta: " . $e->getMessage());
            return null;
        }
    }

    private function generateExplanation(array $question): array
    {
        $prompt = "
            Eres un profesor de medicina experto. Tu tarea es generar una explicación clara y educativa para esta pregunta médica.

            INSTRUCCIONES:
            1. Explica por qué la respuesta correcta es válida
            2. Menciona por qué las otras opciones son incorrectas
            3. Proporciona contexto médico relevante
            4. Usa terminología médica apropiada pero comprensible

            PREGUNTA:
            {$question['content']}

            RESPUESTA CORRECTA:
            " . $this->getCorrectAnswer($question) . "

            FORMATO:
            Devuelve ÚNICAMENTE la explicación en texto plano, sin formato JSON.
        ";

        try {
            $explanation = $this->openAiService->callOpenAI($prompt, '', 'explanation_generation');
            $question['explicacion'] = trim($explanation);
            return $question;

        } catch (\Exception $e) {
            \Log::error("Error generando explicación: " . $e->getMessage());
            return $question;
        }
    }

    private function getCorrectAnswer(array $question): string
    {
        for ($i = 1; $i <= 4; $i++) {
            if (($question["is_correct{$i}"] ?? '') === 'true') {
                return $question["answer{$i}"] ?? '';
            }
        }
        return '';
    }

    private function parseQuestionResponse(string $response): ?array
    {
        try {
            // Limpiar respuesta
            $response = trim($response);
            $response = preg_replace('/^```(?:json)?\s*/', '', $response);
            $response = preg_replace('/\s*```$/', '', $response);

            $decoded = json_decode($response, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception("Error JSON: " . json_last_error_msg());
            }

            // Validar estructura básica
            if (!isset($decoded['content']) || empty($decoded['content'])) {
                return null;
            }

            return $decoded;

        } catch (\Exception $e) {
            \Log::error("Error parseando respuesta: " . $e->getMessage());
            return null;
        }
    }

    private function saveQuestionToDatabase(array $question): void
    {
        try {
            DB::beginTransaction();
            $pregunta = Question::create([
                'user_id' => auth()->id(),
                'content' => $question['content'],
                'question_type' => 'multiple_choice',
                'approved' => true,
                'explanation' => $question['explicacion'],
                'fail_weight' => 0,
            ]);
            $pregunta->categories()->sync([$question['tipos']]);
            $pregunta->universidades()->sync(explode(',', $question['universidades']));
            $pregunta = $pregunta->load(
                'options',
                'categories',
                'universidades',
                'user'
            );
            $this->extractAnswersArray($question, $pregunta);
            dd($pregunta);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            dd($e->getMessage());
            \Log::error("Error guardando pregunta en BD: " . $e->getMessage());
            throw $e;
        }
    }

    private function extractAnswersArray(array $question, Question $pregunta): array
    {
        $answers = [];
        $i = 1;
        while (isset($question["answer{$i}"])) {
            if (!empty(trim($question["answer{$i}"]))) {
                $answers[] = [
                    "question_id" => $pregunta->id,
                    'content' => trim($question["answer{$i}"]),
                    'is_correct' => ($question["is_correct{$i}"] ?? 'false') === 'true',
                    'points' => 1,
                ];
            }
            $i++;
        }

        return $answers;
    }

    private function formatQuestionBlocks(array $questionBlocks): array
    {
        $result = [];
        foreach ($questionBlocks as $index => $block) {
            $block = preg_replace('/\s+/', ' ', $block);
            $block = trim($block);
            $block = preg_replace('/^.*?Solucionario\s+/', '', $block);

            $pregunta = $this->extractQuestionText($block);
            $respuestas = $this->extractAnswerOptions($block);

            $result[] = [
                'pregunta' => $pregunta,
                'respuesta' => $respuestas
            ];
        }
        return $result;
    }

    private function extractQuestionText(string $block): string
    {
        // ✅ BUSCAR EL PATRÓN DE PREGUNTA EN CUALQUIER PARTE DEL BLOQUE
        $patterns = [
            // Patrón 1: Número + punto + texto + A.
            '/(\d+)\.\s+(.*?)(?=\s*A\.)/s',
            // Patrón 2: Número + punto + texto + cualquier opción
            '/(\d+)\.\s+(.*?)(?=\s*[A-E]\.)/s',
            // Patrón 3: Número + punto + texto + "La opción marcada"
            '/(\d+)\.\s+(.*?)(?=\s*La opción marcada)/s'
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $block, $match)) {
                $questionText = trim($match[2]);

                // Validar que el texto extraído tenga sentido
                if (strlen($questionText) > 20 && strpos($questionText, '?') !== false) {
                    return $questionText;
                }
            }
        }

        // ✅ FALLBACK: Extraer manualmente
        return $this->extractQuestionManually($block);
    }

    private function extractQuestionManually(string $block): string
    {
        // Buscar el número de pregunta
        if (preg_match('/(\d+)\.\s+/', $block, $match, PREG_OFFSET_CAPTURE)) {
            $startPos = $match[0][1] + strlen($match[0][0]);

            // Buscar donde empiezan las opciones
            if (preg_match('/\s*[A-E]\./', $block, $optionMatch, PREG_OFFSET_CAPTURE, $startPos)) {
                $endPos = $optionMatch[0][1];
                $questionText = substr($block, $startPos, $endPos - $startPos);
                return trim($questionText);
            }
        }

        return '';
    }


    private function extractAnswerOptions(string $block): array
    {
        // Buscar opciones A-E
        preg_match_all('/([A-E])\.([^A-E]*?)(?=[A-E]\.|La opción marcada|$)/s', $block, $matches, PREG_SET_ORDER);

        $respuestas = [];
        foreach ($matches as $match) {
            $textoRespuesta = trim($match[2]);
            if (!empty($textoRespuesta)) {
                $respuestas[] = $textoRespuesta;
            }
        }

        return $respuestas;
    }

    private function getRealCategoryOfQuestion(?array $response)
    {
        $tipo = $response['tipos'];
        $tipos = Category::query()->get()
            ->pluck('name', 'id')
            ->toJson();

        $content = [
            'category_sugested' => $tipo,
            'all_categories' => $tipos
        ];
        $content = json_encode($content, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        $promt = "
            Eres un experto en clasificación de preguntas médicas. Tu tarea es identificar la categoría médica de esta pregunta.

            +
            INSTRUCCIONES:
            1. $tipo tiene que ser una categoría válida de las siguientes: $tipos
            que averigue si es valida alguna categoria si lo es responde solo con el nombre de la categoría, y su clave
            ";
        $return = $this->openAiService->callOpenAI($promt, $content, 'category_identification');
        $return = json_decode($return, true);
        $response['tipos'] = array_keys($return)[0];
        return $response;
    }

    private function setUniversities(?array $response)
    {
        $universidades = Universidad::query()->pluck('id')->toArray();
        $universidades = implode(',', $universidades);
        $response['universidades'] = $universidades;
        return $response;
    }


}



