<?php

namespace App\Livewire\Medisearch;

use Livewire\Component;
use Illuminate\Support\Facades\Http;

class Index extends Component
{
    public $newMessage = '';
    public $messages = [];

    public function sendMessage()
    {
        // Agrega el mensaje del usuario a la conversación
        $this->messages[] = [
            'from' => 'user',
            'text' => $this->newMessage,
        ];

        // Llama a la API del servicio Python enviando el mensaje en el query param
        $endpoint = 'https://appbanqueo.medbystudents.com/api/python/search';
        $response = Http::get($endpoint, [
            'q' => $this->newMessage,
        ]);

        $data = $response->json();

        // Procesa la respuesta, asumiendo la estructura que recibiste
        if (isset($data['data']['resultados'])) {
            foreach ($data['data']['resultados'] as $item) {
                if ($item['tipo'] === 'articles') {
                    // Agrega los artículos a la conversación
                    $this->messages[] = [
                        'from' => 'articles',
                        'data' => $item['articulos'],
                    ];
                } elseif ($item['tipo'] === 'llm_response') {
                    // Agrega la respuesta del bot. Se mostrará con efecto typing.
                    $this->messages[] = [
                        'from' => 'bot',
                        'text' => $item['respuesta'],
                    ];
                }
            }
        }

        // Limpia el input
        $this->newMessage = '';
    }

    public function render()
    {
        return view('livewire.medisearch.index');
    }
}
