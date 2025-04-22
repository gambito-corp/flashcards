<?php

namespace App\Livewire\Medisearch;

use App\Http\Resources\IA\AIResponseResourceFactory;
use App\Services\DeepseekService;
use App\Services\MedisearchService;
use App\Services\OpenAiService;
use App\Services\PerplexityService;
use App\Services\Usuarios\MBIAService;
use GuzzleHttp\Client;
use Livewire\Component;
use Illuminate\Support\Facades\Http;
use App\Models\MedisearchChat;
use App\Models\MedisearchQuestion;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use OpenAI\Laravel\Facades\OpenAI;

class Index extends Component
{
    protected OpenAiService $openAiService;
    protected MedisearchService $medisearchService;
    protected DeepseekService $deepseekService;
    protected PerplexityService $perplexityService;
    protected MBIAService $MBIAService;

    public $newMessage = '';
    public $messages = [];
    public $activeChatId = null; // ID del chat activo
    public $chatHistory = [];    // Historial de chats del usuario
    public $queryCount = 0;      // Contador de preguntas del mes
    public $modelosIA = ['medisearch' => 'Medisearch', 'MBIA' => 'MBIA'];
    public $modeloIA = 'MBIA'; // Valor por defecto, puedes cambiarlo
    public $investigacionProfunda = false;

    public function boot(
        MBIAService $MBIAService,
        OpenAiService $openAiService,
        MedisearchService $medisearchService,
        DeepseekService $deepseekService,
        PerplexityService $perplexityService,
    )
    {
        $this->MBIAService = $MBIAService;
        $this->openAiService = $openAiService;
        $this->medisearchService = $medisearchService;
        $this->deepseekService = $deepseekService;
        $this->perplexityService = $perplexityService;
    }
    public function mount()
    {
        $this->updateQueryCount();
        $this->loadChatHistory();

        // Si existe un chat activo en sesión, se carga
        $this->activeChatId = session('activeChatId', null);
        if ($this->activeChatId) {
            $this->loadChatMessages($this->activeChatId);
        }
        if ($this->modeloIA == 'medisearch') {
            $this->investigacionProfunda = true;
        } else {
            $this->investigacionProfunda = false;
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
                    }elseif ($item['tipo'] === 'llm_response') {
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



        // Prepara el payload para el backend Python
        $payload = [
            'client' => $this->modeloIA,
            'query' => $query,
            'image' =>'',
            'audio' =>'',
            'model' => '',
            'include_articles' =>  $this->investigacionProfunda,
            'conversation_id' => '',
        ];

        $data = $this->MBIAService->search($payload);

        if (isset($data['data']['resultados'])) {
            foreach ($data['data']['resultados'] as $item) {
                if ($item['tipo'] === 'articles') {
                    $this->messages[] = [
                        'from' => 'articles',
                        'data' => $item['articulos'],
                    ];
                }elseif ($item['tipo'] === 'llm_response') {
                    $this->messages[] = [
                        'from' => 'bot',
                        'text' => $item['respuesta'],
                    ];
                }
            }
        }
        // Guardar en la base de datos
        MedisearchQuestion::create([
            'user_id'  => $user->id,
            'chat_id'  => $this->activeChatId,
            'model'    => $this->modeloIA,
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



    /*
     * public function sendMessage()
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

        $response =  $this->openAiService->buscarOpenAI($query, 2);
        dd($response);
        // Transformamos la estructura:
        $transformedResponse = [
            "data" => [
                "query" => "¿Por qué la respuesta inmunológica ante el parásito de Naegleria fowleri provoca más daño en lugar de ayudar? Responde completo y no tan largo patogénicamente.",
                "resultados" => [
                    [
                        "tipo" => "llm_response",
                        "respuesta" => trim($response["clean_text"])
                    ],
                    [
                        "tipo" => "articles",
                        "articulos" => array_map(function ($url) {
                            return [
                                "url" => $url["url"],
                                "tldr" => $url["description"],
                                "year" => "", // Si tienes información del año, inclúyelo aquí.
                                "title" => $url["title"],
                                "authors" => [], // Si tienes autores, puedes añadirlos aquí.
                                "journal" => ""  // Si tienes información del journal, inclúyela aquí.
                            ];
                        }, $response["urls"])
                    ]
                ]
            ]
        ];

        dd($transformedResponse);

        if (isset($response['clean_text'])){
            $this->messages[] = [
                'from' => 'bot',
                'text' => $response['clean_text'],
            ];
            foreach ($response['urls'] as $url) {
                $this->messages[] = [
                    'from' => 'articles',
                    'data' => $url,
                ];
            }
        }

    }
    */
}
