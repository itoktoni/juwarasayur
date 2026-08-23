<?php

namespace Modules\Chatbot\Services;

use Illuminate\Http\Client\Response;

/**
 * Contract for delivering a chatbot reply to a messenger recipient.
 */
interface ChatMessenger
{
    /**
     * Send a plain-text reply.
     *
     * @param  string  $recipient  telegram chat/user id, or WhatsApp notelp.
     * @param  string  $text  Message body.
     * @return Response|array
     */
    public function send(string $recipient, string $text): mixed;
}
