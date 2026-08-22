<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('po_pos', function (Blueprint $table) {
            $table->text('po_discount_note')->nullable()->after('po_discount_type');
        });
    }

    public function down(): void
    {
        Schema::table('po_pos', function (Blueprint $table) {
            $table->dropColumn('po_discount_note');
        });
    }
};
