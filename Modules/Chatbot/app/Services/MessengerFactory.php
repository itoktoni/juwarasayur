<?php

namespace Modules\Chatbot\Services;

use InvalidArgumentException;
use Modules\Chatbot\Enums\ChatbotChannelEnum;
use Modules\Chatbot\Services\Providers\TelegramMessenger;
use Modules\Chatbot\Services\Providers\WhatsAppMessenger;

/**
 * Resolves the outgoing messenger for a channel.
 */
class MessengerFactory
{
    public function for(string $channel): ChatMessenger
    {
        return match ($channel) {
            ChatbotChannelEnum::TELEGRAM => app(TelegramMessenger::class),
            ChatbotChannelEnum::WHATSAPP => app(WhatsAppMessenger::class),
            default => throw new InvalidArgumentException("Channel chatbot tidak dikenal: {$channel}"),
        };
    }
}
