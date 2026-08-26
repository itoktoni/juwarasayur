<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Fee komisi per-reseller (%). NULL = pakai default config commission.rate.
        Schema::table('users', function (Blueprint $table) {
            $table->decimal('fee', 5, 2)->nullable()->after('reference_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('fee');
        });
    }
};
