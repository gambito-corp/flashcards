<?php

namespace App\Http\Controllers\Api\MedChat;

use App\Http\Controllers\Controller;
use App\Models\MedisearchChat;
use App\Models\MedisearchQuestion;
use App\Services\Api\Commons\UserLimitService;
use App\Services\Api\OpenAI\Chat;
use App\Services\Api\OpenAI\PubMedService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class MedChatController extends Controller
{
    protected $openAI;
    protected $pubMedService;
    protected $limitService;

    public function __construct(Chat $openAI, PubMedService $pubMedService, UserLimitService $limitService)
    {
        $this->openAI = $openAI;
        $this->pubMedService = $pubMedService;
        $this->limitService = $limitService;
    }

    /**
     * ✅ MÉTODO ASK MODIFICADO PARA PERSISTENCIA
     */
    public function ask(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $searchType = $request->input('search_type', 'standard');
            $canSearch = $this->limitService->canUserSearch($user, $searchType);
            if (!$canSearch['allowed']) {
                return response()->json([
                    'success' => false,
                    'error' => 'Límite de búsquedas alcanzado',
                    'details' => $canSearch['message'],
                    'usage_info' => $canSearch
                ], 429);
            }
            $question = $request->input('question');
            $conversationId = $request->input('conversation_id');
            $chatHistory = $request->input('chat_history', []);
            $filters = $request->input('filters', null); // ✅ OBTENER FILTROS
            $searchType = $request->input('search_type', 'standard');

            // ✅ PASO 1: Crear o obtener conversación
            if (!$conversationId) {
                $chat = MedisearchChat::create([
                    'user_id' => $user->id,
                    'title' => 'Nuevo Chat ' . now()->format('d/m H:i')
                ]);
                $conversationId = $chat->id;
            } else {
                $chat = MedisearchChat::find($conversationId);
                if (!$chat || $chat->user_id !== $user->id) {
                    return response()->json([
                        'success' => false,
                        'error' => 'Conversación no encontrada o sin permisos'
                    ], 403);
                }
            }

            // ✅ PASO 2: OBTENER HISTORIAL DE LA BD (CORREGIR ESTA LÍNEA)
            $dbHistory = $this->getConversationHistory($conversationId);

            // ✅ PASO 3: GENERAR RESPUESTA CON FILTROS (AHORA INCLUYE PUBMED)
            $response = $this->openAI->generateMedicalResponse($question, $dbHistory, $filters, $searchType);
            if (!$response['success']) {
                return response()->json([
                    'success' => false,
                    'error' => $response['error']
                ], 500);
            }
            // ✅ PASO 4: OBTENER ARTÍCULOS DE PUBMED CON FILTROS (DIRECTAMENTE)
            $pubmedArticles = $response['pubmed_articles'] ?? [];

            // ✅ PASO 5: Guardar pregunta y respuesta en BD
            $questionRecord = MedisearchQuestion::create([
                'user_id' => $user->id,
                'chat_id' => $conversationId,
                'model' => $searchType,
                'query' => $question,
                'response' => [
                    'data' => [
                        'resultados' => [
                            [
                                'tipo' => 'llm_response',
                                'respuesta' => $response['answer']
                            ],
                            [
                                'tipo' => 'articles',
                                'articulos' => $pubmedArticles
                            ]
                        ]
                    ]
                ]
            ]);

            // ✅ PASO 6: Generar título automático si es la primera pregunta
            if ($chat->questions()->count() === 1) {
                $autoTitle = $this->generateAutoTitle($question);
                $chat->update(['title' => $autoTitle]);
            }

            // ✅ OBTENER INFORMACIÓN DE USO ACTUALIZADA
            $usageSummary = $this->limitService->getUserUsageSummary($user);
            $upgradeInfo = $this->limitService->shouldSuggestUpgrade($user);

            return response()->json([
                'success' => true,
                'data' => [
                    'conversation_id' => $conversationId,
                    'data' => [
                        'answer' => $response['answer'],
                        'pubmed_articles' => $pubmedArticles,
                        'pubmed_query' => $response['pubmed_query'] ?? null // ✅ PARA DEBUG
                    ]
                ],
                'usage_info' => [
                    'current_search_type' => $searchType,
                    'remaining_searches' => $canSearch['remaining'],
                    'user_type' => $canSearch['user_type'],
                    'monthly_summary' => $usageSummary,
                    'upgrade_suggestion' => $upgradeInfo,
                ],
                'message' => 'Respuesta generada correctamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error en MedChat ask: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'Error al procesar la consulta',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * ✅ OBTENER HISTORIAL DE CONVERSACIÓN DESDE BD
     */
    private function getConversationHistory(int $conversationId): array
    {
        $questions = MedisearchQuestion::where('chat_id', $conversationId)
            ->orderBy('created_at', 'asc')
            ->get();

        $history = [];
        foreach ($questions as $question) {
            // Mensaje del usuario
            $history[] = [
                'role' => 'user',
                'content' => $question->query
            ];

            // Respuesta del asistente
            if ($question->response && isset($question->response['data']['resultados'])) {
                foreach ($question->response['data']['resultados'] as $resultado) {
                    if ($resultado['tipo'] === 'llm_response') {
                        $history[] = [
                            'role' => 'assistant',
                            'content' => $resultado['respuesta']
                        ];
                        break; // Solo tomar la primera respuesta LLM
                    }
                }
            }
        }

        return $history;
    }

    /**
     * ✅ GENERAR TÍTULO AUTOMÁTICO
     */
    private function generateAutoTitle(string $question): string
    {
        return strlen($question) > 30
            ? substr($question, 0, 27) . '...'
            : $question;
    }

    // ✅ MANTENER TUS MÉTODOS EXISTENTES
    public function getBestQuestions()
    {
        Log::log('info', 'Obteniendo mejores preguntas médicas');
        try {
            $ultimasPreguntas = MedisearchQuestion::query()
                ->orderBy('created_at', 'desc')
                ->limit(100)
                ->pluck('query', 'id')
                ->toArray();

            return response()->json([
                'success' => true,
                'data' => $this->openAI->getBestMedicalQuestions($ultimasPreguntas),
                'message' => 'Preguntas sugeridas obtenidas correctamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener lista de conversaciones del usuario
     */


// app/Http/Controllers/Api/MedChat/MedChatController.php (AGREGAR ESTOS MÉTODOS)

    /**
     * ✅ OBTENER TODAS LAS CONVERSACIONES DEL USUARIO
     */
    public function getConversations(): JsonResponse
    {
        try {
            $userId = Auth::id();

            $conversations = MedisearchChat::where('user_id', $userId)
                ->with(['questions' => function ($query) {
                    $query->latest()->limit(1); // Solo la última pregunta para preview
                }])
                ->orderBy('updated_at', 'desc')
                ->get()
                ->map(function ($chat) {
                    $lastQuestion = $chat->questions->first();
                    return [
                        'id' => $chat->id,
                        'title' => $chat->title,
                        'created_at' => $chat->created_at->toISOString(),
                        'updated_at' => $chat->updated_at->toISOString(),
                        'last_message' => $lastQuestion ? [
                            'content' => $lastQuestion->query,
                            'timestamp' => $lastQuestion->created_at->toISOString()
                        ] : null,
                        'message_count' => $chat->questions()->count()
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $conversations,
                'message' => 'Conversaciones obtenidas correctamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error obteniendo conversaciones: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'Error al obtener conversaciones',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * ✅ OBTENER CONVERSACIÓN ESPECÍFICA CON TODOS SUS MENSAJES
     */
    public function getConversation(int $id): JsonResponse
    {
        try {
            $userId = Auth::id();

            $chat = MedisearchChat::where('id', $id)
                ->where('user_id', $userId)
                ->with(['questions' => function ($query) {
                    $query->orderBy('created_at', 'asc');
                }])
                ->first();

            if (!$chat) {
                return response()->json([
                    'success' => false,
                    'error' => 'Conversación no encontrada'
                ], 404);
            }

            // Formatear mensajes para el frontend
            $messages = [];
            foreach ($chat->questions as $question) {
                // Mensaje del usuario
                $messages[] = [
                    'id' => 'user_' . $question->id,
                    'type' => 'user',
                    'content' => $question->query,
                    'timestamp' => $question->created_at->toISOString()
                ];

                // Respuesta del asistente
                if ($question->response && isset($question->response['data']['resultados'])) {
                    foreach ($question->response['data']['resultados'] as $resultado) {
                        if ($resultado['tipo'] === 'llm_response') {
                            $pubmedArticles = [];

                            // Buscar artículos en los resultados
                            foreach ($question->response['data']['resultados'] as $res) {
                                if ($res['tipo'] === 'articles' && !empty($res['articulos'])) {
                                    $pubmedArticles = $res['articulos'];
                                    break;
                                }
                            }

                            $messages[] = [
                                'id' => 'ai_' . $question->id,
                                'type' => 'ai',
                                'content' => $resultado['respuesta'],
                                'timestamp' => $question->created_at->toISOString(),
                                'pubmedArticles' => $pubmedArticles,
                                'streaming' => false
                            ];
                            break;
                        }
                    }
                }
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'conversation' => [
                        'id' => $chat->id,
                        'title' => $chat->title,
                        'created_at' => $chat->created_at->toISOString(),
                        'updated_at' => $chat->updated_at->toISOString()
                    ],
                    'messages' => $messages
                ],
                'message' => 'Conversación obtenida correctamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error obteniendo conversación: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'Error al obtener la conversación',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * ✅ CREAR NUEVA CONVERSACIÓN
     */
    public function createConversation(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'title' => 'nullable|string|max:255'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $userId = Auth::id();
            $title = $request->input('title', 'Nuevo Chat ' . now()->format('d/m H:i'));

            $chat = MedisearchChat::create([
                'user_id' => $userId,
                'title' => $title
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $chat->id,
                    'title' => $chat->title,
                    'created_at' => $chat->created_at->toISOString(),
                    'updated_at' => $chat->updated_at->toISOString(),
                    'message_count' => 0
                ],
                'message' => 'Conversación creada correctamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error creando conversación: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'Error al crear conversación',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * ✅ ELIMINAR CONVERSACIÓN
     */
    public function deleteConversation(int $id): JsonResponse
    {
        try {
            $userId = Auth::id();

            $chat = MedisearchChat::where('id', $id)
                ->where('user_id', $userId)
                ->first();

            if (!$chat) {
                return response()->json([
                    'success' => false,
                    'error' => 'Conversación no encontrada'
                ], 404);
            }

            // Eliminar todas las preguntas asociadas (cascade)
            $chat->questions()->delete();

            // Eliminar la conversación
            $chat->delete();

            return response()->json([
                'success' => true,
                'message' => 'Conversación eliminada correctamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error eliminando conversación: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'Error al eliminar conversación',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * ✅ LIMPIAR CONVERSACIÓN (ELIMINAR SOLO LOS MENSAJES)
     */
    public function clearConversation(int $id): JsonResponse
    {
        try {
            $userId = Auth::id();

            $chat = MedisearchChat::where('id', $id)
                ->where('user_id', $userId)
                ->first();

            if (!$chat) {
                return response()->json([
                    'success' => false,
                    'error' => 'Conversación no encontrada'
                ], 404);
            }

            // Eliminar solo las preguntas, mantener la conversación
            $chat->questions()->delete();

            return response()->json([
                'success' => true,
                'message' => 'Conversación limpiada correctamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error limpiando conversación: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'Error al limpiar conversación',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * ✅ ACTUALIZAR TÍTULO DE CONVERSACIÓN
     */
    public function updateConversationTitle(Request $request, int $id): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $userId = Auth::id();
            $newTitle = $request->input('title');

            $chat = MedisearchChat::where('id', $id)
                ->where('user_id', $userId)
                ->first();

            if (!$chat) {
                return response()->json([
                    'success' => false,
                    'error' => 'Conversación no encontrada'
                ], 404);
            }

            $chat->update(['title' => $newTitle]);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $chat->id,
                    'title' => $chat->title,
                    'updated_at' => $chat->updated_at->toISOString()
                ],
                'message' => 'Título actualizado correctamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error actualizando título: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'Error al actualizar título',
                'details' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * Obtener sugerencias de preguntas
     */
    public function getSuggestions()
    {
        try {
            $suggestions = $this->openAI->getMedicalSuggestions();

            return response()->json([
                'success' => true,
                'data' => $suggestions
            ]);

        } catch (\Exception $e) {
            Log::error('Error obteniendo sugerencias: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'Error al obtener sugerencias'
            ], 500);
        }
    }

    // ========================================
    // MÉTODOS PRIVADOS PARA MANEJO DE CACHE
    // ========================================

    private function saveConversationMessage(int $userId, string $conversationId, string $question, string $answer)
    {
        $cacheKey = "medchat_conversation_{$userId}_{$conversationId}";
        $history = Cache::get($cacheKey, []);

        // Agregar pregunta del usuario
        $history[] = [
            'role' => 'user',
            'content' => $question,
            'timestamp' => now()->toISOString()
        ];

        // Agregar respuesta del asistente
        $history[] = [
            'role' => 'assistant',
            'content' => $answer,
            'timestamp' => now()->toISOString()
        ];

        // Mantener solo los últimos 50 mensajes para no exceder límites de memoria
        if (count($history) > 50) {
            $history = array_slice($history, -50);
        }

        // Guardar en cache por 24 horas
        Cache::put($cacheKey, $history, 86400);
    }

    private function getUserConversations(int $userId): array
    {
        $cacheKey = "medchat_conversations_{$userId}";
        return Cache::get($cacheKey, []);
    }

    private function initializeConversation(int $userId, string $conversationId)
    {
        $cacheKey = "medchat_conversation_{$userId}_{$conversationId}";
        Cache::put($cacheKey, [], 86400);

        // Agregar a la lista de conversaciones del usuario
        $conversationsKey = "medchat_conversations_{$userId}";
        $conversations = Cache::get($conversationsKey, []);

        $conversations[] = [
            'id' => $conversationId,
            'title' => 'Nueva conversación',
            'created_at' => now()->toISOString(),
            'updated_at' => now()->toISOString()
        ];

        Cache::put($conversationsKey, $conversations, 86400);
    }

    private function deleteUserConversation(int $userId, string $conversationId)
    {
        // Eliminar conversación específica
        $conversationKey = "medchat_conversation_{$userId}_{$conversationId}";
        Cache::forget($conversationKey);

        // Eliminar de la lista de conversaciones
        $conversationsKey = "medchat_conversations_{$userId}";
        $conversations = Cache::get($conversationsKey, []);

        $conversations = array_filter($conversations, function ($conv) use ($conversationId) {
            return $conv['id'] !== $conversationId;
        });

        Cache::put($conversationsKey, array_values($conversations), 86400);
    }

    private function clearUserConversation(int $userId, string $conversationId)
    {
        $cacheKey = "medchat_conversation_{$userId}_{$conversationId}";
        Cache::put($cacheKey, [], 86400);
    }

    private function updateUserStats(int $userId)
    {
        $statsKey = "medchat_stats_{$userId}";
        $stats = Cache::get($statsKey, [
            'total_questions' => 0,
            'questions_today' => 0,
            'last_question_date' => null
        ]);

        $stats['total_questions']++;

        $today = now()->format('Y-m-d');
        if ($stats['last_question_date'] === $today) {
            $stats['questions_today']++;
        } else {
            $stats['questions_today'] = 1;
            $stats['last_question_date'] = $today;
        }

        Cache::put($statsKey, $stats, 86400);
    }

    /**
     * ✅ OBTENER LÍMITES Y USO ACTUAL DEL USUARIO
     */
    public function getUserLimits(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'error' => 'Usuario no autenticado'
                ], 401);
            }

            // ✅ Usar tu UserLimitService existente
            $userType = $this->limitService->getUserType($user);
            $currentUsage = $this->limitService->getCurrentMonthUsage($user->id);
            $usageSummary = $this->limitService->getUserUsageSummary($user);
            $resetInfo = $this->limitService->getResetInfo();

            // ✅ Calcular mensajes restantes y permisos
            $limits = UserLimitService::LIMITS[$userType];
            $remainingMessages = [];
            $canSendMessage = [];

            foreach (['simple', 'standard', 'deep_research'] as $searchType) {
                $limit = $limits[$searchType];
                $used = $currentUsage[$searchType];

                if ($limit === -1) {
                    $remainingMessages[$searchType] = -1; // Ilimitado
                    $canSendMessage[$searchType] = true;
                } else {
                    $remainingMessages[$searchType] = max(0, $limit - $used);
                    $canSendMessage[$searchType] = $used < $limit;
                }
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'user_type' => $userType,
                    'user_limits' => $limits,
                    'usage_count' => $currentUsage,
                    'remaining_messages' => $remainingMessages,
                    'can_send_message' => $canSendMessage,
                    'reset_info' => $resetInfo,
                    'usage_summary' => $usageSummary,
                    'current_date' => now()->format('Y-m-d')
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error obteniendo límites de usuario:', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * ✅ VERIFICAR SI PUEDE ENVIAR MENSAJE
     */
    public function canSendMessage(Request $request): JsonResponse
    {
        $request->validate([
            'search_type' => 'required|string|in:simple,standard,deep_research'
        ]);

        try {
            $user = Auth::user();
            $searchType = $request->search_type;

            // ✅ Usar tu UserLimitService existente
            $canSearch = $this->limitService->canUserSearch($user, $searchType);

            return response()->json([
                'success' => true,
                'data' => [
                    'can_send' => $canSearch['allowed'],
                    'remaining' => $canSearch['remaining'],
                    'used' => $canSearch['current_usage'] ?? 0,
                    'limit' => $canSearch['limit'],
                    'search_type' => $searchType,
                    'user_type' => $canSearch['user_type'],
                    'message' => $canSearch['message'],
                    'reset_info' => $canSearch['reset_info']
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error verificando límites:', [
                'user_id' => Auth::id(),
                'search_type' => $searchType,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * ✅ OBTENER ESTADÍSTICAS DE USO DEL USUARIO
     */
    public function getUserStats(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();

            // ✅ Usar tu UserLimitService existente
            $stats = $this->limitService->getUserStats($user);
            $usageHistory = $this->limitService->getUserUsageHistory($user, 6);
            $upgradeInfo = $this->limitService->shouldSuggestUpgrade($user);

            return response()->json([
                'success' => true,
                'data' => [
                    'stats' => $stats,
                    'usage_history' => $usageHistory,
                    'upgrade_suggestion' => $upgradeInfo
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error obteniendo estadísticas:', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Error interno del servidor'
            ], 500);
        }
    }
}
