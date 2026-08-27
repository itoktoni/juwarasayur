<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel pivot many-to-many antara po_details dan so_order_details.
     * Menyimpan qty yang diminta per (po_detail, so_detail) — karena 1 PO
     * detail bisa gabungan dari beberapa SO detail, dan 1 SO detail bisa
     * terpecah ke beberapa PO (parsial).
     */
    public function up(): void
    {
        Schema::create('po_detail_so_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('po_detail_id')
                ->constrained('po_details')
                ->cascadeOnDelete();
            $table->foreignId('so_detail_id')
                ->constrained('so_order_details')
                ->cascadeOnDelete();
            // Qty yang diminta oleh SO detail ini ke PO detail (decimal agar aman untuk pecahan).
            $table->decimal('qty', 10, 2)->default(0);
            $table->timestamps();

            $table->unique(['po_detail_id', 'so_detail_id']);
            $table->index('so_detail_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('po_detail_so_details');
    }
};
