<?php

namespace Modules\Chatbot\Services\Providers;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Modules\Chatbot\Services\ChatMessenger;

/**
 * Sends replies through the Telegram Bot API (sendMessage).
 */
class TelegramMessenger implements ChatMessenger
{
    public function send(string $recipient, string $text): Response|array
    {
        $token = config('chatbot.telegram.token');

        if (empty($token)) {
            // No token configured — fall back to a local log so testing is safe.
            return app(LogMessenger::class)->send($recipient, $text);
        }

        $base = (string) config('chatbot.telegram.api_base', 'https://api.telegram.org');

        return Http::asJson()->post("{$base}/bot{$token}/sendMessage", [
            'chat_id' => $recipient,
            'text' => $text,
        ]);
    }
}
