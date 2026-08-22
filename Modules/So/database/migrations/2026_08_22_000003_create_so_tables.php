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
        Schema::create('so_orders', function (Blueprint $table) {
            $table->id();
            $table->string('so_code', 50)->unique();
            $table->date('so_tanggal');
            $table->foreignId('so_id_reseller')->constrained('users')->cascadeOnDelete();
            $table->foreignId('so_id_customer')->nullable()->constrained('users')->nullOnDelete();
            $table->string('so_status')->default('pending');
            $table->string('so_shipping_method')->default('pickup');
            $table->string('so_cod_location')->nullable();
            $table->decimal('so_shipping_fee', 12, 2)->default(0);
            $table->decimal('so_distance_km', 8, 2)->nullable();
            $table->string('so_address')->nullable();
            $table->decimal('so_lat', 10, 7)->nullable();
            $table->decimal('so_lng', 10, 7)->nullable();
            $table->text('so_keterangan')->nullable();
            $table->decimal('so_subtotal', 14, 2)->default(0);
            $table->decimal('so_grand_total', 14, 2)->default(0);
            $table->timestamps();

            $table->index(['so_id_reseller', 'so_id_customer']);
            $table->index(['so_status', 'so_tanggal']);
        });

        Schema::create('so_order_details', function (Blueprint $table) {
            $table->id();
            $table->string('so_detail_code', 60)->unique();
            $table->foreignId('so_detail_id_so')->constrained('so_orders')->cascadeOnDelete();
            $table->foreignId('so_detail_id_product')->constrained('catalog_products')->cascadeOnDelete();
            $table->integer('so_detail_qty')->default(1);
            $table->decimal('so_detail_harga', 12, 2)->default(0);
            $table->string('so_detail_keterangan', 500)->nullable();
            $table->timestamps();

            $table->index('so_detail_id_product');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('so_order_details');
        Schema::dropIfExists('so_orders');
    }
};
