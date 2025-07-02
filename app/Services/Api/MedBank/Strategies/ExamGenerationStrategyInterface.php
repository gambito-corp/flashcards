<?php

namespace App\Services\Api\MedBank\Strategies;

interface ExamGenerationStrategyInterface
{
    public function generateExam(array $data): array;
}
