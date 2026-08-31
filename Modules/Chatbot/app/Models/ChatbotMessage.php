<?php

namespace Modules\Chatbot\Models;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Log percakapan chatbot (semua channel): pesan customer & balasan bot.
 */
class ChatbotMessage extends BaseModel
{
    protected $table = 'chatbot_messages';

    protected $fillable = ['chatbot_session_id', 'role', 'content'];

    public static $sortColumns = ['created_at'];

    public static function field_name(): string
    {
        return 'content';
    }

    protected $with = [];

    public function hasSession(): BelongsTo
    {
        return $this->belongsTo(ChatbotSession::class, 'chatbot_session_id');
    }

    /**
     * Helper log cepat.
     */
    public static function log(ChatbotSession $session, string $role, string $content): self
    {
        if (trim($content) === '') {
            return new self;
        }

        return static::create([
            'chatbot_session_id' => $session->id,
            'role' => $role,
            'content' => $content,
        ]);
    }
}
