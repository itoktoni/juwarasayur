<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Work order produksi
        Schema::create('productions', function (Blueprint $table) {
            $table->id();
            $table->string('production_code', 50)->unique();
            // order = dari pesanan SO; routine = produksi rutin manual
            $table->string('production_type', 20)->default('routine');
            $table->string('production_status', 20)->default('pending');
            // Produk keluaran (paket) hasil produksi
            $table->foreignId('production_id_product')->constrained('catalog_products');
            $table->unsignedInteger('production_qty_output')->default(1);
            // Daftar id SO sumber (untuk tipe order)
            $table->json('production_orders')->nullable();
            $table->text('production_note')->nullable();
            $table->timestamps();
        });

        // Bahan baku yang dikonsumsi per work order
        Schema::create('production_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('production_item_id_production')->constrained('productions')->cascadeOnDelete();
            $table->foreignId('production_item_id_product')->constrained('catalog_products');
            $table->unsignedInteger('production_item_qty')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('production_items');
        Schema::dropIfExists('productions');
    }
};
