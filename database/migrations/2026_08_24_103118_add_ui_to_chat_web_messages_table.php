<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chat_web_messages', function (Blueprint $table) {
            $table->string('ui', 20)->nullable()->after('content');
        });
    }

    public function down(): void
    {
        Schema::table('chat_web_messages', function (Blueprint $table) {
            $table->dropColumn('ui');
        });
    }
};
