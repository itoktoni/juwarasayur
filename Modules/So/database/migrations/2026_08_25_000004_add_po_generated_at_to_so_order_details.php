<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('so_order_details', function (Blueprint $table) {
            $table->timestamp('po_generated_at')->nullable()->after('so_detail_keterangan');
        });
    }

    public function down(): void
    {
        Schema::table('so_order_details', function (Blueprint $table) {
            $table->dropColumn('po_generated_at');
        });
    }
};
