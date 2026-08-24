<?php

namespace Modules\So\Database\Seeders;

use Illuminate\Database\Seeder;

class SoDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            SoDiscountSeeder::class,
        ]);
    }
}
