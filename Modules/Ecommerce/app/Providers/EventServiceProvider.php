<?php

namespace Modules\Ecommerce\Providers;

use Nwidart\Modules\Support\ModuleServiceProvider;

class EventServiceProvider extends ModuleServiceProvider
{
    protected string $name = 'Ecommerce';

    protected string $nameLower = 'ecommerce';

    public function boot(): void
    {
        parent::boot();
    }
}
