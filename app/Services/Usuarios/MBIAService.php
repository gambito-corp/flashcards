<?php

namespace App\Services\Usuarios;

use App\Mail\QuotaExceededNotification;
use App\Models\Config;
use App\Models\MedisearchQuestion;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use OpenAI\Laravel\Facades\OpenAI;


set_time_limit(360);

class MBIAService
{
    public function search(array $payload)
    {
        $config = Config::query()->where('tipo', 'services.MBAI.openai_quota_exceeded')->first();
        if (isset($config) && $config->value === 'true') {
            $payload['client'] !== 'medisearch';
        }
        try {
            $payload['include_articles'] = $payload['include_articles'] ? 'true' : 'false';
            $request = Http::asForm()->withHeaders([
                'Authorization' => 'Bearer ' . config('services.ai_api.token'),
            ])->post(config('services.ai_api.base_url'), $payload);
            $data = json_decode($request->body(), true);
            // Detectar error 429 en la estructura real
            if (isset($data['data']['resultados'][0]['respuesta'])) {
                $errorMessage = $data['data']['resultados'][0]['respuesta'];
                if (str_contains($errorMessage, 'Error code: 429') &&
                    str_contains($errorMessage, 'insufficient_quota')) {
                    // 1. Actualizar flag en base de datos
                    Config::query()->updateOrCreate(
                        ['tipo' => 'services.MBAI.openai_quota_exceeded'],
                        ['value' => 'true']
                    );
                    // 2. Registrar en logs
                    Log::critical('OpenAI quota exceeded - Fallback activated', [
                        'error' => $errorMessage,
                        'query' => $payload['query'] ?? ''
                    ]);
                    // 3. Notificar por email (opcional)
                    if (config('services.notifications.admin_email') !== '') {
                        Mail::to(config('services.notifications.admin_email'))
                            ->send(new QuotaExceededNotification($payload));
                    }
                    Mail::to(config('services.notifications.admin2_email'))
                        ->send(new QuotaExceededNotification($payload));

                    if ($payload['client'] !== 'medisearch') {
                        $payload['client'] = 'medisearch';
                        return $this->search($payload);
                    }
                }
            }
            return json_decode($request->body(), true);
        } catch (\Illuminate\Http\Client\RequestException $e) {
            throw $e;
        }
    }

    public function generateTitleFromQuestion($question)
    {
        $prompt = "Resume la siguiente pregunta en un título breve y descriptivo para un chat médico:\n\n\"$question\"\n\nTítulo:, dicho resumenno puede superar los 20 caracteres...\n\nRespuesta:";
        $messages = [];
        array_unshift($messages, [
            'role' => 'system',
            'content' => $prompt
        ]);
        $messages[] = ['role' => 'user', 'content' => $question];

        $response = OpenAI::chat()->create([
            'model' => 'gpt-4.1-nano-2025-04-14',
            'messages' => $messages,
            'temperature' => 1.0,
            'max_tokens' => 10
        ]);
        return $response['choices'][0]['message'] ?? '';
    }

