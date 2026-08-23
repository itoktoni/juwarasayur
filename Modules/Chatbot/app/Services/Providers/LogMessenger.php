<?php

namespace Modules\Chatbot\Services\Providers;

use Illuminate\Support\Str;
use Modules\Chatbot\Services\ChatMessenger;

/**
 * Local fallback messenger. Writes the outgoing reply to the audit log
 * instead of making a network call. Used when no bot keys are configured.
 */
class LogMessenger implements ChatMessenger
{
    public function send(string $recipient, string $text): array
    {
        $line = strtoupper(Str::uuid()).' '.now()->toDateTimeString()
            .' | recipient='.$recipient.' | '.$text.PHP_EOL;

        $file = storage_path('logs/chatbot.log');

        if (! is_dir(dirname($file))) {
            mkdir(dirname($file), 0755, true);
        }

        file_put_contents($file, $line, FILE_APPEND);

        // ponytail: log-only mode keeps the module fully testable without keys.
        return [
            'status' => true,
            'mode' => 'log',
            'recipient' => $recipient,
            'text' => $text,
        ];
    }
}
