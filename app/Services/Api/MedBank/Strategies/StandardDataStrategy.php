<?php
// app/Services/Api/MedBank/Strategies/StandardDataStrategy.php

namespace App\Services\Api\MedBank\Strategies;

use App\Models\{Area, Category, Tipo};
use Illuminate\Database\Eloquent\Collection;

class StandardDataStrategy implements DataStrategyInterface
{
    public function getAreas()
    {
        // Devuelve las áreas con el conteo de preguntas aprobadas
        return Area::withCount([
            'categories as questions_count' => function ($query) {
                $query->join('tipos', 'categories.id', '=', 'tipos.category_id')
                    ->join('question_tipo', 'tipos.id', '=', 'question_tipo.tipo_id')
                    ->join('questions', 'question_tipo.question_id', '=', 'questions.id')
                    ->where('questions.approved', true)
                    ->whereNull('questions.deleted_at');
            }
        ])->get();
    }

    public function getCategories(?int $areaId = null): Collection
    {
        return Category::where('area_id', $areaId)
            ->withCount([
                'tipos as questions_count' => function ($query) {
                    $query->join('question_tipo', 'tipos.id', '=', 'question_tipo.tipo_id')
                        ->join('questions', 'question_tipo.question_id', '=', 'questions.id')
                        ->where('questions.approved', true)
                        ->whereNull('questions.deleted_at');
                }
            ])
            ->get();
    }

    public function getTipos(?int $categoryId = null): Collection
    {
        return Tipo::where('category_id', $categoryId)
            ->withCount([
                'questions as questions_count' => function ($query) {
                    $query->where('questions.approved', true)
                        ->whereNull('questions.deleted_at');
                }
            ])
            ->get();
    }
}
