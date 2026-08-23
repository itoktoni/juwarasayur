<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Master kode diskon untuk checkout (matrix: minimal transaksi, tipe percent/nominal)
        Schema::create('so_discounts', function (Blueprint $table) {
            $table->id();
            $table->string('discount_code', 50)->unique();
            $table->string('discount_nama', 100);
            $table->string('discount_type', 20)->default('percent'); // percent | nominal
            $table->decimal('discount_value', 15, 2)->default(0);
            // Syarat matrix: transaksi minimal agar diskon berlaku
            $table->decimal('discount_min_purchase', 15, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('so_discounts');
    }
};
