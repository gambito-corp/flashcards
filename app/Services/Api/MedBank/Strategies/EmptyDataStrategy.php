<?php
// app/Services/Api/MedBank/Strategies/EmptyDataStrategy.php

namespace App\Services\Api\MedBank\Strategies;

use Illuminate\Database\Eloquent\Collection;

class EmptyDataStrategy implements DataStrategyInterface
{
    public function getAreas(): Collection
    {
        return new Collection(); // ← Cambiar collect() por new Collection()
    }

    public function getCategories(?int $areaId = null): Collection
    {
        return new Collection(); // ← Cambiar collect() por new Collection()
    }

    public function getTipos(?int $categoryId = null): Collection
    {
        return new Collection(); // ← Cambiar collect() por new Collection()
    }
}
