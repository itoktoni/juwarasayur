<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

function seedSoUpdateScenario(): array
{
    $admin = User::create([
        'name' => 'Admin',
        'email' => 'admin@example.com',
        'password' => Hash::make('password123'),
        'role' => 'admin',
        'verified_at' => now(),
    ]);

    $reseller = User::create([
        'name' => 'Reseller',
        'email' => 'reseller@example.com',
        'password' => Hash::make('password123'),
        'role' => 'user',
        'type' => 'reseller',
        'verified_at' => now(),
    ]);

    $customer = User::create([
        'name' => 'Customer',
        'email' => 'customer@example.com',
        'password' => Hash::make('password123'),
        'role' => 'user',
        'type' => 'customer',
        'reference_id' => $reseller->id,
        'verified_at' => now(),
    ]);

    $productId = DB::table('catalog_products')->insertGetId([
        'product_nama' => 'Produk Uji',
        'product_harga' => 10000,
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $soId = DB::table('so_orders')->insertGetId([
        'so_code' => 'SO-TEST-0001',
        'so_tanggal' => now()->toDateString(),
        'so_id_reseller' => $reseller->id,
        'so_id_customer' => $customer->id,
        'so_status' => 'pending',
        'so_shipping_method' => 'pickup',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return compact('admin', 'reseller', 'customer', 'productId', 'soId');
}

it('allows admin to update an SO with empty reseller by keeping the existing reseller', function () {
    $s = seedSoUpdateScenario();

    $this->actingAs($s['admin'])
        ->post("/admin/so/so/update/{$s['soId']}", [
            'so_tanggal' => now()->toDateString(),
            'so_id_customer' => (string) $s['customer']->id,
            'so_status' => 'pending',
            'so_shipping_method' => 'pickup',
            'details' => [
                ['so_detail_id_product' => (string) $s['productId'], 'so_detail_qty' => '2'],
            ],
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('so_orders', [
        'id' => $s['soId'],
        'so_id_reseller' => $s['reseller']->id,
    ]);
});

it('still blocks a reseller from ordering for another reseller customer', function () {
    $s = seedSoUpdateScenario();

    $otherReseller = User::create([
        'name' => 'Other Reseller',
        'email' => 'other@example.com',
        'password' => Hash::make('password123'),
        'role' => 'user',
        'type' => 'reseller',
        'verified_at' => now(),
    ]);

    $this->actingAs($s['admin'])
        ->post("/admin/so/so/update/{$s['soId']}", [
            'so_tanggal' => now()->toDateString(),
            'so_id_reseller' => (string) $otherReseller->id,
            'so_id_customer' => (string) $s['customer']->id,
            'so_status' => 'pending',
            'so_shipping_method' => 'pickup',
            'details' => [
                ['so_detail_id_product' => (string) $s['productId'], 'so_detail_qty' => '1'],
            ],
        ])
        ->assertStatus(422);
});
