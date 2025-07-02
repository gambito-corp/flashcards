<?php
// app/Services/Api/MedBank/Strategies/DataStrategyInterface.php

namespace App\Services\Api\MedBank\Strategies;

use Illuminate\Database\Eloquent\Collection;

interface DataStrategyInterface
{
    public function getAreas();

    public function getCategories(?int $areaId = null): Collection;

    public function getTipos(?int $categoryId = null): Collection;
}
