<?php

use App\Providers\AppServiceProvider;
use App\Providers\FortifyServiceProvider;
use App\Providers\ModelAliasServiceProvider;
use Laravel\Dusk\DuskServiceProvider;

return [
    AppServiceProvider::class,
    FortifyServiceProvider::class,
    ModelAliasServiceProvider::class,
    DuskServiceProvider::class,
];
