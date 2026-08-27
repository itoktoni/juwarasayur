<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel alokasi persiapan barang dari gudang untuk Sales Order.
     * Setiap baris = record (product, lokasi) yang disiapkan untuk 1 SO detail
     * (atau sebagian dari SO detail). Sumber kebenaran untuk "siap untuk siapa".
     */
    public function up(): void
    {
        Schema::create('prepare_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('so_detail_id')
                ->constrained('so_order_details')
                ->cascadeOnDelete();
            $table->foreignId('product_id')
                ->constrained('catalog_products')
                ->cascadeOnDelete();
            $table->foreignId('lokasi_id')
                ->constrained('inv_lokasis')
                ->cascadeOnDelete();
            $table->integer('qty');
            $table->date('expired_date')->nullable();
            $table->timestamp('prepared_at')->useCurrent();
            $table->foreignId('prepared_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamps();

            $table->index(['product_id', 'lokasi_id']);
            $table->index('so_detail_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prepare_allocations');
    }
};
