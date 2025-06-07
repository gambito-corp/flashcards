<?php

namespace App\Enums;

enum Pais: string
{
    case PERU = 'Peru';
    case ESPANA = 'España';

    // ✅ Método para obtener la etiqueta legible
    public function label(): string
    {
        return match ($this) {
            self::PERU => 'Perú',
            self::ESPANA => 'España',
        };
    }

    // ✅ Método para obtener todos los valores
    public static function values(): array
    {
        return array_map(fn($case) => $case->value, self::cases());
    }

    // ✅ Método para obtener opciones para select
    public static function options(): array
    {
        return array_map(fn($case) => [
            'value' => $case->value,
            'label' => $case->label()
        ], self::cases());
    }

    // ✅ Método para obtener por nombre (string)
    public static function fromName(string $name): ?self
    {
        return constant("self::$name") ?? null;
    }

    // ✅ Valor por defecto
    public static function default(): self
    {
        return self::PERU;
    }

    // ✅ Método para verificar si es válido
    public static function isValid(string $value): bool
    {
        return self::tryFrom($value) !== null;
    }

    // ✅ Método para obtener descripción
    public function description(): string
    {
        return match ($this) {
            self::PERU => 'República del Perú',
            self::ESPANA => 'Reino de España',
        };
    }

    // ✅ Método para obtener código ISO
    public function isoCode(): string
    {
        return match ($this) {
            self::PERU => 'PE',
            self::ESPANA => 'ES',
        };
    }

    // ✅ Método para obtener prefijo telefónico
    public function phonePrefix(): string
    {
        return match ($this) {
            self::PERU => '+51',
            self::ESPANA => '+34',
        };
    }
}
