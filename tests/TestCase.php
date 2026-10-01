<?php

namespace Tests;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    /**
     * Pengaman: trait yang menghancurkan isi DB (migrate:fresh / truncate) hanya
     * boleh jalan di DB testing.
     *
     * Dicek di setUpTraits() — bukan di refreshTestDatabase() — karena test class
     * yang memakai `uses(RefreshDatabase::class)` mengambil method trait itu
     * langsung dan menimpa override-nya, sehingga pengaman tidak pernah jalan.
     * setUpTraits() adalah satu-satunya jalur yang memicu semuanya dan tidak bisa
     * ditimpa oleh penerapan trait.
     */
    protected function setUpTraits()
    {
        if ($this->usesDestructiveDatabaseTrait()) {
            $this->assertSafeTestDatabase();
        }

        return parent::setUpTraits();
    }

    private function usesDestructiveDatabaseTrait(): bool
    {
        $uses = $this->traitsUsedByTest ?? class_uses_recursive(static::class);

        return isset($uses[RefreshDatabase::class])
            || isset($uses[DatabaseMigrations::class])
            || isset($uses[DatabaseTruncation::class]);
    }

    /**
     * Mencegah data produksi/dev terhapus bila phpunit.xml tidak aktif atau
     * environment DB-nya tidak ter-override.
     */
    private function assertSafeTestDatabase(): void
    {
        $connection = config('database.default');
        $database = (string) config("database.connections.{$connection}.database");

        if (! str_contains($database, 'testing')) {
            throw new RuntimeException(
                "REFUSED: migrate:fresh/truncate mencoba jalan di DB '{$database}' (koneksi '{$connection}'). ".
                'Hanya DB yang namanya mengandung "testing" yang diizinkan. '.
                'Pastikan phpunit.xml memakai force="true" pada DB_CONNECTION/DB_DATABASE, '.
                'atau jalankan: DB_DATABASE=whatsapp_testing php artisan test'
            );
        }
    }
}
