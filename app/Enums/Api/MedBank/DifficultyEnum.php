<?php
// app/Enums/Api/MedBank/DifficultyEnum.php

namespace App\Enums\Api\MedBank;

enum DifficultyEnum: string
{
    case FACIL = 'facil';
    case MEDIO = 'medio';
    case DIFICIL = 'dificil';
    case EXPERTO = 'experto';
    case SUICIDA = 'suicida';

    /**
     * Obtiene el nombre legible de la dificultad
     */
    public function getName(): string
    {
        return match ($this) {
            self::FACIL => 'Fácil',
            self::MEDIO => 'Medio',
            self::DIFICIL => 'Difícil',
            self::EXPERTO => 'Experto',
            self::SUICIDA => 'Suicida',
        };
    }

    /**
     * Obtiene la descripción de la dificultad para OpenAI
     */
    public function getDescription(): string
    {
        return match ($this) {
            self::FACIL => 'Preguntas básicas y conceptos fundamentales. Ideal para estudiantes principiantes.',
            self::MEDIO => 'Preguntas de nivel intermedio que requieren comprensión de conceptos clave.',
            self::DIFICIL => 'Preguntas avanzadas que requieren análisis crítico y conocimiento profundo.',
            self::EXPERTO => 'Preguntas de nivel profesional con casos complejos y razonamiento clínico avanzado.',
            self::SUICIDA => 'Preguntas extremadamente desafiantes con casos raros y diagnósticos diferenciales complejos. Solo para los más valientes.',
        };
    }

    /**
     * Verifica si la dificultad está desbloqueada para el usuario
     */
    public function isUnlocked(): bool
    {
        return match ($this) {
            self::FACIL, self::MEDIO, self::DIFICIL, self::EXPERTO => true,
            self::SUICIDA => (1 == 1), // Condición temporal - más tarde configurarás la lógica real
        };
    }

    /**
     * Obtiene todas las dificultades disponibles
     */
    public static function getAvailable(): array
    {
        return array_filter(
            self::cases(),
            fn(self $difficulty) => $difficulty->isUnlocked()
        );
    }

    /**
     * Convierte la dificultad a array para la API
     */
    public function toArray(): array
    {
        return [
            'id' => $this->value,
            'name' => $this->getName(),
            'description' => $this->getDescription(),
            'unlocked' => $this->isUnlocked(),
        ];
    }
}
