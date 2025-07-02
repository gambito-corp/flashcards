<?php
// app/Services/Api/MedBank/Strategies/FailedDataStrategy.php

namespace App\Services\Api\MedBank\Strategies;

use App\Models\{Area, Category, ExamUserAnswer, Question, QuestionTipo, Tipo};
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;

class FailedDataStrategy implements DataStrategyInterface
{
    public function __construct(
        private readonly bool $isPersonal = true
    )
    {
    }

    public function getAreas(): Collection
    {
        $failedData = $this->getFailedData();
        return $failedData['areas'] ?? collect();
    }

    public function getCategories(?int $areaId = null): Collection
    {
        $failedData = $this->getFailedData(areaId: $areaId);
        return $failedData['categories'] ?? collect();
    }

    public function getTipos(?int $categoryId = null): Collection
    {
        $failedData = $this->getFailedData(categoryId: $categoryId);
        return $failedData['tipos'] ?? collect();
    }

    private function getFailedData(?int $areaId = null, ?int $categoryId = null): array
    {
        $preguntasIds = $this->getFailedQuestionIds();
        return $this->buildDataStructure($preguntasIds, $areaId, $categoryId);
    }

    private function getFailedQuestionIds(): SupportCollection
    {
        $query = ExamUserAnswer::query()->where('is_correct', false);

        if ($this->isPersonal) {
            $query->where('user_id', auth()->id());
        }

        return $query->pluck('question_id');
    }

    private function buildDataStructure(SupportCollection $preguntasIds, ?int $areaId, ?int $categoryId): array
    {
        $preguntas = Question::query()->whereIn('id', $preguntasIds)->pluck('id');

        $primitiveTypesId = QuestionTipo::query()
            ->whereIn('question_id', $preguntas)
            ->pluck('tipo_id')
            ->unique()
            ->values();

        $tiposIds = Tipo::query()
            ->when($categoryId, fn($q) => $q->where('category_id', $categoryId))
            ->whereIn('id', $primitiveTypesId)
            ->pluck('id');

        $primitiveCategoriesId = Tipo::query()
            ->whereIn('id', $tiposIds)
            ->pluck('category_id');

        $categoriesIds = Category::query()
            ->when($areaId, fn($q) => $q->where('area_id', $areaId))
            ->whereIn('id', $primitiveCategoriesId)
            ->pluck('id');

        $primitiveAreasId = Category::query()
            ->whereIn('id', $categoriesIds)
            ->pluck('area_id');

        $areasIds = Area::query()
            ->where('team_id', auth()->user()->current_team_id)
            ->whereIn('id', $primitiveAreasId)
            ->pluck('id');

        return [
            'areas' => Area::query()->whereIn('id', $areasIds)->get(),
            'categories' => Category::query()->whereIn('id', $categoriesIds)->get(),
            'tipos' => Tipo::query()->whereIn('id', $tiposIds)->get(),
        ];
    }
}
