<?php

namespace App\Services\Api\OpenAI;

use OpenAI\Laravel\Facades\OpenAI;

class Questions
{
    public function processQuestion(array $question): array
    {
        try {
            // FASE 1: Análisis de desplazamiento
            $displacementAnalysis = $this->analyzeDisplacement($question);

            if ($displacementAnalysis['is_displaced']) {
                \Log::info("Pregunta desplazada detectada", $displacementAnalysis);
                $question = $this->fixDisplacement($question);
            }

            // FASE 2: Validación de completitud
            $completenessAnalysis = $this->analyzeCompleteness($question);

            if (!$completenessAnalysis['is_complete']) {
                \Log::info("Pregunta incompleta detectada", $completenessAnalysis);
            }
            dd($question);
            // FASE 3: Validación final
            $finalValidation = $this->validateFinalQuestion($question);

            if (!$finalValidation['is_valid']) {
                \Log::warning("Pregunta sigue siendo inválida después de correcciones");
                throw new \Exception("No se pudo corregir la pregunta: " . implode(', ', $finalValidation['errors']));
            }

            return $question;

        } catch (\Exception $e) {
            \Log::error("Error procesando pregunta: " . $e->getMessage());
            throw $e;
        }
    }

    private function analyzeDisplacement(array $question): array
    {
        $issues = [];
        $severity = 0;

        // 1. Analizar si el contenido parece una respuesta
        $content = trim($question['content'] ?? '');
        if (strlen($content) < 30 && !empty($content)) {
            $issues[] = 'content_too_short_for_question';
            $severity += 3;
        }

        // 2. Analizar si alguna respuesta parece una pregunta
        for ($i = 1; $i <= 4; $i++) {
            $answer = $question["answer{$i}"] ?? '';
            if (strlen($answer) > 100 ||
                strpos($answer, '¿') !== false ||
                strpos($answer, '?') !== false ||
                str_word_count($answer) > 15) {
                $issues[] = "answer{$i}_seems_like_question";
                $severity += 2;
            }
        }

        // 3. Analizar si la explicación parece una pregunta
        $explanation = $question['explicacion'] ?? '';
        if (strlen($explanation) > 50 &&
            (strpos($explanation, '¿') !== false ||
                str_word_count($explanation) > 20)) {
            $issues[] = 'explanation_seems_like_question';
            $severity += 2;
        }

        // 4. Analizar patrón de campos vacíos
        if (empty($question['answer1']) && !empty($question['answer2'])) {
            $issues[] = 'left_shift_pattern';
            $severity += 2;
        }

        return [
            'is_displaced' => $severity >= 3,
            'issues' => $issues,
            'severity' => $severity,
            'confidence' => $severity >= 5 ? 'high' : ($severity >= 3 ? 'medium' : 'low')
        ];
    }

    private function analyzeCompleteness(array $question): array
    {
        $missing = [];
        $warnings = [];

        // Campos obligatorios
        $requiredFields = [
            'content' => 'Contenido de la pregunta',
            'answer1' => 'Primera opción de respuesta',
            'answer2' => 'Segunda opción de respuesta',
            'answer3' => 'Tercera opción de respuesta',
            'answer4' => 'Cuarta opción de respuesta'
        ];

        foreach ($requiredFields as $field => $description) {
            if (empty(trim($question[$field] ?? ''))) {
                $missing[] = $field;
            }
        }

        // Validar respuestas correctas
        $correctAnswers = 0;
        for ($i = 1; $i <= 4; $i++) {
            if (($question["is_correct{$i}"] ?? '') === 'true') {
                $correctAnswers++;
            }
        }

        if ($correctAnswers === 0) {
            $missing[] = 'correct_answer_marking';
        } elseif ($correctAnswers > 1) {
            $warnings[] = 'multiple_correct_answers';
        }

        // Campos recomendados
        if (empty(trim($question['explicacion'] ?? ''))) {
            $warnings[] = 'missing_explanation';
        }

        return [
            'is_complete' => empty($missing),
            'missing_fields' => $missing,
            'warnings' => $warnings,
            'completeness_score' => (8 - count($missing)) / 8 * 100
        ];
    }

