<?php

namespace App\Services\Api\OpenAI;

use OpenAI\Laravel\Facades\OpenAI;

class Resume
{
    private const MODEL = 'gpt-4.1-nano-2025-04-14'; // Modelo recomendado
    private const MAX_TOKENS = 120000; // Límite seguro

    public function generateResume(string $text): string
    {
        try {
            $response = OpenAI::chat()->create([
                'model' => self::MODEL,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => $this->getSystemPrompt(),
                    ],
                    [
                        'role' => 'user',
                        'content' => $text,
                    ],
                ],
            ]);

            return $response['choices'][0]['message']['content'] ?? '';
        } catch (\Exception $e) {
            // Manejo de errores, log o rethrow según sea necesario
            throw new \RuntimeException('Error generating resume: ' . $e->getMessage());
        }
    }

    private function getSystemPrompt()
    {
        return "Eres un experto en resumir documentos médicos. Tu tarea es crear un resumen
        conciso y claro del texto proporcionado. El resumen debe ser breve, pero contener
        toda la información clave del documento original. Asegúrate de que el resumen sea
        fácil de entender y mantenga la coherencia del contenido original. todo resumen lo
        traduciras al Español y empezas con la palabra 'Este Documento Trata de....'.";
    }
}
