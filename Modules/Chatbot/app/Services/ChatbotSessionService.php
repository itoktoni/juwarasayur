<?php

namespace Modules\Chatbot\Services;

use Modules\Chatbot\Models\ChatbotSession;

/**
 * Persistence for one conversation per (channel, messenger_user).
 *
 * Identity rules:
 *   - Telegram  → messenger_user = telegram chat/user id (angka).
 *   - WhatsApp  → messenger_user = notelp (phone number).
 */
class ChatbotSessionService
{
    public function findOrCreate(string $channel, string $messengerUser): ChatbotSession
    {
        $session = ChatbotSession::query()
            ->where('channel', $channel)
            ->where('messenger_user', $messengerUser)
            ->first();

        if (! $session) {
            $session = ChatbotSession::create([
                'channel' => $channel,
                'messenger_user' => $messengerUser,
                'state' => null,
                'meta' => null,
                'cart' => null,
                'last_active_at' => now(),
            ]);
        }

        $session->forceFill(['last_active_at' => now()])->save();

        return $session;
    }

    /**
     * Set the conversation state + optional meta payload.
     */
    public function setState(ChatbotSession $session, ?string $state, array $meta = []): void
    {
        $session->forceFill([
            'state' => $state,
            'meta' => $meta ?: null,
        ])->save();
    }

    /**
     * Store raw cart payload ([productId => qty]) on the session.
     */
    public function saveCart(ChatbotSession $session, array $cart): void
    {
        $session->forceFill(['cart' => $cart ?: null])->save();
    }
}