    private function fixDisplacement(array $question): array
    {
        $prompt = "
        Eres un experto en corrección de datos CSV médicos con columnas desplazadas.

        TAREA: Analizar y corregir el desplazamiento de campos en esta pregunta médica.

        REGLAS DE CORRECCIÓN:
        1. 'content' debe contener la pregunta médica completa (no una respuesta corta)
        2. 'answer1-n' deben contener opciones de respuesta cortas (no preguntas largas)
        3. 'explicacion' debe explicar la respuesta correcta
        4. Solo UNA respuesta debe tener is_correct = 'true'

        INSTRUCCIONES:
        - Si el 'content' es muy corto y 'explicacion' es largo, intercámbialos
        - Si 'answer1' está vacío pero 'answer2-n' tienen datos, desplaza hacia la izquierda
        - Si alguna respuesta parece una pregunta, muévela al campo correcto
        - Mantén la coherencia médica

        FORMATO DE RESPUESTA:
        Devuelve ÚNICAMENTE el JSON corregido, sin comentarios adicionales.
    ";

        // CONVERTIR EL ARRAY A JSON STRING
        $questionJson = json_encode($question, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        if ($questionJson === false) {
            throw new \Exception("Error convirtiendo pregunta a JSON: " . json_last_error_msg());
        }

        // PASAR EL STRING, NO EL ARRAY
        $response = $this->callOpenAI($prompt, $questionJson, 'displacement_correction');

        return $this->parseOpenAIResponse($response);
    }


    private function completeQuestion(array $question): array
    {
        $prompt = "
        Eres un experto en preguntas médicas. Tu tarea es completar los campos faltantes.

        TAREA: Completar los campos faltantes en esta pregunta médica.

        REGLAS DE COMPLETADO:
        1. Si falta 'content', créalo basándote en las respuestas disponibles
        2. Si faltan respuestas, genera opciones médicamente plausibles
        3. Si falta 'explicacion', crea una explicación médica apropiada
        4. Asegúrate de que solo UNA respuesta sea correcta
        5. Mantén coherencia médica en todo momento

        CONTEXTO MÉDICO:
        - Las preguntas deben ser apropiadas para estudiantes de medicina
        - Las opciones deben ser plausibles pero claramente diferenciables
        - La explicación debe justificar por qué la respuesta correcta es válida

        FORMATO DE RESPUESTA:
        Devuelve ÚNICAMENTE el JSON completo, sin comentarios adicionales.
    ";

        // ✅ CONVERTIR ARRAY A JSON STRING
        $questionJson = json_encode($question, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        if ($questionJson === false) {
            throw new \Exception("Error convirtiendo pregunta a JSON: " . json_last_error_msg());
        }

        // ✅ PASAR STRING, NO ARRAY
        $response = $this->callOpenAI($prompt, $questionJson, 'completion');

        return $this->parseOpenAIResponse($response);
    }


    public function callOpenAI(string $prompt, $content, string $operation): string
    {
        if (is_array($content)) {
            $content = json_encode($content, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

            if ($content === false) {
                throw new \Exception("Error convirtiendo array a JSON: " . json_last_error_msg());
            }
        }

        try {
            $response = OpenAI::chat()->create([
                'model' => 'gpt-4o-mini', // ✅ MODELO CORRECTO
                'messages' => [
                    ['role' => 'system', 'content' => $prompt],
                    ['role' => 'user', 'content' => $content],
                ],
                'temperature' => 0.2,
                'max_tokens' => 4000, // ✅ Reducir tokens para evitar timeout
            ]);

            $responseContent = $response['choices'][0]['message']['content'] ?? '';

            if (empty($responseContent)) {
                throw new \Exception("Respuesta vacía de OpenAI en operación: {$operation}");
            }

            \Log::info("OpenAI {$operation} completada exitosamente");

            return $responseContent;

        } catch (\Exception $e) {
            \Log::error("Error en OpenAI {$operation}: " . $e->getMessage());
            throw new \Exception("Fallo en {$operation}: " . $e->getMessage());
        }
    }


    private function validateFinalQuestion(array $question): array
    {
        $errors = [];

        // Validar contenido
        $content = trim($question['content'] ?? '');
        if (empty($content)) {
            $errors[] = "Contenido vacío";
        } elseif (strlen($content) < 20) {
            $errors[] = "Contenido muy corto";
        }

        // Validar respuestas
        $validAnswers = 0;
        for ($i = 1; $i <= 4; $i++) {
            $answer = trim($question["answer{$i}"] ?? '');
            if (!empty($answer)) {
                $validAnswers++;
                if (strlen($answer) > 200) {
                    $errors[] = "Respuesta {$i} muy larga";
                }
            }
        }

        if ($validAnswers < 4) {
            $errors[] = "Faltan respuestas (solo {$validAnswers} de 4)";
        }

        // Validar respuesta correcta
        $correctAnswers = 0;
        for ($i = 1; $i <= 4; $i++) {
            if (($question["is_correct{$i}"] ?? '') === 'true') {
                $correctAnswers++;
            }
        }

        if ($correctAnswers === 0) {
            $errors[] = "No hay respuesta marcada como correcta";
        } elseif ($correctAnswers > 1) {
            $errors[] = "Hay múltiples respuestas correctas";
        }

        // Validar explicación
        if (empty(trim($question['explicacion'] ?? ''))) {
            $errors[] = "Falta explicación";
        }

        return [
            'is_valid' => empty($errors),
            'errors' => $errors,
            'quality_score' => max(0, (10 - count($errors)) / 10 * 100)
        ];
    }

    private function parseOpenAIResponse(string $response): ?array
    {
        // Limpiar respuesta
        $response = trim($response);
        $response = preg_replace('/^```(?:json)?\s*/', '', $response);
        $response = preg_replace('/\s*```$/', '', $response);

        // Decodificar JSON
        $decoded = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            \Log::error("Error parsing JSON de OpenAI", [
                'error' => json_last_error_msg(),
                'response_preview' => substr($response, 0, 200)
            ]);
            return null;
        }

        return $decoded;
    }

    private function cleanQuestionData(array $question): array
    {
        $cleaned = [];

        foreach ($question as $key => $value) {
            if (is_string($value)) {
                $cleaned[$key] = mb_convert_encoding($value, 'UTF-8', 'UTF-8');
                $cleaned[$key] = preg_replace('/[\x00-\x1F\x7F]/', '', $cleaned[$key]);
                $cleaned[$key] = trim($cleaned[$key]);

                if ($cleaned[$key] === '' && in_array($key, ['iframe', 'url', 'explicacion'])) {
                    $cleaned[$key] = null;
                }
            } else {
                $cleaned[$key] = $value;
            }
        }

        return $cleaned;
    }

    public function analyzeImageWithVision(string $prompt, string $base64Image)
    {
        try {
            $response = OpenAI::images()->create([
                'model' => 'gpt-4-vision-preview',
                'messages' => [
                    ['role' => 'system', 'content' => $prompt],
                    ['role' => 'user', 'content' => $base64Image],
                ],
                'temperature' => 0.2,
                'max_tokens' => 3000,
            ]);

            $responseContent = $response['choices'][0]['message']['content'] ?? '';

            if (empty($responseContent)) {
                throw new \Exception("Respuesta vacía de OpenAI en análisis de imagen");
            }

            \Log::info("Análisis de imagen completado exitosamente");

            return $responseContent;

        } catch (\Exception $e) {
            \Log::error("Error en análisis de imagen: " . $e->getMessage());
            throw new \Exception("Fallo en análisis de imagen: " . $e->getMessage());
        }
    }
}
