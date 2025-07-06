<?php

namespace App\Enums\Api\MedBank;

enum DataTypeEnum: string
{
    case STANDARD = 'standard';
    case AI = 'ai';
    case PDF = 'pdf';
    case LOCAL_FAILED = 'personal-failed';
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
