<?php

namespace App\Services\Api\Flashcard;

use App\Models\FcCategory as Category;
use Log;

class CategoryService
{

    public function __construct()
    {
    }

    public function index($userId)
    {
        try {
            return Category::query()
                ->where('user_id', $userId)
                ->select('id', 'nombre')
                ->orderBy('nombre')
                ->get();
        } catch (\Exception $e) {
            Log::error('Error al Obtener Las Categorias de Flashcard en el Servicio... revisar Funcion index del Service/Api/Flashcard ' . $e->getMessage());
            throw new \Exception($e->getMessage());
        }
    }

    public function show()
    {
    }

    public function store($name, $userId)
    {
        try {
            $category = new Category();
            $category->nombre = $name;
            $category->user_id = $userId;
            $category->save();
            return $category;
        } catch (\Exception $e) {
            Log::error('Error al Crear La Categoria de Flashcard en el Servicio... revisar Funcion strore del Service/Api/Flashcard ' . $e->getMessage());
            throw new \Exception($e->getMessage());
        }
    }

    public function update()
    {
    }

    public function destroy()
    {
    }
}
