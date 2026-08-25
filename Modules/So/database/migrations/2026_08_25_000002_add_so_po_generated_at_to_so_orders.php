<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('so_orders', function (Blueprint $table) {
            $table->timestamp('so_po_generated_at')->nullable()->after('so_status');
        });
    }

    public function down(): void
    {
        Schema::table('so_orders', function (Blueprint $table) {
            $table->dropColumn('so_po_generated_at');
        });
    }
};
