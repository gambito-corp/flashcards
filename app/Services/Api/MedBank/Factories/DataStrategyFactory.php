<?php
// app/Services/Api/MedBank/Factories/DataStrategyFactory.php

namespace App\Services\Api\MedBank\Factories;

use App\Enums\Api\MedBank\DataTypeEnum;
use App\Services\Api\MedBank\Strategies\{AIDataStrategy,
    DataStrategyInterface,
    EmptyDataStrategy,
    FailedDataStrategy,
    StandardDataStrategy};

class DataStrategyFactory
{
    public static function create(DataTypeEnum $type): DataStrategyInterface
    {
        return match ($type) {
            DataTypeEnum::STANDARD => new StandardDataStrategy(),
            DataTypeEnum::AI => new AIDataStrategy(),
            DataTypeEnum::LOCAL_FAILED => new FailedDataStrategy(isPersonal: true),
            DataTypeEnum::GLOBAL_FAILED => new FailedDataStrategy(isPersonal: false),
            DataTypeEnum::PDF => new EmptyDataStrategy(),
        };
    }
}
