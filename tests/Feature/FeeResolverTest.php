<?php

use App\Enums\UserTypeEnum;
use App\Models\User;
use App\Services\Commission\FeeResolver;
use Modules\Catalog\Models\Product;

it('affiliator uses product fee over user fee', function () {
    config()->set('commission.rate', 2);
    $product = new Product(['product_harga' => 100000, 'affiliator_fee_percent' => 5, 'reseller_fee_percent' => 10]);
    $user = new User(['type' => UserTypeEnum::AFFILIATOR, 'fee' => 3]);
    $res = app(FeeResolver::class)->resolve($product, $user, 2, 100000);
    expect($res->percent)->toBe(5.0);
    expect($res->amount)->toBe(10000.0);
    expect($res->hargaEfektif)->toBe(100000.0);
    expect($res->source)->toBe('product');
    expect($res->role)->toBe('affiliator');
});

it('affiliator fallback to user fee then config', function () {
    config()->set('commission.rate', 2);
    $p = new Product(['affiliator_fee_percent' => null, 'reseller_fee_percent' => null]);
    $u = new User(['type' => UserTypeEnum::AFFILIATOR, 'fee' => 3]);
    expect(app(FeeResolver::class)->resolve($p, $u, 1, 100000)->percent)->toBe(3.0);
    expect(app(FeeResolver::class)->resolve($p, $u, 1, 100000)->source)->toBe('user');
    $u2 = new User(['type' => UserTypeEnum::AFFILIATOR, 'fee' => null]);
    expect(app(FeeResolver::class)->resolve($p, $u2, 1, 100000)->percent)->toBe(2.0);
    expect(app(FeeResolver::class)->resolve($p, $u2, 1, 100000)->source)->toBe('config');
});

it('reseller gets discount price no fee amount', function () {
    $p = new Product(['reseller_fee_percent' => 10, 'affiliator_fee_percent' => 5, 'product_harga' => 100000]);
    $u = new User(['type' => UserTypeEnum::RESELLER]);
    $r = app(FeeResolver::class)->resolve($p, $u, 1, 100000);
    expect($r->percent)->toBe(10.0);
    expect($r->hargaEfektif)->toBe(90000.0);
    expect($r->amount)->toBe(0.0);
    expect($r->role)->toBe('reseller');
});

it('reseller with null fee gives no discount', function () {
    $p = new Product(['reseller_fee_percent' => null]);
    $u = new User(['type' => UserTypeEnum::RESELLER]);
    expect(app(FeeResolver::class)->resolve($p, $u, 1, 50000)->hargaEfektif)->toBe(50000.0);
    expect(app(FeeResolver::class)->resolve($p, $u, 1, 50000)->percent)->toBe(0.0);
});
