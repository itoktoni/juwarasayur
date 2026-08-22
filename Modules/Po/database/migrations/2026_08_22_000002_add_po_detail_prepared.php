<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('po_details', function (Blueprint $table) {
            $table->integer('po_detail_prepared')->default(0)->after('po_detail_qty');
        });
    }

    public function down(): void
    {
        Schema::table('po_details', function (Blueprint $table) {
            $table->dropColumn('po_detail_prepared');
        });
    }
};
