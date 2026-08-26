<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('catalog_products', function (Blueprint $table) {
            $table->decimal('reseller_fee_percent', 5, 2)->nullable()->after('product_harga_grosir')->comment('diskon reseller % untuk harga bayar, null=0 tidak diskon');
            $table->decimal('affiliator_fee_percent', 5, 2)->nullable()->after('reseller_fee_percent')->comment('komisi affiliator % per baris, null fallback ke users.fee/config');
        });
    }

    public function down(): void
    {
        Schema::table('catalog_products', function (Blueprint $table) {
            $table->dropColumn(['reseller_fee_percent', 'affiliator_fee_percent']);
        });
    }
};
