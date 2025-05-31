<?php

namespace App\Services\Api\Flashcard;

use App\Models\FcCard as Flashcard;
use App\Models\FcCardCategory;
use App\Models\FcCardsGroupCard;
use App\Models\FcCategory;
use App\Services\Api\Commons\ImageService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FlashcardService
{
    protected ImageService $imageService;

    public function __construct(ImageService $imageService)
    {
        $this->imageService = $imageService;
    }

    public function index($userId, $filters = [])
    {
        try {
            $query = Flashcard::query()->with('categories')->where('user_id', $userId)
                ->orderBy('errors', 'desc');

            // Filtros opcionales
            if (isset($filters['categoria']) && !empty($filters['categoria'])) {
                $query->whereHas('categories', function ($q) use ($filters) {
                    $q->where('fc_category_id', $filters['categoria']);
                });
            }

            if (isset($filters['search']) && !empty($filters['search'])) {
                $query->where(function ($q) use ($filters) {
                    $q->where('pregunta', 'LIKE', '%' . $filters['search'] . '%')
                        ->orWhere('respuesta', 'LIKE', '%' . $filters['search'] . '%');
                });
            }

            $flashcards = $query->get();

            // ← NUEVA LÓGICA: Crear una entrada por cada categoría
            $expandedFlashcards = collect();

            foreach ($flashcards as $flashcard) {
                if ($flashcard->categories->isNotEmpty()) {
                    // Para cada categoría, crear una entrada separada
                    foreach ($flashcard->categories as $category) {
                        $expandedFlashcards->push([
                            'id' => $flashcard->id,
                            'pregunta' => $flashcard->pregunta,
                            'respuesta' => $flashcard->respuesta,
                            'imagen' => $flashcard->imagen ? asset('storage/' . $flashcard->imagen) : null,
                            'imagen_respuesta' => $flashcard->imagen_respuesta ? asset('storage/' . $flashcard->imagen_respuesta) : null,
                            'url' => $flashcard->url,
                            'url_respuesta' => $flashcard->url_respuesta,
                            'user_id' => $flashcard->user_id,
                            'errors' => $flashcard->errors ?? 0,
                            'created_at' => $flashcard->created_at,
                            'updated_at' => $flashcard->updated_at,

                            // ← CATEGORÍA ESPECÍFICA PARA ESTA ENTRADA
                            'category' => $category->nombre,
                            'category_id' => $category->id,
                            'categories' => $flashcard->categories->map(function ($cat) {
                                return [
                                    'id' => $cat->id,
                                    'name' => $cat->nombre,
                                    'user_id' => $cat->user_id
                                ];
                            })
                        ]);
                    }
                } else {
                    // Sin categoría
                    $expandedFlashcards->push([
                        'id' => $flashcard->id,
                        'pregunta' => $flashcard->pregunta,
                        'respuesta' => $flashcard->respuesta,
                        'imagen' => $flashcard->imagen ? asset('storage/' . $flashcard->imagen) : null,
                        'imagen_respuesta' => $flashcard->imagen_respuesta ? asset('storage/' . $flashcard->imagen_respuesta) : null,
                        'url' => $flashcard->url,
                        'url_respuesta' => $flashcard->url_respuesta,
                        'user_id' => $flashcard->user_id,
                        'errors' => $flashcard->errors ?? 0,
                        'created_at' => $flashcard->created_at,
                        'updated_at' => $flashcard->updated_at,
                        'category' => 'Sin Categoría',
                        'category_id' => null,
                        'categories' => []
                    ]);
                }
            }

            return $expandedFlashcards;

        } catch (\Exception $e) {
            Log::error('Error obteniendo flashcards: ' . $e->getMessage());
            throw new \Exception('Error al obtener las flashcards');
        }
    }


    public function show($id, $userId)
    {
        try {
            $flashcard = Flashcard::where('id', $id)
                ->where('user_id', $userId)
                ->where('activo', 1)
                ->with('categories')
                ->first();

            if (!$flashcard) {
                throw new \Exception('Flashcard no encontrada');
            }

            return $flashcard;

        } catch (\Exception $e) {
            Log::error('Error obteniendo flashcard: ' . $e->getMessage());
            throw $e;
        }
    }

    public function store($data, $userId)
    {
        $imagenesSubidas = [];
        $flashcard = null;

        try {
            DB::beginTransaction();

            $tieneImagenPregunta = isset($data['imagen']) && $data['imagen'] !== null;
            $tieneImagenRespuesta = isset($data['imagen_respuesta']) && $data['imagen_respuesta'] !== null;

            // Crear flashcard
            $flashcard = new Flashcard();
            $flashcard->user_id = $userId;
            $flashcard->pregunta = $data['pregunta'];
            $flashcard->respuesta = $data['respuesta'];
            $flashcard->url = $data['url'];
            $flashcard->url_respuesta = $data['url_respuesta'];
            $flashcard->errors = 0;

            // Subir imágenes
            if ($tieneImagenPregunta) {
                $imagenPregunta = $this->subirImagen($data['imagen'], 'pregunta', $userId);
                $imagenesSubidas['pregunta'] = $imagenPregunta;
                $flashcard->imagen = $imagenPregunta;
            }

            if ($tieneImagenRespuesta) {
                $imagenRespuesta = $this->subirImagen($data['imagen_respuesta'], 'respuesta', $userId);
                $imagenesSubidas['respuesta'] = $imagenRespuesta;
                $flashcard->imagen_respuesta = $imagenRespuesta;
            }

            // Guardar en base de datos
            $flashcard->save();

            // Procesar categorías usando attach
            if (!empty($data['categorias'])) {
                $this->procesarCategorias($flashcard, $data['categorias'], $userId);
            }

            DB::commit();

            return $this->formatearRespuesta($flashcard, $data['categorias'] ?? []);

        } catch (\Exception $e) {
            DB::rollBack();

            // Cleanup de imágenes
            $this->cleanupImages($imagenesSubidas);

            Log::error('Error completo en store: ' . $e->getMessage());
            throw $e;
        }
    }

    public function update($id, $data, $userId)
    {
        try {
            DB::beginTransaction();

            // Buscar flashcard existente
            $flashcard = Flashcard::where('id', $id)
                ->where('user_id', $userId)
                ->first();

            if (!$flashcard) {
                throw new \Exception('Flashcard no encontrada');
            }
            $flashcard->pregunta = $data['pregunta'];
            $flashcard->respuesta = $data['respuesta'];
            $flashcard->save();
            DB::commit();
            return $this->formatearRespuesta($flashcard, []);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en update de flashcard: ' . $e->getMessage());
            throw $e;
        }
    }

    public function destroy($id, $userId)
    {
        try {
            DB::beginTransaction();

            $flashcard = Flashcard::where('id', $id)
                ->where('user_id', $userId)
                ->first();
            if (!$flashcard) {
                throw new \Exception('Flashcard no encontrada');
            }

            $CategoriaCard = FcCardCategory::where('fc_card_id', $id)->get();
            $CategoriaCard->each(function ($item) {
                $item->delete();
            });

            $fc_cards_groupcard = FcCardsGroupCard::where('fc_card_id', $id)->get();
            $fc_cards_groupcard->each(function ($item) {
                $item->delete();
            });
            $flashcard->delete();
            DB::commit();

            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error eliminando flashcard: ' . $e->getMessage());
            throw $e;
        }
    }

    private function subirImagen($archivo, $tipo, $userId)
    {
        try {
            $path = $this->imageService->setName($archivo, $tipo, $userId);
            $name = time() . "_{$tipo}." . $archivo->extension();
            return $this->imageService->upload($archivo, $path, $name);
        } catch (\Exception $e) {
            throw new \Exception("Error subiendo imagen de {$tipo}: " . $e->getMessage());
        }
    }

    /**
     * Limpiar imágenes subidas a S3
     */
    private function cleanupImages(array $imagenesSubidas)
    {
        foreach ($imagenesSubidas as $tipo => $nombreImagen) {
            try {
                if ($nombreImagen) {
                    $this->imageService->delete($nombreImagen);
                    Log::info("Imagen {$tipo} eliminada de S3: {$nombreImagen}");
                }
            } catch (\Exception $e) {
                Log::error("Error eliminando imagen {$tipo} de S3: " . $e->getMessage());
            }
        }
    }

    public function setGame(array $flashcards)
    {
        $cacheKey = 'selected_cards_' . Auth()->user()->id;

        // ✅ GUARDAR DATOS COMPLETOS DEL JUEGO EN CACHE
        $gameData = [
            'game_id' => uniqid('game_'),
            'user_id' => Auth()->user()->id,
            'flashcard_ids' => $flashcards['flashcard_ids'],
            'total_selected' => $flashcards['total_selected'],
            'started_at' => now(),
            'status' => 'active'
        ];

        // ✅ GUARDAR EN CACHE CON TTL (Time To Live)
        Cache::put($cacheKey, $gameData, now()->addHours(2)); // Expira en 2 horas

        return $gameData;
    }

    public function getGame()
    {
        $cacheKey = 'selected_cards_' . Auth()->user()->id;

        // ✅ OBTENER DATOS DEL CACHE
        $gameData = Cache::get($cacheKey);

        if (!$gameData) {
            return null;
        }

        // ✅ OBTENER LAS FLASHCARDS REALES
        $flashcardIds = $gameData['flashcard_ids'] ?? [];

        if (empty($flashcardIds)) {
            return null;
        }

        $flashcards = Flashcard::with('categories')
            ->whereIn('id', $flashcardIds)
            ->where('user_id', Auth()->user()->id)
            ->get();

        // ✅ PREPARAR DATOS COMPLETOS
        return [
            'game_session' => $gameData,
            'flashcards' => $flashcards->map(function ($flashcard) {
                return [
                    'id' => $flashcard->id,
                    'pregunta' => $flashcard->pregunta,
                    'respuesta' => $flashcard->respuesta,
                    'imagen' => $flashcard->imagen,
                    'imagen_respuesta' => $flashcard->imagen_respuesta,
                    'url' => $flashcard->url,
                    'url_respuesta' => $flashcard->url_respuesta,
                    'categories' => $flashcard->categories->pluck('nombre')->toArray()
                ];
            }),
            'total_flashcards' => $flashcards->count()
        ];
    }

// ✅ MÉTODO ADICIONAL PARA LIMPIAR CACHE DEL JUEGO
    public function clearGame()
    {
        $cacheKey = 'selected_cards_' . Auth()->user()->id;
        Cache::forget($cacheKey);
    }

// ✅ MÉTODO PARA VERIFICAR SI HAY JUEGO ACTIVO
    public function hasActiveGame()
    {
        $cacheKey = 'selected_cards_' . Auth()->user()->id;
        return Cache::has($cacheKey);
    }

// ✅ MÉTODO PARA EXTENDER TIEMPO DEL JUEGO
    public function extendGame()
    {
        $cacheKey = 'selected_cards_' . Auth()->user()->id;
        $gameData = Cache::get($cacheKey);

        if ($gameData) {
            // Extender por 2 horas más
            Cache::put($cacheKey, $gameData, now()->addHours(2));
            return true;
        }

        return false;
    }

    /**
     * Eliminar imágenes anteriores después de actualización exitosa
     */
    private function eliminarImagenesAnteriores(array $imagenesEliminadas)
    {
        foreach ($imagenesEliminadas as $nombreImagen) {
            try {
                if ($nombreImagen) {
                    $this->imageService->delete($nombreImagen);
                    Log::info("Imagen anterior eliminada de S3: {$nombreImagen}");
                }
            } catch (\Exception $e) {
                Log::error("Error eliminando imagen anterior de S3: " . $e->getMessage());
            }
        }
    }

    /**
     * Procesar categorías usando attach - Solo para crear
     */
    private function procesarCategorias(Flashcard $flashcard, array $categorias, $userId)
    {
        try {
            // Validar que las categorías pertenezcan al usuario
            $categoriasValidas = FcCategory::where('user_id', $userId)
                ->whereIn('id', $categorias)
                ->pluck('id')
                ->toArray();

            if (!empty($categoriasValidas)) {
                // Usar attach para asociar categorías
                $flashcard->categories()->attach($categoriasValidas);
            }

        } catch (\Exception $e) {
            Log::error('Error procesando categorías: ' . $e->getMessage());
            throw new \Exception('Error al asociar categorías: ' . $e->getMessage());
        }
    }

    /**
     * Actualizar categorías usando sync - Para actualizar
     */
    private function actualizarCategorias(Flashcard $flashcard, array $categorias, $userId)
    {
        try {
            // Validar que las categorías pertenezcan al usuario
            $categoriasValidas = FcCategory::where('user_id', $userId)
                ->whereIn('id', $categorias)
                ->pluck('id')
                ->toArray();

            // Usar sync para reemplazar todas las categorías
            $flashcard->categories()->sync($categoriasValidas);

        } catch (\Exception $e) {
            Log::error('Error actualizando categorías: ' . $e->getMessage());
            throw new \Exception('Error al actualizar categorías: ' . $e->getMessage());
        }
    }

    /**
     * Formatear respuesta para el frontend
     */
    private function formatearRespuesta($flashcard, array $categorias = [])
    {
        // Cargar categorías si no están cargadas
        if (!$flashcard->relationLoaded('categories')) {
            $flashcard->load('categories');
        }

        return [
            'id' => $flashcard->id,
            'pregunta' => $flashcard->pregunta,
            'respuesta' => $flashcard->respuesta,
            'url' => $flashcard->url,
            'url_respuesta' => $flashcard->url_respuesta,
            'imagen' => $flashcard->imagen,
            'imagen_respuesta' => $flashcard->imagen_respuesta,
            'errors' => $flashcard->errors,
            'categorias' => $flashcard->categories->pluck('id')->toArray(),
            'categorias_nombres' => $flashcard->categories->pluck('nombre', 'id')->toArray(),
        ];
    }

    /**
     * Obtener estadísticas del usuario
     */
    public function getEstadisticas($userId)
    {
        try {
            $total = Flashcard::where('user_id', $userId)
                ->where('activo', 1)
                ->count();

            $conErrores = Flashcard::where('user_id', $userId)
                ->where('activo', 1)
                ->where('errors', '>', 0)
                ->count();

            $sinErrores = $total - $conErrores;

            return [
                'total' => $total,
                'con_errores' => $conErrores,
                'sin_errores' => $sinErrores,
                'porcentaje_aciertos' => $total > 0 ? round(($sinErrores / $total) * 100, 2) : 0
            ];

        } catch (\Exception $e) {
            Log::error('Error obteniendo estadísticas: ' . $e->getMessage());
            throw new \Exception('Error al obtener estadísticas');
        }
    }
}
