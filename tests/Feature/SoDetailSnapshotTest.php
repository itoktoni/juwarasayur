<?php

use App\Models\User;
use Illuminate\Support\Str;
use Modules\Catalog\Models\Product;
use Modules\So\Models\So;
use Modules\So\Models\SoDetail;

it('persists fee snapshot on so_detail', function () {
    $so = So::create([
        'so_tanggal' => now()->toDateString(),
        'so_id_reseller' => User::factory()->create()->id,
        'so_shipping_method' => 'pickup',
    ]);
    $product = Product::create([
        'product_nama' => 'Snap Test '.uniqid(),
        'product_harga' => 100000,
    ]);
    $detail = SoDetail::create([
        'so_detail_code' => 'DT-'.strtoupper(Str::random(6)),
        'so_detail_id_so' => $so->id,
        'so_detail_id_product' => $product->id,
        'so_detail_qty' => 2,
        'so_detail_harga' => 100000,
        'fee_percent' => 5,
        'fee_amount' => 10000,
        'fee_source' => 'product',
        'applied_role' => 'affiliator',
    ]);
    expect((float) $detail->fresh()->fee_amount)->toBe(10000.00);
    expect((float) $detail->fresh()->fee_percent)->toBe(5.00);
});
