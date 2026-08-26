<?php

namespace Modules\So\Enums;

use App\Concerns\EnumTrait;
use BenSampo\Enum\Enum;

final class ConsignmentStatusEnum extends Enum
{
    use EnumTrait;

    const OPEN = 'open';

    const SETTLED = 'settled';

    public static function getDescription(mixed $value): string
    {
        return match ($value) {
            self::OPEN => 'Titipan Berjalan',
            self::SETTLED => 'Sudah Ditarik',
            default => parent::getDescription($value),
        };
    }
}
