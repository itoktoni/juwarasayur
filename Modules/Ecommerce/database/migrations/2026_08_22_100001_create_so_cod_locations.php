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
        Schema::create('so_cod_locations', function (Blueprint $table) {
            $table->id();
            $table->string('location_name', 100);
            $table->string('address')->nullable();
            $table->decimal('lat', 10, 7);
            $table->decimal('lng', 10, 7);
            // Fee flat per titik (dipakai modul SO). Null = ongkir dihitung dari jarak.
            $table->decimal('fee', 12, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique('location_name');
        });

        // Migrasi data awal dari config lama (so.shipping.cod_locations)
        $legacy = collect(config('so.shipping.cod_locations', []));

        foreach ($legacy as $i => $loc) {
            if (empty($loc['name']) || ! isset($loc['lat'], $loc['lng'])) {
                continue;
            }

            DB::table('so_cod_locations')->insert([
                'location_name' => (string) $loc['name'],
                'address' => null,
                'lat' => (float) $loc['lat'],
                'lng' => (float) $loc['lng'],
                'fee' => isset($loc['fee']) ? (float) $loc['fee'] : null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('so_cod_locations');
    }
};
