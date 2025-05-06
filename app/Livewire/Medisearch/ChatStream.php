<?php

namespace App\Livewire\Medisearch;

use Livewire\Component;
use App\Services\Chat\ChatService;

class ChatStream extends Component
{
    public $prompt = '';
    public $messages = [];
    public $streamedResponse = '';

    protected $chatService;

    public function boot(ChatService $chatService)
    {
        $this->chatService = $chatService;
    }

    public function ask()
    {
        $this->messages[] = ['role' => 'user', 'content' => $this->prompt];

        $stream = $this->chatService->askToOpenAI($this->messages, $this->prompt);
//        $stream = $this->chatService->askToAssistant(
//        'asst_xsAGQrVQA0TSxyGZOEZXSZDr',
//        $this->prompt
//    );
//        $stream = $this->chatService->askToAssistantStream(
//            'asst_xsAGQrVQA0TSxyGZOEZXSZDr',
//            $this->prompt
//        );

        foreach ($stream as $response) {
            $chunk = $response->choices[0]->delta->content ?? '';
            $this->streamedResponse .= $chunk;

            $this->stream(
                to: 'streaming_response',
                content: $chunk
            );
        }

        $this->messages[] = [
            'role' => 'assistant',
            'content' => $this->streamedResponse
        ];

        $this->streamedResponse = '';
        $this->prompt = '';
    }

    public function render()
    {
        return view('livewire.medisearch.chat-stream');
    }
}
