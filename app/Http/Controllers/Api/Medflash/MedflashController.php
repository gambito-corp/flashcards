<?php

namespace App\Http\Controllers\Api\Medflash;

use App\Http\Controllers\Controller;
use App\Services\Api\Flashcard\CategoryService;
use App\Services\Api\Flashcard\FlashcardService;
use App\Services\Api\OpenAI\Flashcard as OpenAI;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class MedflashController extends Controller
{
    protected CategoryService $category;
    protected FlashcardService $flashcard;
    protected OpenAI $openAI;

    public function __construct(
        CategoryService  $category,
        OpenAI           $openAI,
        FlashcardService $flashcard
    )
    {
        $this->middleware('auth:sanctum');
        $this->category = $category;
        $this->openAI = $openAI;
        $this->flashcard = $flashcard;
    }

    // ========================================
    // FLASHCARDS CRUD
    // ========================================

    // En tu FlashcardController.php
    public function index(Request $request)
    {
        try {
            $userId = Auth::id();
            $page = $request->get('page', 1);
            $perPage = $request->get('per_page', 10);

            // Obtener filtros del request
            $filters = [
                'categoria' => $request->get('categoria'),
                'search' => $request->get('search'),
                'sin_categoria' => $request->get('sin_categoria') // ✅ NUEVO FILTRO
            ];

            $flashcards = $this->flashcard->index($userId, $filters, $page, $perPage);

            return response()->json([
                'success' => true,
                'data' => $flashcards
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener flashcards: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Error interno del servidor'
            ], 500);
        }
    }


    public function show($flashcardId)
    {
        // Implementar según necesidad
    }

    public function store(Request $request)
    {
        try {
            $userId = Auth::id();

            $request->validate([
                'pregunta' => ['required', 'string', 'max:2000', 'min:5'],
                'respuesta' => ['required', 'string', 'max:2000', 'min:5'],
                'url' => ['nullable', 'url', 'max:500'],
                'url_respuesta' => ['nullable', 'url', 'max:500'],
                'imagen' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:5120'],
                'imagen_respuesta' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:5120'],
                'categorias' => ['nullable', 'json']
            ]);

            $categoriasArray = [];
            if ($request->filled('categorias')) {
                $categoriasDecoded = json_decode($request->categorias, true);
                if (is_array($categoriasDecoded)) {
                    $categoriasArray = $categoriasDecoded;
                }
            }

            $data = [
                'pregunta' => $request->pregunta,
                'respuesta' => $request->respuesta,
                'url' => $request->url,
                'url_respuesta' => $request->url_respuesta,
                'imagen' => $request->imagen,
                'imagen_respuesta' => $request->imagen_respuesta,
                'categorias' => $categoriasArray,
            ];

            // Validaciones adicionales
            if ($request->filled('url') && $request->hasFile('imagen')) {
                return response()->json([
                    'success' => false,
                    'errors' => ['imagen' => ['No puedes subir una imagen y una URL al mismo tiempo para la pregunta.']]
                ], 422);
            }

            if ($request->filled('url_respuesta') && $request->hasFile('imagen_respuesta')) {
                return response()->json([
                    'success' => false,
                    'errors' => ['imagen_respuesta' => ['No puedes subir una imagen y una URL al mismo tiempo para la respuesta.']]
                ], 422);
            }

            $response = $this->flashcard->store($data, $userId);
            return response()->json([
                'success' => true,
                'message' => 'Flashcard creada exitosamente',
                'data' => $response
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error al crear flashcard: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Error interno del servidor'
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $userId = Auth::id();

            $request->validate([
                'pregunta' => ['required', 'string', 'max:2000', 'min:5'],
                'respuesta' => ['required', 'string', 'max:2000', 'min:5'],
                'url' => ['nullable', 'url', 'max:500'],
                'url_respuesta' => ['nullable', 'url', 'max:500'],
                'imagen' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:5120'],
                'imagen_respuesta' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:5120'],
                'categorias' => ['nullable', 'json'],
                // ✅ NUEVOS CAMPOS PARA CONTROL DE IMÁGENES
                'remove_imagen' => ['nullable', 'boolean'],
                'remove_imagen_respuesta' => ['nullable', 'boolean']
            ]);

            // ✅ PROCESAR CATEGORÍAS
            $categoriasArray = [];
            if ($request->filled('categorias')) {
                $categoriasDecoded = json_decode($request->categorias, true);
                if (is_array($categoriasDecoded)) {
                    $categoriasArray = $categoriasDecoded;
                }
            }

            $data = [
                'pregunta' => $request->pregunta,
                'respuesta' => $request->respuesta,
                'url' => $request->url,
                'url_respuesta' => $request->url_respuesta,
                'imagen' => $request->imagen,
                'imagen_respuesta' => $request->imagen_respuesta,
                'categorias' => $categoriasArray,
                'remove_imagen' => $request->boolean('remove_imagen'),
                'remove_imagen_respuesta' => $request->boolean('remove_imagen_respuesta')
            ];

            // ✅ VALIDACIONES ADICIONALES
            if ($request->filled('url') && $request->hasFile('imagen')) {
                return response()->json([
                    'success' => false,
                    'errors' => ['imagen' => ['No puedes subir una imagen y una URL al mismo tiempo para la pregunta.']]
                ], 422);
            }

            if ($request->filled('url_respuesta') && $request->hasFile('imagen_respuesta')) {
                return response()->json([
                    'success' => false,
                    'errors' => ['imagen_respuesta' => ['No puedes subir una imagen y una URL al mismo tiempo para la respuesta.']]
                ], 422);
            }

            $response = $this->flashcard->update($id, $data, $userId);

            return response()->json([
                'success' => true,
                'message' => 'Flashcard actualizada exitosamente',
                'data' => $response
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error al actualizar flashcard: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Error interno del servidor'
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $this->flashcard->destroy($id, Auth::id());
            return response()->json([
                'success' => true,
                'message' => 'Flashcard eliminada exitosamente'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error al eliminar flashcard: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Eliminar todas las flashcards de una categoría específica
     */
    public function deleteByCategory(Request $request)
    {
        try {
            $userId = Auth::id();
            $user = Auth::user();

            $request->validate([
                'category_id' => 'nullable|integer',
                'password' => 'nullable|string'
            ]);

            // ✅ CORREGIR: Verificar roles correctamente
            $userRoles = $user->roles ?? [];

            // Si roles es una Collection, convertir a array
            if ($userRoles instanceof \Illuminate\Database\Eloquent\Collection) {
                $userRoles = $userRoles->pluck('name')->toArray();
            }

            // Si roles es un array JSON (como en tu caso), usarlo directamente
            if (is_string($userRoles)) {
                $userRoles = json_decode($userRoles, true) ?? [];
            }

            // Verificar contraseña si no es root
            if (!in_array('root', $userRoles) && $request->password) {
                if (!Hash::check($request->password, $user->password)) {
                    return response()->json([
                        'success' => false,
                        'error' => 'Contraseña incorrecta'
                    ], 401);
                }
            }

            $deletedCount = $this->flashcard->deleteByCategory($userId, $request->category_id);

            return response()->json([
                'success' => true,
                'data' => ['deleted_count' => $deletedCount],
                'message' => "{$deletedCount} flashcards eliminadas exitosamente"
            ]);

        } catch (\Exception $e) {
            Log::error('Error al eliminar flashcards por categoría: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Eliminar todas las flashcards del usuario
     */
    public function deleteAll(Request $request)
    {
        try {
            $userId = Auth::id();
            $user = Auth::user();

            $request->validate([
                'password' => 'nullable|string'
            ]);

            // ✅ CORREGIR: Verificar roles correctamente
            $userRoles = $user->roles ?? [];

            // Si roles es una Collection, convertir a array
            if ($userRoles instanceof \Illuminate\Database\Eloquent\Collection) {
                $userRoles = $userRoles->pluck('name')->toArray();
            }

            // Si roles es un array JSON (como en tu caso), usarlo directamente
            if (is_string($userRoles)) {
                $userRoles = json_decode($userRoles, true) ?? [];
            }

            // Verificar contraseña si no es root
            if (!in_array('root', $userRoles) && $request->password) {
                if (!Hash::check($request->password, $user->password)) {
                    return response()->json([
                        'success' => false,
                        'error' => 'Contraseña incorrecta'
                    ], 401);
                }
            }

            $deletedCount = $this->flashcard->deleteAll($userId);

            return response()->json([
                'success' => true,
                'data' => ['deleted_count' => $deletedCount],
                'message' => "{$deletedCount} flashcards eliminadas exitosamente"
            ]);

        } catch (\Exception $e) {
            Log::error('Error al eliminar todas las flashcards: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Error interno del servidor'
            ], 500);
        }
    }


    // ========================================
    // CATEGORÍAS CRUD
    // ========================================

    public function categoryIndex(Request $request)
    {
        try {
            $page = $request->get('page', 1);
            $perPage = $request->get('per_page', 10);

            $categories = $this->category->index(Auth::id(), $page, $perPage);

            return response()->json([
                'success' => true,
                'data' => $categories  // ← Cambiar de 'categories' a 'data'
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener categorías: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Error interno del servidor'
            ], 500);
        }
    }

    public function categorySearch(Request $request)
    {

        try {
            $userId = Auth::id();
            $searchTerm = $request->get('search', '');
            $page = $request->get('page', 1);
            $perPage = $request->get('per_page', 10);

            $query = $this->category->search($userId, $searchTerm);
            // Si hay término de búsqueda, filtrar
            if (!empty($searchTerm)) {
                $query->where('nombre', 'LIKE', "%{$searchTerm}%");
            }

            $categories = $query->paginate($perPage, ['*'], 'page', $page);

            return response()->json([
                'success' => true,
                'data' => $categories
            ]);
        } catch (\Exception $e) {
            Log::error('Error en búsqueda de categorías: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Error interno del servidor'
            ], 500);
        }
    }

    public function categoryStore(Request $request)
    {
        try {
            $userId = Auth::id();

            $request->validate([
                'name' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('fc_categories', 'nombre')->where(function ($query) use ($userId) {
                        return $query->where('user_id', $userId);
                    }),
                ],
            ], [
                'name.required' => 'El nombre de la categoría es obligatorio.',
                'name.string' => 'El nombre de la categoría debe ser una cadena de texto.',
                'name.max' => 'El nombre de la categoría no puede tener más de 255 caracteres.',
                'name.unique' => 'Ya tienes una categoría con ese nombre. Por favor elige otro.',
            ]);

            $category = $this->category->store($request->name, $userId);

            return response()->json([
                'success' => true,
                'message' => 'Categoría creada exitosamente',
                'data' => [
                    'id' => $category->id,
                    'nombre' => $category->nombre,
                    'user_id' => $category->user_id,
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Error de validación: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error al crear categoría: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Error interno del servidor'
            ], 500);
        }
    }

    public function categoryUpdate(Request $request, $id)
    {
        try {
            $userId = Auth::id();

            $request->validate([
                'name' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('fc_categories', 'nombre')->where(function ($query) use ($userId, $id) {
                        return $query->where('user_id', $userId)->where('id', '!=', $id);
                    }),
                ],
            ], [
                'name.required' => 'El nombre de la categoría es obligatorio.',
                'name.string' => 'El nombre de la categoría debe ser una cadena de texto.',
                'name.max' => 'El nombre de la categoría no puede tener más de 255 caracteres.',
                'name.unique' => 'Ya tienes una categoría con ese nombre. Por favor elige otro.',
            ]);

            $category = $this->category->update($id, $request->name, $userId);

            return response()->json([
                'success' => true,
                'message' => 'Categoría actualizada exitosamente',
                'data' => [
                    'id' => $category->id,
                    'nombre' => $category->nombre,
                    'user_id' => $category->user_id,
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error al actualizar categoría: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Error interno del servidor'
            ], 500);
        }
    }

    public function categoryDestroy($id)
    {
        try {
            $this->category->destroy($id, Auth::id());
            return response()->json([
                'success' => true,
                'message' => 'Categoría eliminada exitosamente'
            ]);
        } catch (\Exception $e) {
            Log::error('Error al eliminar categoría: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Error interno del servidor'
            ], 500);
        }
    }

    public function categoryBulkDestroy(Request $request)
    {
        try {
            $request->validate([
                'category_ids' => 'required|array|min:1',
                'category_ids.*' => 'integer|exists:fc_categories,id'
            ]);

            $deletedCount = $this->category->bulkDestroy($request->category_ids, Auth::id());

            return response()->json([
                'success' => true,
                'message' => "{$deletedCount} categorías eliminadas exitosamente"
            ]);
        } catch (\Exception $e) {
            Log::error('Error al eliminar categorías masivamente: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Error interno del servidor'
            ], 500);
        }
    }

    public function categoryDestroyAll()
    {
        try {
            $deletedCount = $this->category->destroyAll(Auth::id());

            return response()->json([
                'success' => true,
                'message' => "{$deletedCount} categorías eliminadas exitosamente"
            ]);
        } catch (\Exception $e) {
            Log::error('Error al eliminar todas las categorías: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Error interno del servidor'
            ], 500);
        }
    }

    public function categoriesWithCount()
    {
        try {
            $userId = Auth::id();

            $categories = $this->category->getAllWithCount($userId);

            return response()->json([
                'success' => true,
                'data' => $categories
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener categorías con conteo: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Error interno del servidor'
            ], 500);
        }
    }

    // ========================================
    // IA Y JUEGOS
    // ========================================

    public function generateAI(Request $request)
    {
        try {
            $userId = Auth::id();

            $request->validate([
                'type' => 'required|in:pregunta,respuesta',
                'prompt' => 'nullable|string|max:1000',
                'current_text' => 'nullable|string|max:2000'
            ]);

            $type = $request->type ?? 'pregunta';
            $prompt = $request->prompt ?? 'a';
            $currentText = $request->current_text ?? 'a';

            $generated_text = $this->openAI->generate($userId, $type, $prompt, $currentText);

            return response()->json([
                'success' => true,
                'data' => [
                    'type' => $type,
                    'prompt' => $prompt,
                    'current_text' => $currentText,
                    'generated_text' => $generated_text,
                ]
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error en generación de IA: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Error interno del servidor'
            ], 500);
        }
    }

    public function setGame(Request $request)
    {
        $request->validate([
            'flashcard_ids' => 'required|array|min:1',
            'flashcard_ids.*' => 'integer|exists:fc_cards,id',
            'total_selected' => 'required|integer|min:1'
        ]);

        $flashcards = $request->all();
        $this->flashcard->setGame($flashcards);

        return response()->json([
            'success' => true,
            'message' => 'Juego iniciado exitosamente',
            'data' => ['flashcards' => $flashcards]
        ]);
    }

    public function getGame()
    {
        try {
            $gameData = $this->flashcard->getGame();
            if (!$gameData) {
                return response()->json([
                    'success' => false,
                    'error' => 'No hay juego activo. Inicia un nuevo juego.'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Datos del juego obtenidos exitosamente',
                'data' => $gameData
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error en getGame: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Error interno del servidor'
            ], 500);
        }
    }

    // Agregar estos métodos al final de MedflashController.php

    // Agregar estos métodos al final de tu MedflashController.php

    public function incrementError($id)
    {
        try {
            $userId = Auth::id();

            $flashcard = Flashcard::where('id', $id)
                ->where('user_id', $userId)
                ->first();

            if (!$flashcard) {
                return response()->json([
                    'success' => false,
                    'error' => 'Flashcard no encontrada'
                ], 404);
            }

            $flashcard->increment('errors');

            return response()->json([
                'success' => true,
                'message' => 'Error incrementado exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error incrementando errores: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Error interno del servidor'
            ], 500);
        }
    }

    public function finishGame(Request $request)
    {
        try {
            $request->validate([
                'correct' => 'required|integer|min:0',
                'incorrect' => 'required|integer|min:0',
                'total' => 'required|integer|min:1',
                'flashcard_ids' => 'required|array|min:1'
            ]);

            // Limpiar cache del juego
            $this->flashcard->clearGame();

            return response()->json([
                'success' => true,
                'message' => 'Juego finalizado exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error finalizando juego: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Error interno del servidor'
            ], 500);
        }
    }


}
