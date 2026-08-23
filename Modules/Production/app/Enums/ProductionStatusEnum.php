<?php

namespace Modules\Production\Enums;

use App\Concerns\EnumTrait;
use BenSampo\Enum\Enum;

final class ProductionStatusEnum extends Enum
{
    use EnumTrait;

    const PENDING = 'pending';

    const COMPLETED = 'completed';

    const CANCELLED = 'cancelled';

    public static function getDescription(mixed $value): string
    {
        return match ($value) {
            self::PENDING => 'Menunggu',
            self::COMPLETED => 'Selesai',
            self::CANCELLED => 'Dibatalkan',
            default => parent::getDescription($value),
        };
    }
}
