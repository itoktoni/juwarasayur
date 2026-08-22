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
        Schema::table('so_orders', function (Blueprint $table) {
            $table->decimal('so_discount', 12, 2)->default(0)->after('so_subtotal');
            $table->string('so_discount_type')->default('nominal')->after('so_discount');
            $table->string('so_discount_note', 500)->nullable()->after('so_discount_type');
            $table->decimal('so_dpp', 14, 2)->default(0)->after('so_discount_note');
            $table->decimal('so_ppn', 14, 2)->default(0)->after('so_dpp');
            $table->decimal('so_ppn_rate', 5, 2)->default(0)->after('so_ppn');
            $table->decimal('so_pph', 14, 2)->default(0)->after('so_ppn_rate');
            $table->decimal('so_pph_rate', 5, 2)->default(0)->after('so_pph');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('so_orders', function (Blueprint $table) {
            $table->dropColumn([
                'so_discount',
                'so_discount_type',
                'so_discount_note',
                'so_dpp',
                'so_ppn',
                'so_ppn_rate',
                'so_pph',
                'so_pph_rate',
            ]);
        });
    }
};
