<?php

namespace App\Livewire\Medisearch;

use App\Http\Resources\IA\AIResponseResourceFactory;
use App\Models\Config;
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
    public $config = true;

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
        $config = Config::query()->where('tipo', 'services.MBAI.openai_quota_exceeded')->first();
        if (isset($config) && $config->value === 'true') {
            $this->modelosIA = ['medisearch' => 'Medisearch'];
            $this->modeloIA = 'medisearch';
            $this->config = false;
        }

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
        if (!$user->hasAnyRole('root', 'admin', 'colab', 'Rector')) {
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
        if (!$user->hasAnyRole('root', 'admin', 'colab', 'Rector')) {
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

        $conversationId = $this->getConversationId($this->activeChatId);

        // Prepara el payload para el backend Python
        $payload = [
            'client' => $this->modeloIA,
            'query' => $query,
            'image' =>'',
            'chat_id' => $this->activeChatId,
            'audio' =>'',
            'model' => '',
            'include_articles' =>  $this->investigacionProfunda,
            'conversation_id' => $conversationId,
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
            $data['data']['query'] = $query;
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

    private function getConversationId(mixed $activeChatId)
    {
        $message = MedisearchQuestion::query()->where('chat_id', $this->activeChatId)->get()->last();
        if (!empty($message) && !empty($message->response['data']) && !empty($message->response['data']['conversation_id'])) {
            return $message->response['data'] != null ?   : '' ;
        }
    }
}
