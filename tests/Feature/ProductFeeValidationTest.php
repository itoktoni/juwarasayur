<?php

use Modules\Catalog\Models\Product;

it('validates fee percent between 0 and 100', function () {
    $p = new Product;
    $rules = $p->rules();
    expect($rules['reseller_fee_percent'])->toContain('between:0,100');
    expect($rules['affiliator_fee_percent'])->toContain('between:0,100');
});

it('persists product fees', function () {
    $product = Product::create([
        'product_nama' => 'Test Fee Product '.uniqid(),
        'product_harga' => 100000,
        'reseller_fee_percent' => 10.50,
        'affiliator_fee_percent' => 5.00,
    ]);
    expect((float) $product->fresh()->reseller_fee_percent)->toBe(10.50);
    expect((float) $product->fresh()->affiliator_fee_percent)->toBe(5.00);
});
