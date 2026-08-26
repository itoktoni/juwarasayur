<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consignments', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // reseller penerima titipan
            $table->date('consignment_date');
            $table->string('note')->nullable();
            $table->string('status')->default('open'); // open = barang dititipkan, settled = uang ditarik
            $table->decimal('total_qty', 12, 2)->default(0);
            $table->decimal('total_sold', 12, 2)->default(0);
            $table->decimal('total_returned', 12, 2)->default(0);
            $table->decimal('total_amount', 14, 2)->default(0); // nilai invoice = terjual x harga
            $table->timestamp('settled_at')->nullable();
            $table->timestamps();
        });

        Schema::create('consignment_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('consignment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('catalog_products')->cascadeOnDelete();
            $table->decimal('qty', 12, 2)->default(0);      // jumlah yang dititipkan
            $table->decimal('qty_sold', 12, 2)->nullable(); // terjual (diisi saat settle)
            $table->decimal('qty_returned', 12, 2)->nullable(); // sisa/kembali
            $table->decimal('price', 14, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consignment_details');
        Schema::dropIfExists('consignments');
    }
};
