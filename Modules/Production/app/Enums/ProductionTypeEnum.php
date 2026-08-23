<?php

namespace Modules\Production\Enums;

use App\Concerns\EnumTrait;
use BenSampo\Enum\Enum;

final class ProductionTypeEnum extends Enum
{
    use EnumTrait;

    const ORDER = 'order';

    const ROUTINE = 'routine';

    public static function getDescription(mixed $value): string
    {
        return match ($value) {
            self::ORDER => 'Dari Pesanan',
            self::ROUTINE => 'Produksi Rutin',
            default => parent::getDescription($value),
        };
    }
}
