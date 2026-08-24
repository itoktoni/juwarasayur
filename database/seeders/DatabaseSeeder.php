<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Catalog\Database\Seeders\CatalogDatabaseSeeder;
use Modules\Inventory\Database\Seeders\InventoryDatabaseSeeder;
use Modules\Po\Database\Seeders\PoDatabaseSeeder;
use Modules\So\Database\Seeders\SoDatabaseSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(UserSeeder::class);
        $this->call(MenuSeeder::class);
        $this->call(CatalogDatabaseSeeder::class);
        $this->call(PoDatabaseSeeder::class);
        $this->call(InventoryDatabaseSeeder::class);
        $this->call(SayurBusinessSeeder::class);
        $this->call(SoDatabaseSeeder::class);
    }
}
