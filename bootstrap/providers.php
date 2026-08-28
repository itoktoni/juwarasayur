<?php

use App\Providers\AppServiceProvider;
use App\Providers\EnvKitTrustProxies;
use App\Providers\FortifyServiceProvider;
use App\Providers\ModelAliasServiceProvider;
use Laravel\Dusk\DuskServiceProvider;

return [
    EnvKitTrustProxies::class,
    AppServiceProvider::class,
    FortifyServiceProvider::class,
    ModelAliasServiceProvider::class,
    DuskServiceProvider::class,
];
