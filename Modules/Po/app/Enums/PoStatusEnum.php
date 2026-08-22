<?php

namespace Modules\Po\Enums;

use App\Concerns\EnumTrait;
use BenSampo\Enum\Enum;

final class PoStatusEnum extends Enum
{
    use EnumTrait;

    const PENDING = 'pending';

    const ORDERED = 'ordered';

    const PARTIAL = 'partial';

    const CLOSED = 'closed';

    const CANCELLED = 'cancelled';

    public static function getDescription(mixed $value): string
    {
        return match ($value) {
            self::PENDING => 'Pending',
            self::ORDERED => 'Ordered',
            self::PARTIAL => 'Partial',
            self::CLOSED => 'Closed',
            self::CANCELLED => 'Cancelled',
            default => parent::getDescription($value),
        };
    }
}
