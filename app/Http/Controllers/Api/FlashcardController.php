<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Api\Flashcard\CategoryService;
use App\Services\Api\Flashcard\FlashcardService;
use App\Services\Api\OpenAI\Flashcard as OpenAI;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Log;

class FlashcardController extends Controller
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

    // DESPUÉS (correcto):
    public function index()
    {
        return response()->json([
            'success' => true,
            'data' => [
                'flashcards' => $this->flashcard->index(Auth::id())  // ← Directamente los datos
            ]
        ]);
    }


    public function show($flashcardId)
    {

    }

    public function store(Request $request)
    {
        try {
            $userId = Auth::id();

            $request->validate([
                'pregunta' => [
                    'required',
                    'string',
                    'max:2000',
                    'min:5'
                ],
                'respuesta' => [
                    'required',
                    'string',
                    'max:2000',
                    'min:5'
                ],
                'url' => [
                    'nullable',
                    'url',
                    'max:500'
                ],
                'url_respuesta' => [
                    'nullable',
                    'url',
                    'max:500'
                ],
                'imagen' => [
                    'nullable',
                    'image',
                    'mimes:jpeg,png,jpg,gif,webp',
                    'max:5120' // 5MB máximo
                ],
                'imagen_respuesta' => [
                    'nullable',
                    'image',
                    'mimes:jpeg,png,jpg,gif,webp',
                    'max:5120' // 5MB máximo
                ],
                'categorias' => [
                    'nullable',
                    'json'
                ]
            ], [
                // Mensajes personalizados
                'pregunta.required' => 'La pregunta es obligatoria.',
                'pregunta.min' => 'La pregunta debe tener al menos 5 caracteres.',
                'pregunta.max' => 'La pregunta no puede tener más de 2000 caracteres.',

                'respuesta.required' => 'La respuesta es obligatoria.',
                'respuesta.min' => 'La respuesta debe tener al menos 5 caracteres.',
                'respuesta.max' => 'La respuesta no puede tener más de 2000 caracteres.',

                'url.url' => 'La URL de la pregunta debe ser una URL válida.',
                'url.max' => 'La URL de la pregunta no puede tener más de 500 caracteres.',

                'url_respuesta.url' => 'La URL de la respuesta debe ser una URL válida.',
                'url_respuesta.max' => 'La URL de la respuesta no puede tener más de 500 caracteres.',

                'imagen.image' => 'El archivo de imagen de pregunta debe ser una imagen válida.',
                'imagen.mimes' => 'La imagen de pregunta debe ser de tipo: jpeg, png, jpg, gif, webp.',
                'imagen.max' => 'La imagen de pregunta no puede ser mayor a 5MB.',

                'imagen_respuesta.image' => 'El archivo de imagen de respuesta debe ser una imagen válida.',
                'imagen_respuesta.mimes' => 'La imagen de respuesta debe ser de tipo: jpeg, png, jpg, gif, webp.',
                'imagen_respuesta.max' => 'La imagen de respuesta no puede ser mayor a 5MB.',

                'categorias.json' => 'Las categorías deben tener un formato válido.'
            ]);
            $categoriasArray = [];
            if ($request->filled('categorias')) {
                $categoriasDecoded = json_decode($request->categorias, true); // ← true para array asociativo
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
            // Validación adicional: no puede tener URL e imagen al mismo tiempo
            if ($request->filled('url') && $request->hasFile('imagen')) {
                return response()->json([
                    'success' => false,
                    'errors' => [
                        'imagen' => ['No puedes subir una imagen y una URL al mismo tiempo para la pregunta.']
                    ]
                ], 422);
            }

            if ($request->filled('url_respuesta') && $request->hasFile('imagen_respuesta')) {
                return response()->json([
                    'success' => false,
                    'errors' => [
                        'imagen_respuesta' => ['No puedes subir una imagen y una URL al mismo tiempo para la respuesta.']
                    ]
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
                'pregunta' => [
                    'required',
                    'string',
                    'max:2000',
                    'min:5'
                ],
                'respuesta' => [
                    'required',
                    'string',
                    'max:2000',
                    'min:5'
                ]
            ], [
                // Mensajes personalizados
                'pregunta.required' => 'La pregunta es obligatoria.',
                'pregunta.min' => 'La pregunta debe tener al menos 5 caracteres.',
                'pregunta.max' => 'La pregunta no puede tener más de 2000 caracteres.',

                'respuesta.required' => 'La respuesta es obligatoria.',
                'respuesta.min' => 'La respuesta debe tener al menos 5 caracteres.',
                'respuesta.max' => 'La respuesta no puede tener más de 2000 caracteres.',
            ]);

            $data = [
                'pregunta' => $request->pregunta,
                'respuesta' => $request->respuesta,
                'categorias' => [],
            ];

            $response = $this->flashcard->update($id, $data, $userId);
            return response()->json([
                'success' => true,
                'message' => 'Flashcard Actualizada exitosamente',
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

        $this->flashcard->update($id, $request->all(), Auth::id());

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
        }

    }

    public function categoryIndex()
    {
        try {
            return response()->json([
                'success' => true,
                'categories' => $this->category->index(Auth::id())
            ]);
        } catch (\Exception $e) {
            Log::error('Error al Obtener Las Categorias de Flashcard en el controlador... revisar Funcion index del Controlller/Api/Flashcard ' . $e->getMessage());
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
                'nombre' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('fc_categories')->where(function ($query) use ($userId) {
                        return $query->where('user_id', $userId);
                    }),
                ],
            ], [
                // Mensajes personalizados
                'nombre.required' => 'El nombre de la categoría es obligatorio.',
                'nombre.string' => 'El nombre de la categoría debe ser una cadena de texto.',
                'nombre.max' => 'El nombre de la categoría no puede tener más de 255 caracteres.',
                'nombre.unique' => 'Ya tienes una categoría con ese nombre. Por favor elige otro.',
            ]);

            $name = $request->nombre;

            $category = $this->category->store($name, $userId);

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
            // Capturar errores de validación
            Log::error('Error al Validar La Categoria de Flashcard en el controlador... revisar Funcion categoryStrore del Controlller/Api/Flashcard ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            // Capturar otros errores
            Log::error('Error al Crear La Categoria de Flashcard en el controlador... revisar Funcion strore del Controlller/Api/Flashcard ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Error interno del servidor'
            ], 500);
        }
    }

    public function generateAI(Request $request)
    {
        dd($request->all());
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
//            if(){} AQUIIII TENGO QUE Validar Roles de Pago y demas.....
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
        // ✅ VALIDACIÓN CORREGIDA
        $request->validate([
            'flashcard_ids' => 'required|array|min:1',
            'flashcard_ids.*' => 'integer|exists:fc_cards,id',
            'total_selected' => 'required|integer|min:1'
        ]);
        $flashcards = $request->all();
        $this->flashcard->setGame($flashcards);
        dump($flashcards);
        return response()->json([
            'success' => true,
            'message' => 'Juego iniciado exitosamente',
            'data' => [
                'flashcards' => $flashcards
            ]
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
            \Log::error('Error en getGame: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'Error interno del servidor'
            ], 500);
        }
    }
}
