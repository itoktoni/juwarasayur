<?php

namespace Modules\Chatbot\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Chatbot\Enums\ChatbotChannelEnum;
use Modules\Chatbot\Services\ChatbotService;
use Modules\Chatbot\Services\MessengerFactory;

/**
 * Public webhooks for incoming WhatsApp & Telegram messages.
 *
 * CSRF dicexempt di bootstrap/app.php (validateCsrfTokens except).
 * Balasan dikirim otomatis melalui provider messenger konfig.
 */
class ChatbotWebhookController extends Controller
{
    public function __construct(
        private readonly ChatbotService $chatbot,
        private readonly MessengerFactory $messengers,
    ) {}

    /**
     * Telegram update (Bot API). Register via:
     *   setWebhook?url={APP_URL}/chatbot/webhook/telegram
     */
    public function telegram(Request $request): JsonResponse
    {
        $update = $request->json()->all();

        $message = $update['message'] ?? [];

        if (empty($message) || empty($message['chat']['id'])) {
            return response()->json(['status' => false, 'error' => 'not a message']);
        }

        // Unique identity: telegram chat/user id.
        $chatId = (string) $message['chat']['id'];
        $text = (string) ($message['text'] ?? '');

        $reply = $this->chatbot->respond(ChatbotChannelEnum::TELEGRAM, $chatId, $text);

        if ($reply !== '') {
            $this->messengers->for(ChatbotChannelEnum::TELEGRAM)->send($chatId, $reply);
        }

        return response()->json(['status' => true]);
    }

    /**
     * WhatsApp message. Mendukung payload flat (Fonnte) maupun nested
     * WhatsApp Cloud API (entry[].changes[].value.messages[]).
     */
    public function whatsapp(Request $request): JsonResponse
    {
        $data = $request->json()->all() ?: $request->all();

        // WhatsApp Cloud API nested payload.
        $messages = $data['entry'][0]['changes'][0]['value']['messages'] ?? null;

        if (is_array($messages) && ! empty($messages)) {
            $first = $messages[0];
            $from = (string) ($first['from'] ?? '');
            $text = (string) ($first['text']['body'] ?? $first['body'] ?? '');
        } else {
            // Flat payload (Fonnte / custom): from + message_body/message/text.
            $from = (string) ($data['from'] ?? $data['sender'] ?? $data['waId'] ?? '');
            $text = (string) ($data['message_body'] ?? $data['message'] ?? $data['body'] ?? $data['text'] ?? '');
        }

        if ($from === '' || $text === '') {
            return response()->json(['status' => false, 'error' => 'from/text required']);
        }

        // Unique identity: notelp. Used as recipient & phone snapshot.
        $reply = $this->chatbot->respond(ChatbotChannelEnum::WHATSAPP, $from, $text, $from);

        if ($reply !== '') {
            $this->messengers->for(ChatbotChannelEnum::WHATSAPP)->send($from, $reply);
        }

        return response()->json(['status' => true]);
    }
}
