<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('catalog_product_masters', function (Blueprint $table) {
            $table->id();
            $table->string('product_master_nama');
            $table->string('product_master_slug')->nullable();
            $table->text('product_master_deskripsi')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('catalog_products', function (Blueprint $table) {
            $table->foreignId('product_id_product_master')
                ->nullable()
                ->after('product_id_brand')
                ->constrained('catalog_product_masters')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('catalog_products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('product_id_product_master');
        });

        Schema::dropIfExists('catalog_product_masters');
    }
};
