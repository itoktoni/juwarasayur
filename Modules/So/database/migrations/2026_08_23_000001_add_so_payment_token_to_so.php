<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Token acak untuk URL pembayaran (bukan id berurutan).
     */
    public function up(): void
    {
        Schema::table('so_orders', function (Blueprint $table) {
            $table->uuid('so_payment_token')->nullable()->unique()->after('so_code');
        });

        // Backfill token untuk SO lama agar URL /payment/{id} lama tetap bisa dipetakan
        foreach (DB::table('so_orders')->whereNull('so_payment_token')->get(['id']) as $row) {
            DB::table('so_orders')
                ->where('id', $row->id)
                ->update(['so_payment_token' => (string) Str::uuid()]);
        }
    }

    public function down(): void
    {
        Schema::table('so_orders', function (Blueprint $table) {
            $table->dropUnique(['so_payment_token']);
            $table->dropColumn('so_payment_token');
        });
    }
};
