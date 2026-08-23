<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Biaya tambahan per work order (parkir, konsumsi, dll)
        Schema::create('production_costs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('production_cost_id_production')->constrained('productions')->cascadeOnDelete();
            $table->string('production_cost_nama', 100);
            $table->decimal('production_cost_nominal', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('production_costs');
    }
};
