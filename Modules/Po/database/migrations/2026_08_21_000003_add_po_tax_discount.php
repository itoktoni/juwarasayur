<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('po_pos', function (Blueprint $table) {
            $table->decimal('po_discount', 15, 2)->default(0)->after('po_subtotal');
            $table->string('po_discount_type')->default('nominal')->after('po_discount');
            $table->decimal('po_ppn_rate', 5, 2)->default(11)->after('po_ppn');
            $table->decimal('po_pph_rate', 5, 2)->default(2)->after('po_ppn_rate');
            $table->decimal('po_pph', 15, 2)->default(0)->after('po_ppn_rate');
            $table->decimal('po_dpp', 15, 2)->default(0)->after('po_discount_type');
        });
    }

    public function down(): void
    {
        Schema::table('po_pos', function (Blueprint $table) {
            $table->dropColumn(['po_discount', 'po_discount_type', 'po_ppn_rate', 'po_pph_rate', 'po_pph', 'po_dpp']);
        });
    }
};
