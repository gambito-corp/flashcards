<?php

namespace App\Services\Api\MedBank\Factories;

use App\Services\Api\MedBank\Strategies\AiExamGeneration;
use App\Services\Api\MedBank\Strategies\FailedExamGenerationStrategy;
use App\Services\Api\MedBank\Strategies\PDFExamGeneration;
use App\Services\Api\MedBank\Strategies\StandardExamGenerationStrategy;

class ExamGenerationStrategyFactory
{
    public static function create(string $type)
    {
        return match ($type) {
            'standard' => new StandardExamGenerationStrategy(),
            'personal-failed' => new FailedExamGenerationStrategy(true),
            'global-failed' => new FailedExamGenerationStrategy(false),
            'ai' => new AiExamGeneration(),
            'pdf' => new PDFExamGeneration(),
            default => throw new \InvalidArgumentException("Tipo de examen no soportado: $type"),
        };
    }
}