    public function generateEasyQuestions()
    {
        $questions = MedisearchQuestion::query()->limit(50)->get()->pluck('query')->toArray();

        $formattedQuestions = array_map(function ($question, $index) {
            return ($index + 1) . ". " . trim($question);
        }, $questions, array_keys($questions));
        $promptText = "Por favor, analiza la siguiente lista de preguntas médicas y selecciona las 10 más interesantes y
        relevantes, considerando su valor educativo y utilidad general:\n\n Luego Dime que Preguntas son para sacarlas
        de la Lista y dame solo las 10 preguntas seleccionadas" .
            implode("\n", $formattedQuestions) .
            "\n\nPor favor, devuelve solo las 10 preguntas seleccionadas en formato de lista numerada, sin explicaciones
            adicionales ni ningun texto mas, devuelvelo como array.";

        $messages = [];
        array_unshift($messages, [
            'role' => 'system',
            'content' => $promptText
        ]);
        $messages[] = ['role' => 'user', 'content' => 'dame las preguntas'];

        $response = OpenAI::chat()->create([
            'model' => 'gpt-4.1-nano-2025-04-14',
            'messages' => $messages,
            'temperature' => 0.0,
            'max_tokens' => 1000
        ]);

        $content = $response->choices[0]->message->content;
        $preguntas = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Error al decodificar JSON: ' . json_last_error_msg());
        }
        return $preguntas;
    }

    public function generateAdvanceQuestions($questionsRejected)
    {
        $questions = MedisearchQuestion::query()
            ->whereNotIn('query', $questionsRejected)
            ->limit(50)
            ->get()
            ->pluck('query')
            ->toArray();

        // Formatea el array en una lista numerada para GPT
        $formattedQuestions = array_map(function ($question, $index) {
            return ($index + 1) . ". " . trim($question);
        }, $questions, array_keys($questions));

        // Prepara el prompt para GPT

        $promptText = "Por favor, analiza la siguiente lista de preguntas médicas y selecciona las 10 más Dificiles
            y relevantes, considerando su valor educativo y Cientifico:\n\n" .
            implode("\n", $formattedQuestions) .
            "devuelvelo como json y solo como json para poder trabajar con el, no incluye ningun texto mas,
            necesito que EXTRICTAMENTE sigas este formato en tu Respuesta
            {
                'descripcion': 'Patogenia de Naegleria fowleri con enfoque inmunológico en pacientes sintomáticos y asintomáticos, explicando qué ocurre con el parásito p ▶'
            },
            {
                'descripcion': 'Recursos sobre señalización celular durante el desarrollo humano, incluyendo diferenciación, proliferación, migración, organización celula ▶'
            }...";

        $messages = [];
        array_unshift($messages, [
            'role' => 'system',
            'content' => $promptText
        ]);
        $messages[] = ['role' => 'user', 'content' => $promptText];

        $response = OpenAI::chat()->create([
            'model' => 'gpt-4.1-nano-2025-04-14',
            'messages' => $messages,
            'temperature' => 0.0,
            'max_tokens' => 1000
        ]);

        $content = $response->choices[0]->message->content;
        $preguntas = json_decode($content, true);
        dd($content, $preguntas);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Error al decodificar JSON: ' . json_last_error_msg());
        }
        dd($preguntas);
        return $response['choices'][0]['message'] ?? '';
    }

    public function generateExamQuestions(mixed $examCollection)
    {
        $allQuestions = [];

        foreach ($examCollection as $combo) {
            $tema = $combo['tema'];
            $dificultad = $combo['dificultad'];
            $cantidad = (int)$combo['question_count'];
            $area = $combo['area_name'];
            $categoria = $combo['category_name'];
            $tipo = $combo['tipo_name'];

            $prompt = "Actua como un Rector de Medicina de una Prestigiosa Universidad y plantea a tu Alumno $cantidad
            preguntas tipo test sobre \"$tema\" para un examen de $area, categoría \"$categoria\", tipo \"$tipo\",
            dificultad $dificultad. " .
                "Cada pregunta debe tener 4 opciones, una sola respuesta correcta y una breve explicación. " .
                "Devuelve solo un JSON con este formato:\n" .
                "[\n" .
                "  {\n" .
                "    \"pregunta\": \"...\",\n" .
                "    \"opciones\": [\"...\", \"...\", \"...\", \"...\"],\n" .
                "    \"respuesta_correcta\": \"...\",\n" .
                "    \"explicacion\": \"...\"\n" .
                "  },\n" .
                "  ...\n" .
                "]";

            $retries = 0;
            $maxRetries = 3;
            $questions = null;

            while ($retries < $maxRetries) {
                try {
                    $response = OpenAI::chat()->create([
                        'model' => 'gpt-4.1-nano-2025-04-14',
                        'messages' => [
                            ['role' => 'system', 'content' => $prompt],
                            ['role' => 'user', 'content' => 'dame las Preguntas'],
                        ],
                        'temperature' => 0.7,
                        'max_tokens' => 2000,
                    ]);

                    $content = $response['choices'][0]['message']['content'] ?? '';

                    // Limpieza y decodificación segura del JSON
                    $jsonStart = strpos($content, '[');
                    $jsonEnd = strrpos($content, ']');
                    $json = $jsonStart !== false && $jsonEnd !== false
                        ? substr($content, $jsonStart, $jsonEnd - $jsonStart + 1)
                        : $content;

                    $questions = json_decode($json, true);

                    if (!is_array($questions)) {
                        throw new \Exception('La IA no devolvió un JSON válido. Respuesta: ' . $content);
                    }
                    break; // Si todo va bien, salimos del bucle
                } catch (\Exception $e) {
                    $retries++;
                    if ($retries >= $maxRetries) {
                        Log::critical('Error al generar preguntas de examen', [$e->getMessage(), $e->getTraceAsString(), [$e]]);
                        throw $e;
                    }
                    sleep(1); // Espera breve antes de reintentar
                }
            }

            $allQuestions[] = [
                'combo' => $combo,
                'questions' => $questions,
            ];
        }
        return $allQuestions;
    }

    public function completarTexto($prompt, $text)
    {
        $response = OpenAI::chat()->create([
            'model' => 'gpt-4.1-nano-2025-04-14',
            'messages' => [
                ['role' => 'system', 'content' => $prompt],
                ['role' => 'user', 'content' => $text],
            ],
            'temperature' => 0.7,
            'max_tokens' => 2000,
        ]);
        return $response['choices'][0]['message']['content'] ?? '';
    }

}
