<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('po_suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('supplier_nama');
            $table->string('supplier_kode')->nullable()->unique();
            $table->string('supplier_telepon')->nullable();
            $table->string('supplier_email')->nullable();
            $table->text('supplier_alamat')->nullable();
            $table->string('supplier_kontak_person')->nullable();
            $table->string('supplier_npwp')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('po_pos', function (Blueprint $table) {
            $table->id();
            $table->string('po_code')->unique();
            $table->date('po_tanggal');
            $table->foreignId('po_id_supplier')->constrained('po_suppliers')->cascadeOnDelete();
            $table->string('po_status')->default('pending');
            $table->text('po_keterangan')->nullable();
            $table->decimal('po_subtotal', 15, 2)->default(0);
            $table->decimal('po_ppn', 15, 2)->default(0);
            $table->decimal('po_grand_total', 15, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('po_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('po_detail_id_po')->constrained('po_pos')->cascadeOnDelete();
            $table->foreignId('po_detail_id_product')->constrained('catalog_products')->cascadeOnDelete();
            $table->string('po_detail_code')->nullable();
            $table->integer('po_detail_qty');
            $table->decimal('po_detail_harga', 15, 2)->default(0);
            $table->decimal('po_detail_subtotal', 15, 2)->default(0);
            $table->string('po_detail_keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('po_details');
        Schema::dropIfExists('po_pos');
        Schema::dropIfExists('po_suppliers');
    }
};
