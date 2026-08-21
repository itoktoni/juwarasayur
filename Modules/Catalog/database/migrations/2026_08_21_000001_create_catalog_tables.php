<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('catalog_brands', function (Blueprint $table) {
            $table->id();
            $table->string('brand_nama');
            $table->string('brand_slug')->nullable();
            $table->string('brand_logo')->nullable();
            $table->text('brand_deskripsi')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('catalog_satuans', function (Blueprint $table) {
            $table->id();
            $table->string('satuan_nama');
            $table->string('satuan_kode')->nullable();
            $table->string('satuan_simbol')->nullable();
            $table->text('satuan_deskripsi')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('catalog_categories', function (Blueprint $table) {
            $table->id();
            $table->string('category_nama');
            $table->string('category_slug')->nullable();
            $table->text('category_deskripsi')->nullable();
            $table->string('category_icon')->nullable();
            $table->string('category_image')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('catalog_tags', function (Blueprint $table) {
            $table->id();
            $table->string('tag_nama');
            $table->string('tag_slug')->nullable();
            $table->string('tag_warna')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('catalog_products', function (Blueprint $table) {
            $table->id();
            $table->string('product_nama');
            $table->string('product_slug')->nullable();
            $table->string('product_kode')->nullable()->unique();
            $table->string('product_sku')->nullable()->unique();
            $table->string('product_barcode')->nullable();
            $table->text('product_deskripsi')->nullable();
            $table->longText('product_deskripsi_lengkap')->nullable();
            $table->decimal('product_harga', 15, 2)->default(0);
            $table->decimal('product_harga_modal', 15, 2)->default(0);
            $table->decimal('product_harga_grosir', 15, 2)->nullable();
            $table->decimal('product_berat', 10, 2)->nullable();
            $table->decimal('product_panjang', 10, 2)->nullable();
            $table->decimal('product_lebar', 10, 2)->nullable();
            $table->decimal('product_tinggi', 10, 2)->nullable();
            $table->integer('product_stok')->default(0);
            $table->integer('product_stok_minimum')->default(0);
            $table->string('product_gambar')->nullable();
            $table->json('product_galeri')->nullable();
            $table->string('product_status')->default('active');
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->foreignId('product_id_brand')->nullable()->constrained('catalog_brands')->nullOnDelete();
            $table->foreignId('product_id_satuan')->nullable()->constrained('catalog_satuans')->nullOnDelete();
            $table->foreignId('product_id_category')->nullable()->constrained('catalog_categories')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('catalog_product_tag', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('catalog_products')->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained('catalog_tags')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['product_id', 'tag_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catalog_product_tag');
        Schema::dropIfExists('catalog_products');
        Schema::dropIfExists('catalog_tags');
        Schema::dropIfExists('catalog_categories');
        Schema::dropIfExists('catalog_satuans');
        Schema::dropIfExists('catalog_brands');
    }
};
