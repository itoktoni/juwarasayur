<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    /**
     * Pengaman: RefreshDatabase (migrate:fresh) hanya boleh jalan di DB testing.
     * Mencegah data produksi terhapus bila phpunit.xml tidak aktif / terubah.
     */
    protected function refreshTestDatabase(): void
    {
        $connection = config('database.default');
        $database = (string) config("database.connections.{$connection}.database");

        if (! str_contains($database, 'testing')) {
            throw new RuntimeException(
                "REFUSED: RefreshDatabase mencoba menjalankan migrate:fresh di DB '{$database}'. ".
                'Hanya DB testing yang diizinkan. Periksa phpunit.xml (DB_CONNECTION/DB_DATABASE).'
            );
        }

        parent::refreshTestDatabase();
    }
}
