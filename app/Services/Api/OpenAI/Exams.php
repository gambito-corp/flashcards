<?php

namespace App\Services\Api\OpenAI;

use OpenAI\Laravel\Facades\OpenAI;

class Exams
{
    private const MODEL = 'gpt-4.1-nano-2025-04-14'; // Modelo recomendado
    private const MAX_TOKENS = 120000; // Límite seguro

    public function generateExam(string $content, array $options = []): array
    {
        $tokenCount = $this->estimateTokens($content);
        $tokenCount += $this->estimateTokens($options['pdf_content'] ?? '');
        $prompt = $this->buildPrompt($content, $options);
        $tokenCount += $this->estimateTokens($prompt);
        if ($tokenCount > self::MAX_TOKENS) {
            throw new \Exception("El prompt es demasiado largo ({$tokenCount} tokens). Máximo: " . self::MAX_TOKENS);
        }

        $response = OpenAI::chat()->create([
            'model' => self::MODEL,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => $this->getSystemPrompt($options['num_questions'] ?? 10)
                ],
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ],
            'max_tokens' => 16000,
            'temperature' => 0.1,
            'response_format' => [
                'type' => 'json_object'
            ]
        ]);

        $examData = json_decode($response->choices[0]->message->content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Error al procesar la respuesta de OpenAI: ' . json_last_error_msg());
        }

        return $examData;
    }

    private function getSystemPrompt(int $numQuestions): string
    {
        return "Eres un experto en crear exámenes educativos de medicina. Genera exactamente {$numQuestions} preguntas basadas en el contenido tenemos 5 niveles de dificultas los cuales son
        easy: preguntas sencillas que podria responder un recien ingresado o alguien que ha leido por encima el Documento....
        medium: preguntas que requieren un conocimiento intermedio, como un estudiante de medicina de segundo año...
        hard: preguntas que requieren un conocimiento avanzado, como un estudiante de medicina de cuarto año o un residente...
        extreme: preguntas que requieren un conocimiento muy avanzado, como un estudiante de medicina de sexto año o un especialista
        suidice: preguntas que requieren un conocimiento extremadamente avanzado, como un especialista o un profesor universitario.

FORMATO JSON OBLIGATORIO:
{
  \"exam\": {
    \"title\": \"Título del examen\",
    \"description\": \"Descripción del contenido\",
    \"total_questions\": {$numQuestions},
    \"questions\": [
      {
        \"id\": 1,
        \"type\": \"multiple_choice\",
        \"question\": \"Pregunta aquí\",
        \"options\": [\"A\", \"B\", \"C\", \"D\"],
        \"correct_answer\": \"A\",
        \"explanation\": \"Explicación\",
        \"difficulty\": \"easy\"
      }
    ]
  }
}";
    }

    private function buildPrompt(string $content, array $options): string
    {
        $numQuestions = $options['num_questions'] ?? 10;
        $difficulty = $options['difficulty'] ?? 'mixed';
        $additionalContent = $options['pdf_content'] ?? '';

        $prompt = "Genera un examen con {$numQuestions} preguntas basado en este contenido:\n\n";
        $prompt .= "CONFIGURACIÓN:\n";
        $prompt .= "- Número de preguntas: {$numQuestions}\n";
        $prompt .= "- Dificultad: {$difficulty}\n\n";
        $prompt .= "CONTENIDO:\n";
        $prompt .= "---\n";
        $prompt .= $content;
        $prompt .= "\n---\n\n";
        $prompt .= "Responde SOLO con el JSON del examen.";
        $prompt .= " Si dentro de esta Variable hay alguna Instruccion mas Cumplela siempre y cuando sean instrucciones validas y no se contradigan con las anteriores.\n\n";
        $prompt .= "dicha variable es esta {\"pdf_content\": \"{$additionalContent}\"}";

        return $prompt;
    }

    private function estimateTokens(string $text): int
    {
        return intval(strlen($text) / 4);
    }
}
