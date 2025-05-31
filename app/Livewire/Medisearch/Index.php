<?php

namespace App\Livewire\Medisearch;

use App\Models\MedisearchQuestion;
use App\Services\Chat\chatService;
use App\Services\Usuarios\MBIAService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class Index extends Component
{
    protected MBIAService $MBIAService;
    protected chatService $chatService;
    public Collection $chatHistory, $messages;
    public bool $showEditModal, $showDeleteModal, $openModalFilter, $deepResearch, $openSidebar;
    public int|null $activeChatId, $editChatId, $deleteChatId, $current, $currentAdvance, $from_date, $to_date;
    public string $editChatName, $deleteChatName, $activeChatTitle, $newMessage, $question;
    public array $groupedChats, $chatGroupsOpen, $suggestedQuestions, $questionsBasic, $questionsAdvanced, $fontOptions,
        $typeOptions, $selectedOptions, $selectedTypeOptions;

    private function setingProps()
    {
        $this->chatHistory = collect();
        $this->showEditModal = false;
        $this->showDeleteModal = false;
        $this->activeChatId = null;
        $this->editChatId = null;
        $this->deleteChatId = null;
        $this->editChatName = '';
        $this->deleteChatName = '';
        $this->groupedChats = [];
        $this->chatGroupsOpen = [];
        $this->activeChatTitle = '';
        $this->suggestedQuestions = [];
        $this->messages = collect();
        $this->openModalFilter = false;
        $this->deepResearch = false;
        $this->current = 0;
        $this->currentAdvance = 0;
        $this->question = '';
        $this->fontOptions = [
            'PubMed',
        ];
        $this->typeOptions = [
            'Meta Analyses',
            'Reviews',
            'Clinical Trials',
            'Observational Studies',
            'Otros',
        ];
        $this->selectedOptions = ["PubMed"];
        $this->selectAll();
        $this->selectTypeAll();
        $this->from_date = 1890;
        $this->to_date = 2025;
        $this->openSidebar = false;
        $this->setQuestionPrompt();


    }

    public function setQuestionPrompt()
    {
        // Usar cache con una duración de 24 horas (86400 segundos)
        $questions = Cache::remember('mbia_questions', 86400, function () {
            return $this->MBIAService->generateEasyQuestions() ?? [];
        });

        // Si el caché está vacío o ha expirado, usamos un array de respaldo
        $fallbackQuestions = [
            'basic' => [
                '¿El deporte aumenta la esperanza de vida?',
                '¿Cuáles son las probabilidades de contraer cáncer?'
            ],
            'advanced' => [
                '¿La vacuna contra el COVID empeora la artritis?',
                '¿El control de la natalidad hormonal puede afectar la demografía?'
            ]
        ];

        // Dividir las preguntas cacheadas
        $this->questionsBasic = array_slice($questions, 0, 5);
        $this->questionsAdvanced = array_slice($questions, 5, 5);

        // Usar preguntas de respaldo si no hay suficientes en caché
        if (count($this->questionsAdvanced) <= 0) {
            $this->questionsAdvanced = $fallbackQuestions['advanced'];
        }

        if (count($this->questionsBasic) <= 0) {
            $this->questionsBasic = $fallbackQuestions['basic'];
        }
    }

    public function boot(MBIAService $MBIAService, chatService $chatService)
    {
        $this->MBIAService = $MBIAService;
        $this->chatService = $chatService;
    }

    public function mount()
    {
        $this->openSidebar = session('sidebarAbierto', true);
        $this->setingProps();
        $this->activeChatId = session('activeChatId');

        if ($this->activeChatId) {
            // Verificar que el chat aún existe
            $existingChat = $this->chatService->findChat($this->activeChatId);

            if (!$existingChat) {
                // Si el chat fue eliminado, crear uno nuevo
                $this->activeChatId = $this->createFirstChat();
                session()->put('activeChatId', $this->activeChatId);
            }

            $this->loadChatHistory();
            $this->groupChatsByAge();
            $this->loadChatMessages($this->activeChatId);
        } else {
            // 2. Si no hay chat en sesión, crear uno nuevo
            $this->activeChatId = $this->createFirstChat();
            session()->put('activeChatId', $this->activeChatId);
            $this->loadChatHistory();
            $this->groupChatsByAge();
        }
    }

    public function render()
    {
        return view('livewire.medisearch.index');
    }

    public function toggleSidebar()
    {
        $this->openSidebar = !$this->openSidebar;

        // Guardar estado en la sesión
        session()->put('openSidebar', $this->openSidebar);
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
        $firstChat = $this->chatService->createNewChat(
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
            // Truncar el título si es necesario
            $originalTitle = $chat->title;
            if (strlen($originalTitle) > 20) {
                $chat->title = substr($originalTitle, 0, 17) . '...';
                $chat->save();
            }
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
        $this->groupedChats = array_filter($groups, function ($items) {
            return !empty($items);
        });

        // Inicializar el estado de apertura de cada grupo (abierto por defecto)
        foreach (array_keys($this->groupedChats) as $group) {
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

        // Actualizar la sesión con el nuevo chat activo
        session()->put('activeChatId', $chatId);
    }

    private function loadChatMessages($chatId)
    {
        $this->messages = $this->chatService->loadMessages($chatId)
            ->flatMap(function ($question) {
                $messages = [];

                // Mensaje del usuario
                $messages[] = [
                    'is_new' => 'false',
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
                                'is_new' => 'false',
                                'from' => 'bot',
                                'text' => $resultado['respuesta'],
                                'references' => [],
                                'timestamp' => $question->created_at
                            ];
                        } elseif ($resultado['tipo'] === 'articles' && !empty($resultado['articulos'])) {
                            // Artículos relacionados
                            $messages[] = [
                                'is_new' => 'false',
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

    public function openDeleteModal($chatId)
    {
        $chat = $this->chatHistory->where('id', $chatId)->first();
        $this->deleteChatId = $chatId;
        $this->deleteChatName = $chat->title ?? '';
        $this->showDeleteModal = true;
    }

    public function openFilters()
    {
        $this->openModalFilter = true;
    }

    public function closeEditModal()
    {
        $this->showEditModal = false;
        $this->editChatId = null;
        $this->editChatName = '';
        $this->loadChatHistory();

    }

    public function closeDeleteModal()
    {
        $this->showDeleteModal = false;
        $this->deleteChatId = null;
        $this->deleteChatName = '';
        $this->loadChatHistory();

    }

    public function closeFilters()
    {
        $this->openModalFilter = false;
    }

    public function saveChatName()
    {
        $this->chatService->updateTitle($this->editChatId, $this->editChatName, Auth::id());
        $this->loadChatHistory();
        $this->closeEditModal();
    }

    public function deleteChat()
    {
        $this->chatService->deleteChat($this->deleteChatId, Auth::id());
        $this->loadChatHistory();
        $this->groupChatsByAge(); // <-- ¡Añade esta línea!
        $this->closeDeleteModal();
    }

    public function createNewChat()
    {
        // Si el chat actual está vacío (sin mensajes), no permitas crear uno nuevo
        if ($this->messages->isEmpty()) {
            // Opcional: puedes mostrar un mensaje de error o feedback
            $this->dispatch('error', message: 'Debes escribir al menos un mensaje antes de crear un nuevo chat.');
            return;
        }

        try {
            $newChat = $this->chatService->createNewChat(
                userId: Auth::id(),
                title: 'Nuevo Chat ' . now()->format('d/m H:i')
            );

            $this->loadChatHistory();
            $this->groupChatsByAge();
            $this->activeChatId = $newChat->id;
            $this->messages = collect();
            $this->newMessage = '';
            session()->put('activeChatId', $newChat->id);
            $this->dispatch('chat-selected', chatId: $newChat->id);

        } catch (\Exception $e) {
            $this->dispatch('error', message: 'Error al crear nuevo chat: ' . $e->getMessage());
        }
    }

    public function setQuestion($question)
    {
        $this->newMessage = $question;
        $this->sendMessage();
    }

    public function updatedDeepResearch($value)
    {
        $this->setActiveFilters($value);
    }

    private function setActiveFilters($deep)
    {
        if ($deep) {
            $this->fontOptions = [
                'internationalHealthGuidelines',
                'medicineGuidelines',
                'scientificArticles',
                'books',
                'healthBlogs'
            ];
            $this->typeOptions = [
                'metaAnalysis',
                'reviews',
                'clinicalTrials',
                'observationalStudies',
                'other'
            ];
            $this->selectAll();
            $this->selectTypeAll();
        } else {
            $this->selectedOptions = ["PubMed"];
            $this->fontOptions = [
                'PubMed',
            ];
            $this->typeOptions = [
                'Meta Analyses',
                'Reviews',
                'Clinical Trials',
                'Observational Studies',
                'Otros',
            ];
            $this->selectAll();
            $this->selectTypeAll();
        }
    }

    public function sendMessage()
    {
        // Validar que haya texto y un chat activo
        $query = trim($this->newMessage ?? '');
        if (!$query || !$this->activeChatId) {
            return;
        }
        $config = [
            'fuentes' => $this->selectedOptions,
            'years' => [$this->from_date, $this->to_date],
            'types' => $this->selectedTypeOptions,
            'lang' => 'es', // Por default en todos los Idiomas
        ];

        // Opcional: puedes limitar longitud o limpiar caracteres no permitidos
        if (mb_strlen($query) > 800) {
            // Puedes emitir un error o feedback al usuario
            $this->dispatch('error', message: 'El mensaje es demasiado largo.');
            return;
        }

        // Crear la pregunta en la base de datos
        $question = MedisearchQuestion::create([
            'user_id' => \Auth::id(),
            'chat_id' => $this->activeChatId,
            'query' => $query,
            'response' => null, // Se puede actualizar luego con la respuesta del bot
        ]);
        // Detectar si es la primera pregunta del chat
        $isFirstMessage = MedisearchQuestion::where('chat_id', $this->activeChatId)->count() === 1;
        if ($isFirstMessage) {
            // Llama a tu servicio de IA para el título
            $titulo = $this->MBIAService->generateTitleFromQuestion($query);

            // Si la IA no responde, usa el inicio de la pregunta como fallback
            if (!$titulo['content']) {
                $titulo = mb_strlen($query) > 20 ? mb_substr($query, 0, 17) . '...' : $query;
            } else {
                $titulo = $titulo['content'];
            }

            // SANITIZAR EL TÍTULO ANTES DE GUARDAR
            $titulo = $this->sanitizeTitle($titulo);

            try {
                // Actualiza el chat en DB
                $this->chatService->updateTitle($this->activeChatId, $titulo, Auth::id());

                // Refresca el historial y el título activo en Livewire
                $this->loadChatHistory();
                $this->activeChatTitle = $titulo;
                $this->groupChatsByAge();
            } catch (\Exception $e) {
                \Log::error('Error al actualizar título: ' . $e->getMessage());

                // Fallback con título genérico
                $tituloFallback = 'Chat ' . now()->format('Y-m-d H:i');
                $this->chatService->updateTitle($this->activeChatId, $tituloFallback, Auth::id());
                $this->activeChatTitle = $tituloFallback;
                $this->groupChatsByAge();
            }
        }


        // Agregar el mensaje del usuario al array de mensajes (frontend inmediato)
        $this->messages[] = [
            'from' => 'user',
            'text' => $query,
        ];

        // Limpiar el input
        $this->newMessage = '';

        $client = $this->deepResearch ? 'medisearch' : 'MBIA';

        // Lógica para obtener la respuesta del bot:
        $payload = [
            'client' => $client,
            'query' => $query,
            'image' => '',
            'chat_id' => $this->activeChatId,
            'audio' => '',
            'model' => '',
            'include_articles' => true,
            'conversation_id' => '',
            'config' => json_encode($config),
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
                        'is_new' => true,
                        'from' => 'bot',
                        'text' => $item['respuesta'],
                        'references' => $references,
                    ];
                } elseif ($item['tipo'] === 'articles' && !empty($item['articulos'])) {
                    $this->messages[] = [
                        'is_new' => true,
                        'from' => 'articles',
                        'data' => $item['articulos'],
                    ];
                }
            }
        }

        // Guardar la respuesta completa en la pregunta
        $question->response = $responseData;
        $this->dispatch('new-message');
        $question->save();
    }

    public function selectAll()
    {
        $this->selectedOptions = $this->fontOptions;
    }

    public function deselectAll()
    {
        $this->selectedOptions = [];
    }

    public function selectTypeAll()
    {
        $this->selectedTypeOptions = $this->typeOptions;
    }

    public function deselectTypeAll()
    {
        $this->selectedTypeOptions = [];
    }

    public function nextQuestion()
    {
        $this->current = ($this->current < count($this->questionsBasic) - 1)
            ? $this->current + 1
            : 0;
    }

    public function previousQuestion()
    {
        $this->current = ($this->current > 0)
            ? $this->current - 1
            : count($this->questionsBasic) - 1;
    }

    public function nextQuestionAdvance()
    {
        $this->currentAdvance = ($this->currentAdvance < count($this->questionsAdvanced) - 1)
            ? $this->currentAdvance + 1
            : 0;
    }

    public function previousQuestionAdvance()
    {
        $this->currentAdvance = ($this->currentAdvance > 0)
            ? $this->currentAdvance - 1
            : count($this->questionsAdvanced) - 1;
    }

    public function addNewLine()
    {
        // Agrega un salto de línea al contenido
        $this->newMessage .= "\n";
    }
}
