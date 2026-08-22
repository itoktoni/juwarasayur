<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Modules\Catalog\Database\Seeders\CatalogDatabaseSeeder;
use Modules\Inventory\Database\Seeders\InventoryDatabaseSeeder;
use Modules\Po\Database\Seeders\PoDatabaseSeeder;

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
    }
}
