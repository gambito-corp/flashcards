<?php
// app/Services/Api/MedBank/Strategies/FailedDataStrategy.php

namespace App\Services\Api\MedBank\Strategies;

use App\Models\{Area, Category, ExamUserAnswer, QuestionTipo, Tipo};
use Illuminate\Database\Eloquent\Collection;

class FailedDataStrategy implements DataStrategyInterface
{
    public function __construct(
        private readonly bool $isPersonal = true
    )
    {
    }

    public function getAreas(): Collection
    {
        $examUserAnswers = ExamUserAnswer::query()
            ->where('is_correct', false)
            ->when($this->isPersonal, fn($q) => $q->where('user_id', auth()->id()))
            ->pluck('question_id');
        $typesIds = QuestionTipo::query()
            ->whereIn('question_id', $examUserAnswers)
            ->pluck('tipo_id')
            ->unique()
            ->values();
        $categorysIds = Tipo::query()
            ->whereIn('id', $typesIds)
            ->pluck('category_id')
            ->unique()
            ->values();
        $areasIds = Category::query()
            ->whereIn('id', $categorysIds)
            ->pluck('area_id')
            ->unique()
            ->values();
        return Area::query()->whereIn('id', $areasIds)->withCount([
            'categories as questions_count' => function ($query) use ($examUserAnswers) {
                $query->join('tipos', 'categories.id', '=', 'tipos.category_id')
                    ->join('question_tipo', 'tipos.id', '=', 'question_tipo.tipo_id')
                    ->join('questions', 'question_tipo.question_id', '=', 'questions.id')
                    ->where('questions.approved', true)
                    ->whereNull('questions.deleted_at')
                    ->whereIn('questions.id', $examUserAnswers); // <-- SOLO FALLADAS
            }
        ])->get();
    }


    public function getCategories(?int $areaId = null): Collection
    {
        $examUserAnswers = ExamUserAnswer::query()
            ->where('is_correct', false)
            ->when($this->isPersonal, fn($q) => $q->where('user_id', auth()->id()))
            ->pluck('question_id');

        $categories = Category::where('area_id', $areaId)->get();

        foreach ($categories as $category) {
            // Obtener los tipos de la categoría
            $tipoIds = Tipo::where('category_id', $category->id)->pluck('id');
            // Obtener cuántas preguntas falladas hay en esos tipos
            $falladasCount = QuestionTipo::whereIn('tipo_id', $tipoIds)
                ->whereIn('question_id', $examUserAnswers)
                ->distinct('question_id')
                ->count('question_id');
            $category->questions_count = $falladasCount;
        }

        return $categories;
    }


    public function getTipos(?int $categoryId = null): Collection
    {
        $examUserAnswers = ExamUserAnswer::query()
            ->where('is_correct', false)
            ->when($this->isPersonal, fn($q) => $q->where('user_id', auth()->id()))
            ->pluck('question_id');

        $tipos = Tipo::where('category_id', $categoryId)->get();

        foreach ($tipos as $tipo) {
            $falladasCount = QuestionTipo::where('tipo_id', $tipo->id)
                ->whereIn('question_id', $examUserAnswers)
                ->distinct('question_id')
                ->count('question_id');
            $tipo->questions_count = $falladasCount;
        }

        return $tipos;
    }

}
