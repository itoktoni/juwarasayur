<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('so_orders', function (Blueprint $table) {
            $table->string('so_customer_name')->nullable()->after('so_id_customer');
            $table->string('so_customer_phone', 20)->nullable()->after('so_customer_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('so_orders', function (Blueprint $table) {
            $table->dropColumn(['so_customer_name', 'so_customer_phone']);
        });
    }
};
