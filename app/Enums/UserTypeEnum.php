<?php

namespace App\Enums;

use App\Concerns\EnumTrait;
use BenSampo\Enum\Enum;

final class UserTypeEnum extends Enum
{
    use EnumTrait;

    const USER = 'user';

    const RESELLER = 'reseller';

    const CUSTOMER = 'customer';

    public static function getDescription(mixed $value): string
    {
        return match ($value) {
            self::USER => 'User',
            self::RESELLER => 'Reseller',
            self::CUSTOMER => 'Customer',
            default => parent::getDescription($value),
        };
    }
}
