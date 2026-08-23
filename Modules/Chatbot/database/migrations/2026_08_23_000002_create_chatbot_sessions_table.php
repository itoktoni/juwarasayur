<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('chatbot_sessions', function (Blueprint $table) {
            $table->id();

            // Channel where the chat happens (whatsapp | telegram).
            $table->string('channel', 20);

            // Messenger identity — telegram: chat/user id, whatsapp: notelp.
            $table->string('messenger_user', 191);

            // Optional linked app user (customer). Null for guest/chat-only.
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            // Contact snapshot captured from the conversation.
            $table->string('contact_name')->nullable();
            $table->string('contact_phone', 30)->nullable();

            // Conversation state machine (ordering | picking | awaiting_qty ...).
            $table->string('state', 30)->nullable();
            $table->json('meta')->nullable();

            // Cart items: { product_id: quantity }.
            $table->json('cart')->nullable();

            $table->timestamp('last_active_at')->nullable();
            $table->timestamps();

            // One conversation per (channel, messenger_user).
            $table->unique(['channel', 'messenger_user']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chatbot_sessions');
    }
};
