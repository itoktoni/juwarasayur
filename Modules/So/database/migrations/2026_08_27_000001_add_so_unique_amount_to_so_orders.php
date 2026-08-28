<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Nominal unik 2 digit untuk verifikasi pembayaran via webhook.
     * Contoh: total 50.000 + unik 23 = 50.023
     */
    public function up(): void
    {
        Schema::table('so_orders', function (Blueprint $table) {
            $table->decimal('so_unique_amount', 15, 2)->nullable()->after('so_grand_total');
        });

        // Backfill untuk SO lama: gunakan grand_total tanpa unique code
        DB::table('so_orders')
            ->whereNull('so_unique_amount')
            ->update(['so_unique_amount' => DB::raw('so_grand_total')]);
    }

    public function down(): void
    {
        Schema::table('so_orders', function (Blueprint $table) {
            $table->dropColumn('so_unique_amount');
        });
    }
};
