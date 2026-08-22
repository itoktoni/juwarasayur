<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inv_gudangs', function (Blueprint $table) {
            $table->id();
            $table->string('gudang_nama')->unique();
            $table->string('gudang_kode')->nullable()->unique();
            $table->text('gudang_alamat')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('inv_lokasis', function (Blueprint $table) {
            $table->id();
            $table->string('lokasi_nama');
            $table->string('lokasi_kode')->nullable()->unique();
            $table->foreignId('lokasi_id_gudang')->constrained('inv_gudangs')->cascadeOnDelete();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('inv_stocks', function (Blueprint $table) {
            $table->id();
            $table->string('stock_code')->unique();
            $table->foreignId('stock_id_product')->constrained('catalog_products')->cascadeOnDelete();
            $table->foreignId('stock_id_lokasi')->constrained('inv_lokasis')->cascadeOnDelete();
            $table->integer('stock_qty')->default(0);
            $table->date('stock_expired_date')->nullable();
            $table->string('stock_batch')->nullable();
            $table->timestamps();
            $table->unique(['stock_id_product', 'stock_id_lokasi', 'stock_expired_date', 'stock_batch'], 'inv_stocks_lot_unique');
        });

        Schema::create('inv_stock_movements', function (Blueprint $table) {
            $table->id();
            $table->string('movement_code')->unique();
            $table->string('movement_type')->default('IN');
            $table->foreignId('movement_id_product')->constrained('catalog_products')->cascadeOnDelete();
            $table->foreignId('movement_id_lokasi')->constrained('inv_lokasis')->cascadeOnDelete();
            $table->integer('movement_qty');
            $table->date('movement_expired_date')->nullable();
            $table->string('movement_ref_type')->nullable();
            $table->unsignedBigInteger('movement_ref_id')->nullable();
            $table->string('movement_note')->nullable();
            $table->timestamps();
            $table->index(['movement_ref_type', 'movement_ref_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inv_stock_movements');
        Schema::dropIfExists('inv_stocks');
        Schema::dropIfExists('inv_lokasis');
        Schema::dropIfExists('inv_gudangs');
    }
};
