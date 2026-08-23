<?php

namespace Modules\Chatbot\Enums;

use App\Concerns\EnumTrait;
use BenSampo\Enum\Enum;

final class ChatbotChannelEnum extends Enum
{
    use EnumTrait;

    const WHATSAPP = 'whatsapp';

    const TELEGRAM = 'telegram';

    public static function getDescription(mixed $value): string
    {
        return match ($value) {
            self::WHATSAPP => 'WhatsApp',
            self::TELEGRAM => 'Telegram',
            default => parent::getDescription($value),
        };
    }
}
