<?php

namespace App\Services\Api\OpenAI;

use OpenAI\Laravel\Facades\OpenAI;

class Flashcard
{
    public function generate($userId, $type, $prompt, $currentText = 'a')
    {

        $response = OpenAI::chat()->create([
            'model' => 'gpt-4.1-nano-2025-04-14',
            'messages' => [
                ['role' => 'system', 'content' => $prompt],
                ['role' => 'user', 'content' => $currentText],
            ],
            'temperature' => 0.7,
            'max_tokens' => 1000,
        ]);
        return $response['choices'][0]['message']['content'] ?? '';
    }
}
