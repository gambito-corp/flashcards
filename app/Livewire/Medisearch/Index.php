<?php

namespace App\Livewire\Medisearch;

use Livewire\Component;
use Illuminate\Support\Facades\Http;
use App\Models\MedisearchChat;
use App\Models\MedisearchQuestion;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{
    public $newMessage = '';
    public $messages = [];
    public $activeChatId = null; // ID del chat activo
    public $chatHistory = [];    // Historial de chats del usuario
    public $queryCount = 0;      // Contador de preguntas del mes

    public function mount()
    {
        $this->updateQueryCount();
        $this->loadChatHistory();

        // Si existe un chat activo en sesión, se carga
        $this->activeChatId = session('activeChatId', null);
        if ($this->activeChatId) {
            $this->loadChatMessages($this->activeChatId);
        }
    }

    // Actualiza el contador de preguntas mensuales
    public function updateQueryCount()
    {
        $user = Auth::user();
        if (!$user->hasRole('root')) {
            $this->queryCount = MedisearchQuestion::where('user_id', $user->id)
                ->where('created_at', '>=', Carbon::now()->startOfMonth())
                ->count();
        } else {
            $this->queryCount = 0;
        }
    }

    // Carga el historial de chats del usuario
    public function loadChatHistory()
    {
        $user = Auth::user();
        $this->chatHistory = MedisearchChat::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    // Carga los mensajes de un chat seleccionado
    public function loadChatMessages($chatId)
    {
        // Se recuperan todas las preguntas/respuestas de ese chat, ordenadas cronológicamente
        $questions = MedisearchQuestion::where('chat_id', $chatId)
            ->orderBy('created_at', 'asc')
            ->get();

        $this->messages = [];
        foreach ($questions as $question) {
            // Agregamos el mensaje del usuario
            $this->messages[] = [
                'from' => 'user',
                'text' => $question->query,
            ];

            // Procesamos la respuesta almacenada (asumiendo que viene con artículos y respuesta llm)
            $data = $question->response;
            if (isset($data['data']['resultados'])) {
                foreach ($data['data']['resultados'] as $item) {
                    if ($item['tipo'] === 'articles') {
                        $this->messages[] = [
                            'from' => 'articles',
                            'data' => $item['articulos'],
                        ];
                    } elseif ($item['tipo'] === 'llm_response') {
                        $this->messages[] = [
                            'from' => 'bot',
                            'text' => $item['respuesta'],
                        ];
                    }
                }
            }
        }
        $this->activeChatId = $chatId;
        session(['activeChatId' => $chatId]);
    }

    // Selecciona un chat del historial y carga sus mensajes
    public function selectChat($chatId)
    {
        $this->loadChatMessages($chatId);
    }

    public function sendMessage()
    {
        $user = Auth::user();

        // Verifica límite de 100 preguntas mensuales para usuarios que no sean root
        if (!$user->hasRole('root')) {
            if ($this->queryCount >= 100) {
                $this->messages[] = [
                    'from' => 'bot',
                    'text' => 'Has alcanzado el límite de 100 preguntas por mes. Por favor, revisa nuestros planes de suscripción.',
                ];
                $this->newMessage = '';
                return;
            }
        }

        $query = $this->newMessage;

        // Si no hay chat activo, se crea uno
        if (!$this->activeChatId) {
            $chat = MedisearchChat::create([
                'user_id' => $user->id,
                'title'   => 'Chat ' . now()->toDateTimeString(),
            ]);
            $this->activeChatId = $chat->id;
            session(['activeChatId' => $chat->id]);
            $this->loadChatHistory(); // Actualiza el historial
        }

        // Agrega el mensaje del usuario a la conversación (interfaz)
        $this->messages[] = [
            'from' => 'user',
            'text' => $query,
        ];

        // Llama a la API del servicio Python enviando el mensaje en el query param
        $endpoint = 'https://appbanqueo.medbystudents.com/api/python/search';
        $response = Http::get($endpoint, [
            'q' => $query,
        ]);
        $data = $response->json();

        // Procesa la respuesta
        if (isset($data['data']['resultados'])) {
            foreach ($data['data']['resultados'] as $item) {
                if ($item['tipo'] === 'articles') {
                    $this->messages[] = [
                        'from' => 'articles',
                        'data' => $item['articulos'],
                    ];
                } elseif ($item['tipo'] === 'llm_response') {
                    $this->messages[] = [
                        'from' => 'bot',
                        'text' => $item['respuesta'],
                    ];
                }
            }
        }

        // Guarda la pregunta y la respuesta en la base de datos, asociándola al chat activo
        MedisearchQuestion::create([
            'user_id'  => $user->id,
            'chat_id'  => $this->activeChatId,
            'query'    => $query,
            'response' => $data,
        ]);

        $this->newMessage = '';
        $this->updateQueryCount();
    }

    public function render()
    {
        return view('livewire.medisearch.index');
    }
}
