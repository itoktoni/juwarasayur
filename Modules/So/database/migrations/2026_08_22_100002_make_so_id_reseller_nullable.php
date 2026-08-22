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
        // Order guest tidak punya reseller — kolom dibuat nullable
        Schema::table('so_orders', function (Blueprint $table) {
            $table->foreignId('so_id_reseller')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('so_orders')->whereNull('so_id_reseller')->delete();

        Schema::table('so_orders', function (Blueprint $table) {
            $table->foreignId('so_id_reseller')->nullable(false)->change();
        });
    }
};
