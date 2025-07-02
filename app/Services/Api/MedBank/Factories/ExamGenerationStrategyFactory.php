<?php

namespace App\Services\Api\MedBank\Factories;

use App\Services\Api\MedBank\Strategies\FailedExamGenerationStrategy;
use App\Services\Api\MedBank\Strategies\StandardExamGenerationStrategy;

class ExamGenerationStrategyFactory
{
    public static function create(string $type)
    {
        return match ($type) {
            'standard' => new StandardExamGenerationStrategy(),
            'failed' => new FailedExamGenerationStrategy(),
            // agrega más tipos aquí
            default => throw new \InvalidArgumentException("Tipo de examen no soportado: $type"),
        };
    }
}
