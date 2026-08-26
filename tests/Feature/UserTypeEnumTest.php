<?php

use App\Enums\UserTypeEnum;
use App\Models\User;

it('has affiliator type', function () {
    expect(UserTypeEnum::hasValue('affiliator'))->toBeTrue();
    expect(UserTypeEnum::AFFILIATOR)->toBe('affiliator');
    expect(UserTypeEnum::getDescription('affiliator'))->toBe('Affiliator');
});

it('user can check isAffiliator', function () {
    $u = new User(['type' => UserTypeEnum::AFFILIATOR]);
    expect($u->isAffiliator())->toBeTrue();
    expect($u->isReseller())->toBeFalse();
    $r = new User(['type' => UserTypeEnum::RESELLER]);
    expect($r->isAffiliator())->toBeFalse();
});
