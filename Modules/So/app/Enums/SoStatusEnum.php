<?php

namespace Modules\So\Enums;

use App\Concerns\EnumTrait;
use BenSampo\Enum\Enum;

final class SoStatusEnum extends Enum
{
    use EnumTrait;

    const PENDING = 'pending';

    const CONFIRMED = 'confirmed';

    const SHIPPED = 'shipped';

    const DELIVERED = 'delivered';

    const CANCELLED = 'cancelled';

    public static function getDescription(mixed $value): string
    {
        return match ($value) {
            self::PENDING => 'Pending',
            self::CONFIRMED => 'Confirmed',
            self::SHIPPED => 'Dikirim',
            self::DELIVERED => 'Diterima',
            self::CANCELLED => 'Cancelled',
            default => parent::getDescription($value),
        };
    }
}
