<?php

namespace App\Services\Api\MedBank\Strategies;

use App\Models\Area;
use App\Models\Category;
use App\Models\Tipo;
use Illuminate\Database\Eloquent\Collection;

class AIDataStrategy implements DataStrategyInterface
{
    public function getAreas()
    {
        return Area::query()->get();
    }

    public function getCategories(?int $areaId = null): Collection
    {
        return Category::query()->where('area_id', $areaId)->get();
    }

    public function getTipos(?int $categoryId = null): Collection
    {
        return Tipo::query()->where('category_id', $categoryId)->get();
    }
}
