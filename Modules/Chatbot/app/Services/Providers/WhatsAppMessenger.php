<?php

namespace Modules\Chatbot\Services\Providers;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Modules\Chatbot\Services\ChatMessenger;

/**
 * Sends replies through a configurable WhatsApp gateway.
 *
 * Supported providers (config `chatbot.whatsapp.provider`):
 *   - fonnte : Fonnte simple WhatsApp JSON API.
 *   - twilio : Twilio Messaging (WhatsApp channel).
 *   - custom : any generic HTTP endpoint that accepts POST JSON with
 *              `{ to, text }` and a bearer token.
 *   - log    : local fallback, no external call.
 */
class WhatsAppMessenger implements ChatMessenger
{
    public function send(string $recipient, string $text): Response|array
    {
        $provider = (string) config('chatbot.whatsapp.provider', 'log');

        return match ($provider) {
            'fonnte' => $this->viaFonnte($recipient, $text),
            'twilio' => $this->viaTwilio($recipient, $text),
            'custom' => $this->viaCustom($recipient, $text),
            default => app(LogMessenger::class)->send($recipient, $text),
        };
    }

    private function viaFonnte(string $phone, string $text): Response|array
    {
        $token = config('chatbot.whatsapp.fonnte.token');
        if (empty($token)) {
            return app(LogMessenger::class)->send($phone, $text);
        }

        return Http::post((string) config('chatbot.whatsapp.fonnte.url'), [
            'token' => $token,
            'to' => $phone,
            'message' => $text,
        ]);
    }

    private function viaTwilio(string $phone, string $text): Response|array
    {
        $sid = config('chatbot.whatsapp.twilio.account_sid');
        $token = config('chatbot.whatsapp.twilio.auth_token');
        if (empty($sid) || empty($token)) {
            return app(LogMessenger::class)->send($phone, $text);
        }

        $numeric = preg_replace('/[^0-9]/', '', $phone);
        $from = (string) config('chatbot.whatsapp.twilio.from', 'whatsapp:+14155238886');

        return Http::asForm()->withBasicAuth($sid, $token)->post(
            "https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json",
            [
                'From' => $from,
                'To' => "whatsapp:+{$numeric}",
                'Body' => $text,
            ]
        );
    }

    private function viaCustom(string $phone, string $text): Response|array
    {
        $endpoint = config('chatbot.whatsapp.endpoint');
        $token = config('chatbot.whatsapp.token');
        if (empty($endpoint)) {
            return app(LogMessenger::class)->send($phone, $text);
        }

        return Http::withToken($token)->post((string) $endpoint, [
            'to' => $phone,
            'text' => $text,
        ]);
    }
}
