<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('so_order_details', function (Blueprint $table) {
            $table->decimal('fee_percent', 5, 2)->nullable()->after('so_detail_harga')->comment('fee affiliator snapshot %');
            $table->decimal('fee_amount', 14, 2)->default(0)->after('fee_percent')->comment('fee affiliator snapshot Rp');
            $table->string('fee_source', 20)->nullable()->after('fee_amount')->comment('product|user|config');
            $table->string('applied_role', 20)->nullable()->after('fee_source')->comment('reseller|affiliator');
        });
    }

    public function down(): void
    {
        Schema::table('so_order_details', function (Blueprint $table) {
            $table->dropColumn(['fee_percent', 'fee_amount', 'fee_source', 'applied_role']);
        });
    }
};
