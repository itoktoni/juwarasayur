<?php

namespace Modules\Chatbot\Models;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Attributes\Fillable;

/**
 * Riwayat chat web (halaman WhatsApp-like). Identitas anonim per browser
 * via cookie session_token — terpisah dari ChatbotSession (WA/Telegram).
 */
#[Fillable(['session_token', 'role', 'content'])]
class WebChatMessage extends BaseModel
{
    protected $table = 'chat_web_messages';

    public static $sortColumns = ['created_at'];

    public static function field_name(): string
    {
        return 'content';
    }
}
