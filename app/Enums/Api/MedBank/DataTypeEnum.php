<?php

namespace App\Enums\Api\MedBank;

enum DataTypeEnum: string
{
    case STANDARD = 'standard';
    case IA = 'ia';
    case PDF = 'pdf';
    case LOCAL_FAILED = 'local-failed';
    case GLOBAL_FAILED = 'global-failed';

    public static function getDefault(): self
    {
        return self::STANDARD;
    }

    public function isFailedType(): bool
    {
        return in_array($this, [self::LOCAL_FAILED, self::GLOBAL_FAILED]);
    }

    public function isPersonalFailed(): bool
    {
        return $this === self::LOCAL_FAILED;
    }
}
