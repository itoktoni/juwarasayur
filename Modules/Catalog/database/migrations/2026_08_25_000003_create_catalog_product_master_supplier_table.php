<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('catalog_product_master_supplier', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_master_id')->constrained('catalog_product_masters')->cascadeOnDelete();
            $table->foreignId('supplier_id')->constrained('po_suppliers')->cascadeOnDelete();
            $table->boolean('is_recommended')->default(false);
            $table->timestamps();

            $table->unique(['product_master_id', 'supplier_id'], 'cms_master_supplier_unique');
            $table->index('supplier_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catalog_product_master_supplier');
    }
};
