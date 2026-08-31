<?php

namespace Modules\Chatbot\Models;

use App\Models\BaseModel;

/**
 * Riwayat chat web (halaman WhatsApp-like). Identitas anonim per browser
 * via cookie session_token — terpisah dari ChatbotSession (WA/Telegram).
 */
class WebChatMessage extends BaseModel
{
    protected $table = 'chat_web_messages';

    protected $fillable = ['session_token', 'role', 'content', 'ui'];

    public static $sortColumns = ['created_at'];

    public static function field_name(): string
    {
        return 'content';
    }

    protected function casts(): array
    {
        return [
            'ui' => 'string',
        ];
    }
}
