<?php

namespace App\Livewire\Medisearch;

use App\Models\Config;
use App\Services\Chat\chatService;
use App\Services\DeepseekService;
use App\Services\MedisearchService;
use App\Services\OpenAiService;
use App\Services\PerplexityService;
use App\Services\Usuarios\MBIAService;
use Illuminate\Support\Collection;
use Livewire\Component;
use App\Models\MedisearchChat;
use App\Models\MedisearchQuestion;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{
    protected MBIAService $MBIAService;
    protected chatService $chatService;
    public Collection $chatHistory, $messages;
    public bool $showEditModal, $openModalFilter, $deepResearch;
    public int|null $activeChatId, $editChatId;
    public string $editChatName, $activeChatTitle, $newMessage;
    public array $groupedChats, $chatGroupsOpen, $suggestedQuestions;


    public function boot(
        MBIAService $MBIAService,
        chatService $chatService
    ){
        $this->MBIAService = $MBIAService;
        $this->chatService = $chatService;
    }
    public function mount()
    {
        $this->setingProps();
        $this->loadChatHistory();
        $this->groupChatsByAge();
    }
    public function render()
    {
        return view('livewire.medisearch.index');
    }


    private function loadChatHistory()
    {
        $this->chatHistory = $this->chatService->loadChats(Auth::id());
        $this->activeChatId = $this->chatHistory->isNotEmpty()
            ? $this->chatHistory->first()->id
            : $this->createFirstChat();
    }
    private function createFirstChat()
    {
        $newChat = $this->chatService->createNewChat(
                userId: Auth::id(),
                title: 'Nuevo Chat ' . now()->format('d/m H:i')
            );
        return $firstChat->id;
    }
    private function groupChatsByAge()
    {
        $now = now();
        $groups = [
            'Últimos' => [],
            'Últimos 7 días' => [],
            'Últimos 30 días' => [],
            'Último año' => [],
            'El resto' => []
        ];

        foreach ($this->chatHistory as $chat) {
            $diffDays = $now->diffInDays($chat->created_at);

            if ($diffDays == 0) {
                $groups['Últimos'][] = $chat;
            } elseif ($diffDays <= 7) {
                $groups['Últimos 7 días'][] = $chat;
            } elseif ($diffDays <= 30) {
                $groups['Últimos 30 días'][] = $chat;
            } elseif ($diffDays <= 365) {
                $groups['Último año'][] = $chat;
            } else {
                $groups['El resto'][] = $chat;
            }
        }

        // Filtrar grupos vacíos y mantener el orden
        $this->groupedChats = array_filter($groups, function($items) {
            return !empty($items);
        });

        // Inicializar el estado de apertura de cada grupo (abierto por defecto)
        foreach(array_keys($this->groupedChats) as $group) {
            if (!isset($this->chatGroupsOpen[$group])) {
                $this->chatGroupsOpen[$group] = true;
            }
        }
    }
    public function toggleChatGroup($group)
    {
        $this->chatGroupsOpen[$group] = !($this->chatGroupsOpen[$group] ?? false);
    }
    public function selectChat($chatId)
    {
        $this->loadChatMessages($chatId);
        $this->activeChatId = $chatId;
        $chat = $this->chatService->findChat($chatId);
        $this->activeChatTitle = $chat->title ?? "Chat #{$chatId}";
    }

    private function loadChatMessages($chatId)
    {
        $this->messages = $this->chatService->loadMessages($chatId)
            ->flatMap(function ($question) {
                $messages = [];

                // Mensaje del usuario
                $messages[] = [
                    'from' => 'user',
                    'text' => $question->query,
                    'timestamp' => $question->created_at
                ];

                // Procesar respuesta
                if (isset($question->response['data']['resultados'])) {
                    foreach ($question->response['data']['resultados'] as $resultado) {
                        if ($resultado['tipo'] === 'llm_response') {
                            // Mensaje del bot
                            $messages[] = [
                                'from' => 'bot',
                                'text' => $resultado['respuesta'],
                                'references' => [],
                                'timestamp' => $question->created_at
                            ];
                        } elseif ($resultado['tipo'] === 'articles' && !empty($resultado['articulos'])) {
                            // Artículos relacionados
                            $messages[] = [
                                'from' => 'articles',
                                'data' => $resultado['articulos'],
                                'timestamp' => $question->created_at
                            ];
                        }
                    }
                }

                return $messages;
            });
    }
    public function openEditModal($chatId)
    {
        $chat = $this->chatHistory->where('id', $chatId)->first();
        $this->editChatId = $chatId;
        $this->editChatName = $chat->title ?? '';
        $this->showEditModal = true;
    }
    public function openFilters()
    {
        dd($this->openModalFilter);
    }
    public function closeEditModal()
    {
        $this->showEditModal = false;
        $this->editChatId = null;
        $this->editChatName = '';
    }
    public function closeFilters()
    {
        dd($this->openModalFilter);
    }
    public function saveChatName()
    {
        $this->chatService->updateTitle($this->editChatId, $this->editChatName, Auth::id());
        $this->loadChatHistory();
        $this->closeEditModal();
    }
    public function createNewChat()
    {
        try {
            $newChat = $this->chatService->createNewChat(
                userId: Auth::id(),
                title: 'Nuevo Chat ' . now()->format('d/m H:i')
            );

            $this->loadChatHistory();
            $this->groupChatsByAge();
            $this->activeChatId = $newChat->id;
            $this->dispatch('chat-selected', chatId: $newChat->id); // Opcional para notificar a otros componentes

        } catch (\Exception $e) {
            $this->dispatch('error', message: 'Error al crear nuevo chat: ' . $e->getMessage());
        }
    }
    public function setQuestion($question)
    {
        $this->newMessage = $question;
        $this->sendMessage();
    }
    public function sendMessage()
    {
        // Validar que haya texto y un chat activo
        $query = trim($this->newMessage ?? '');
        //dd($this->activeChatId);
        if (!$query || !$this->activeChatId) {
            return;
        }

        // Opcional: puedes limitar longitud o limpiar caracteres no permitidos
        if (mb_strlen($query) > 800) {
            // Puedes emitir un error o feedback al usuario
            $this->dispatch('error', message: 'El mensaje es demasiado largo.');
            return;
        }

        // Crear la pregunta en la base de datos
        $question = \App\Models\MedisearchQuestion::create([
            'user_id'  => \Auth::id(),
            'chat_id'  => $this->activeChatId,
            'query'    => $query,
            'response' => null, // Se puede actualizar luego con la respuesta del bot
        ]);

        // Agregar el mensaje del usuario al array de mensajes (frontend inmediato)
        $this->messages[] = [
            'from' => 'user',
            'text' => $query,
            'loading' => false,
        ];

        // Limpiar el input
        $this->newMessage = '';

        // Lógica para obtener la respuesta del bot:
        $payload = [
            'client' => 'MBIA',
            'query' => $query,
            'image' =>'',
            'chat_id' => $this->activeChatId,
            'audio' =>'',
            'model' => '',
            'include_articles' =>  true,
            'conversation_id' => '',
            'config' => [
                'fuentes' => ["PubMed"],
                'years' => [2015, 2025],
                'types' => ["Meta Analysis",
                    "Clinical Trials"],
                'lang' => 'es',
            ]
        ];
        // Aquí deberías llamar a tu servicio de IA, por ejemplo:
        $responseData = $this->MBIAService->search($payload);

        // Procesar y mostrar la respuesta del bot
        if (isset($responseData['data']['resultados'])) {
            foreach ($responseData['data']['resultados'] as $item) {
                if ($item['tipo'] === 'llm_response') {
                    // Extrae referencias del HTML si existen
                    $references = [];
                    if (preg_match('/<div class="referencias">(.*?)<\/div>/s', $item['respuesta'], $matches)) {
                        preg_match_all('/<li>(.*?)<\/li>/s', $matches[1], $refMatches);
                        $references = array_map('strip_tags', $refMatches[1] ?? []);
                    }

                    $this->messages[] = [
                        'from' => 'bot',
                        'text' => $item['respuesta'],
                        'references' => $references,
                        'loading' => true,
                    ];
                } elseif ($item['tipo'] === 'articles' && !empty($item['articulos'])) {
                    $this->messages[] = [
                        'from' => 'articles',
                        'data' => $item['articulos'],
                        'loading' => true,
                    ];
                }
            }
        }

        // Guardar la respuesta completa en la pregunta
        $question->response = $responseData;
        $this->dispatch('new-message');
        $question->save();
    }

    private function setingProps()
    {
        $this->chatHistory = collect();
        $this->showEditModal = false;
        $this->activeChatId = null;
        $this->editChatId = null;
        $this->editChatName = '';
        $this->groupedChats = [];
        $this->chatGroupsOpen = [];
        $this->activeChatTitle = '';
        $this->suggestedQuestions= [
            '¿El deporte aumenta la esperanza de vida?',
            '¿Cuáles son las probabilidades de contraer cáncer?',
            '¿La vacuna contra el COVID empeora la artritis?',
            '¿El control de la natalidad hormonal puede afectar la demografía?'
        ];
        $this->messages = collect();
        $this->openModalFilter = false;
        $this->deepResearch = false;
    }

//    public $newMessage = '';
//    public $messages = [];
//    public $activeChatId = null;
//    public $chatHistory = [];
//    public $queryCount = 0;
//    public $modelosIA = ['medisearch' => 'Medisearch', 'MBIA' => 'MBIA'];
//    public $modeloIA = 'MBIA'; // Valor por defecto, puedes cambiarlo
//    public $investigacionProfunda = false;
//    public $config = true;
//    public $suggestedQuestions = [
//        '¿El deporte aumenta la esperanza de vida?',
//        '¿Cuáles son las probabilidades de recuperación tras un ictus?',
//        '¿La vacuna contra el COVID empeora la artritis?',
//        '¿El control de la natalinad afecta a la demografía?'
//    ];
//    public $showFilters = false;
//    public $filters = [
//        'year' => 2025,
//        'sources' => [],
//        'types' => []
//    ];
//    public function mount()
//    {
//        $this->updateQueryCount();
//        $this->loadChatHistory();
//        $this->checkMBIAStatus();
//        $this->chargeChatsSessions();
//        if ($this->modeloIA == 'medisearch') {
//            $this->investigacionProfunda = true;
//        } else {
//            $this->investigacionProfunda = false;
//        }
//    }


//    public function setQuestion($question)
//    {
//        $this->newMessage = $question;
//    }
//    public $availableSources = [
//        ['value' => 'articles', 'label' => 'Artículos científicos'],
//        ['value' => 'books', 'label' => 'Libros'],
//        ['value' => 'guidelines', 'label' => 'Directrices internacionales de salud'],
//        ['value' => 'guides', 'label' => 'Guías de práctica'],
//        ['value' => 'healthlines', 'label' => 'Healthlines']
//    ];
//
//    public $availableTypes = [
//        ['value' => 'metanalysis', 'label' => 'Metanálisis'],
//        ['value' => 'review', 'label' => 'Artículos de revisión'],
//        ['value' => 'trials', 'label' => 'Ensayos clínicos'],
//        ['value' => 'others', 'label' => 'Otros']
//    ];
//
//
//    // Actualiza el contador de preguntas mensuales
//    private function updateQueryCount()
//    {
//        $user = Auth::user();
//        if (!$user->hasAnyRole('root', 'admin', 'colab', 'Rector')) {
//            $this->queryCount = MedisearchQuestion::where('user_id', $user->id)
//                ->where('created_at', '>=', Carbon::now()->startOfMonth())
//                ->count();
//        } else {
//            $this->queryCount = 0;
//        }
//    }
//
//    // Carga el historial de chats del usuario
//    private function loadChatHistory()
//    {
//        $user = Auth::user();
//        $this->chatHistory = MedisearchChat::where('user_id', $user->id)
//            ->orderBy('created_at', 'desc')
//            ->get();
//    }
//
//    // Carga los mensajes de un chat seleccionado
//    private function loadChatMessages($chatId)
//    {
//        // Se recuperan todas las preguntas/respuestas de ese chat, ordenadas cronológicamente
//        $questions = MedisearchQuestion::where('chat_id', $chatId)
//            ->orderBy('created_at', 'asc')
//            ->get();
//
//        $this->messages = [];
//        foreach ($questions as $question) {
//            // Agregamos el mensaje del usuario
//            $this->messages[] = [
//                'from' => 'user',
//                'text' => $question->query,
//            ];
//            // Procesamos la respuesta almacenada (asumiendo que viene con artículos y respuesta llm)
//            $data = $question->response;
//            if (isset($data['data']['resultados'])) {
//                foreach ($data['data']['resultados'] as $item) {
//                    if ($item['tipo'] === 'articles') {
//                        $this->messages[] = [
//                            'from' => 'articles',
//                            'data' => $item['articulos'],
//                        ];
//                    }elseif ($item['tipo'] === 'llm_response') {
//                        $this->messages[] = [
//                            'from' => 'bot',
//                            'text' => $item['respuesta'],
//                        ];
//                    }
//                }
//            }
//        }
//        $this->activeChatId = $chatId;
//        session(['activeChatId' => $chatId]);
//    }
//
//    // Selecciona un chat del historial y carga sus mensajes
//    public function selectChat($chatId)
//    {
//        $this->loadChatMessages($chatId);
//    }
//
//    public function sendMessage()
//    {
//        $user = Auth::user();
//
//        if (!$user->hasAnyRole('root', 'admin', 'colab', 'Rector')) {
//            $limite = $user->status == 0 ? 20 : 100;
//            if ($this->queryCount >= $limite) {
//                $this->messages[] = [
//                    'from' => 'bot',
//                    'text' => 'Has alcanzado el límite de '.$limite.' preguntas por mes. Por favor, revisa nuestros planes de suscripción.',
//                ];
//                $this->newMessage = '';
//                return;
//            }
//        }
//
//        $query = $this->newMessage;
//
//        // Si no hay chat activo, se crea uno
//        if (!$this->activeChatId) {
//            $chat = MedisearchChat::create([
//                'user_id' => $user->id,
//                'title'   => 'Chat ' . now()->toDateTimeString(),
//            ]);
//            $this->activeChatId = $chat->id;
//            session(['activeChatId' => $chat->id]);
//            $this->loadChatHistory(); // Actualiza el historial
//        }
//
//        // Agrega el mensaje del usuario a la conversación (interfaz)
//
//        $this->messages[] = [
//            'from' => 'user',
//            'text' => $query,
//        ];
//
//        $conversationId = $this->getConversationId($this->activeChatId);
//
//        // Prepara el payload para el backend Python
//        $payload = [
//            'client' => $this->modeloIA,
//            'query' => $query,
//            'image' =>'',
//            'chat_id' => $this->activeChatId,
//            'audio' =>'',
//            'model' => '',
//            'include_articles' =>  $this->investigacionProfunda,
//            'conversation_id' => $conversationId,
//        ];
//
//        $data = $this->MBIAService->search($payload);
//        if (isset($data['data']['resultados'])) {
//            foreach ($data['data']['resultados'] as $item) {
//                if ($item['tipo'] === 'articles') {
//                    $this->messages[] = [
//                        'from' => 'articles',
//                        'data' => $item['articulos'],
//                    ];
//                }elseif ($item['tipo'] === 'llm_response') {
//                    $this->messages[] = [
//                        'from' => 'bot',
//                        'text' => $item['respuesta'],
//                    ];
//                }
//            }
//            $data['data']['query'] = $query;
//        }
//        // Guardar en la base de datos
//        MedisearchQuestion::create([
//            'user_id'  => $user->id,
//            'chat_id'  => $this->activeChatId,
//            'model'    => $this->modeloIA,
//            'query'    => $query,
//            'response' => $data,
//        ]);
//
//        $this->newMessage = '';
//        $this->updateQueryCount();
//    }



//    private function getConversationId(mixed $activeChatId)
//    {
//        $message = MedisearchQuestion::query()->where('chat_id', $this->activeChatId)->get()->last();
//        if (!empty($message) && !empty($message->response['data']) && !empty($message->response['data']['conversation_id'])) {
//            return $message->response['data'] != null ?   : '' ;
//        }
//    }
//
//    private function checkMBIAStatus(): void
//    {
//        $config = Config::query()->where('tipo', 'services.MBAI.openai_quota_exceeded')->first();
//        if (isset($config) && $config->value === 'true') {
//            $this->modelosIA = ['medisearch' => 'Medisearch'];
//            $this->modeloIA = 'medisearch';
//            $this->investigacionProfunda = true;
//            $this->config = false;
//        }
//    }
//
//    private function chargeChatsSessions(): void
//    {
//        // Si existe un chat activo en sesión, se carga
//        $this->activeChatId = session('activeChatId', null);
//        if ($this->activeChatId) {
//            $this->loadChatMessages($this->activeChatId);
//        }
//    }
}
