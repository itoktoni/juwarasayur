<?php

namespace Modules\So\Enums;

use App\Concerns\EnumTrait;
use BenSampo\Enum\Enum;

final class ShippingMethodEnum extends Enum
{
    use EnumTrait;

    const PICKUP = 'pickup';

    const COD = 'cod';

    const DELIVERY = 'delivery';

    public static function getDescription(mixed $value): string
    {
        return match ($value) {
            self::PICKUP => 'Diambil di Gudang',
            self::COD => 'COD',
            self::DELIVERY => 'Dikirim',
            default => parent::getDescription($value),
        };
    }
}
