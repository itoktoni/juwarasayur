<?php

namespace Modules\Chatbot\Models;

use App\Models\BaseModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'channel', 'messenger_user', 'user_id', 'contact_name', 'contact_phone',
    'state', 'meta', 'cart', 'last_active_at',
])]
class ChatbotSession extends BaseModel
{
    protected $table = 'chatbot_sessions';

    public static $sortColumns = ['channel', 'contact_name', 'last_active_at'];

    public static $filterColumns = ['channel', 'contact_name', 'contact_phone'];

    public static function field_name(): string
    {
        return 'contact_name';
    }

    protected function casts(): array
    {
        return [
            'meta' => 'array',
            'cart' => 'array',
            'last_active_at' => 'datetime',
        ];
    }

    public function has_user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function has_chat()
    {
        return $this->hasMany(WebChatMessage::class, 'session_token', 'messenger_user');
    }

    /**
     * Reset conversation to ordering state (keep identity & cart intact).
     */
    public function resetState(): void
    {
        $this->state = null;
        $this->meta = null;
        $this->save();
    }
}
