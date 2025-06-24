<?php

namespace App\Services\Api\Flashcard;

use App\Models\FcCard as Card;
use App\Models\FcCategory as Category;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CategoryService
{
    public function __construct()
    {
    }

    // ========================================
    // LISTAR CATEGORÍAS CON PAGINACIÓN
    // ========================================
    // En tu FlashcardService.php - actualizar el método index
    public function index($userId, $filters = [], $page = 1, $perPage = 10)
    {
        try {
            $query = Card::query()->with('categories')->where('user_id', $userId)
                ->orderBy('errors', 'desc');

            // ✅ FILTRO PARA FLASHCARDS SIN CATEGORÍA
            if (isset($filters['sin_categoria']) && $filters['sin_categoria'] === 'true') {
                $query->doesntHave('categories');
            } // Filtro por categoría específica
            elseif (isset($filters['categoria']) && !empty($filters['categoria'])) {
                $query->whereHas('categories', function ($q) use ($filters) {
                    $q->where('fc_category_id', $filters['categoria']);
                });
            }

            // Filtro de búsqueda
            if (isset($filters['search']) && !empty($filters['search'])) {
                $query->where(function ($q) use ($filters) {
                    $q->where('pregunta', 'LIKE', '%' . $filters['search'] . '%')
                        ->orWhere('respuesta', 'LIKE', '%' . $filters['search'] . '%');
                });
            }

            // Obtener flashcards con paginación
            $flashcards = $query->paginate($perPage, ['*'], 'page', $page);

            // Transformar los datos manteniendo la estructura de paginación
            $transformedData = $flashcards->getCollection()->map(function ($flashcard) {
                return [
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

                    // Categorías como array
                    'categories' => $flashcard->categories->map(function ($cat) {
                        return [
                            'id' => $cat->id,
                            'nombre' => $cat->nombre,
                            'user_id' => $cat->user_id
                        ];
                    })->toArray()
                ];
            });

            $flashcards->setCollection($transformedData);

            return $flashcards;

        } catch (\Exception $e) {
            Log::error('Error obteniendo flashcards: ' . $e->getMessage());
            throw new \Exception('Error al obtener las flashcards');
        }
    }



    // ========================================
    // CREAR CATEGORÍA
    // ========================================
    public function store($name, $userId)
    {
        try {
            $category = new Category();
            $category->nombre = $name;
            $category->user_id = $userId;
            $category->save();
            return $category;
        } catch (\Exception $e) {
            Log::error('Error al crear categoría: ' . $e->getMessage());
            throw new \Exception($e->getMessage());
        }
    }

    // ========================================
    // ACTUALIZAR CATEGORÍA
    // ========================================
    public function update($id, $name, $userId)
    {
        try {
            $category = Category::where('id', $id)
                ->where('user_id', $userId)
                ->firstOrFail();

            $category->nombre = $name;
            $category->save();

            return $category;
        } catch (\Exception $e) {
            Log::error('Error al actualizar categoría: ' . $e->getMessage());
            throw new \Exception($e->getMessage());
        }
    }

    // ========================================
    // ELIMINAR CATEGORÍA INDIVIDUAL
    // ========================================
    public function destroy($id, $userId)
    {
        try {
            DB::beginTransaction();

            // Primero, desasociar las flashcards de esta categoría
            Card::whereHas('categories', function ($query) use ($id, $userId) {
                $query->where('fc_categories.id', $id)
                    ->where('fc_categories.user_id', $userId);
            })->get()->each(function ($card) use ($id) {
                $card->categories()->detach($id);
            });

            // Luego eliminar la categoría
            $deleted = Category::where('id', $id)
                ->where('user_id', $userId)
                ->delete();

            DB::commit();
            return $deleted;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al eliminar categoría: ' . $e->getMessage());
            throw new \Exception($e->getMessage());
        }
    }

    // ========================================
    // ELIMINAR MÚLTIPLES CATEGORÍAS
    // ========================================
    public function bulkDestroy($categoryIds, $userId)
    {
        try {
            DB::beginTransaction();

            // Desasociar flashcards de estas categorías
            Card::whereHas('categories', function ($query) use ($categoryIds, $userId) {
                $query->whereIn('fc_categories.id', $categoryIds)
                    ->where('fc_categories.user_id', $userId);
            })->get()->each(function ($card) use ($categoryIds) {
                $card->categories()->detach($categoryIds);
            });

            // Eliminar las categorías
            $deleted = Category::whereIn('id', $categoryIds)
                ->where('user_id', $userId)
                ->delete();

            DB::commit();
            return $deleted;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al eliminar categorías masivamente: ' . $e->getMessage());
            throw new \Exception($e->getMessage());
        }
    }

    // ========================================
    // ELIMINAR TODAS LAS CATEGORÍAS
    // ========================================
    public function destroyAll($userId)
    {
        try {
            DB::beginTransaction();

            // Obtener todas las categorías del usuario
            $categoryIds = Category::where('user_id', $userId)->pluck('id');

            // Desasociar todas las flashcards de todas las categorías del usuario
            Card::whereHas('categories', function ($query) use ($userId) {
                $query->where('fc_categories.user_id', $userId);
            })->get()->each(function ($card) use ($categoryIds) {
                $card->categories()->detach($categoryIds->toArray());
            });

            // Eliminar todas las categorías del usuario
            $deleted = Category::where('user_id', $userId)->delete();

            DB::commit();
            return $deleted;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al eliminar todas las categorías: ' . $e->getMessage());
            throw new \Exception($e->getMessage());
        }
    }

    // ========================================
    // MÉTODOS AUXILIARES
    // ========================================
    public function show($id, $userId)
    {
        try {
            return Category::where('id', $id)
                ->where('user_id', $userId)
                ->firstOrFail();
        } catch (\Exception $e) {
            Log::error('Error al obtener categoría: ' . $e->getMessage());
            throw new \Exception($e->getMessage());
        }
    }

    public function search(int|string|null $userId, mixed $searchTerm)
    {
        try {
            return Category::query()
                ->where('user_id', $userId)
                ->where('nombre', 'like', '%' . $searchTerm . '%')
                ->select('id', 'nombre')
                ->orderBy('nombre');
        } catch (\Exception $e) {
            Log::error('Error al buscar categorías: ' . $e->getMessage());
            throw new \Exception($e->getMessage());
        }
    }

    public function getAllWithCount(int|string|null $userId)
    {
        return Category::query()
            ->where('user_id', $userId)
            ->withCount(['cards' => function ($query) use ($userId) {
                $query->where('user_id', $userId);
            }])
            ->orderBy('nombre')
            ->get(['id', 'nombre']);
    }
}
