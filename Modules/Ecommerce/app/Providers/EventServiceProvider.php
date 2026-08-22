<?php

namespace Modules\Ecommerce\Providers;

use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Event;
use Modules\Ecommerce\Listeners\MergeSessionCartOnLogin;
use Nwidart\Modules\Support\ModuleServiceProvider;

class EventServiceProvider extends ModuleServiceProvider
{
    protected string $name = 'Ecommerce';

    protected string $nameLower = 'ecommerce';

    public function boot(): void
    {
        parent::boot();

        // Cart session guest → DB user saat login
        Event::listen(Login::class, MergeSessionCartOnLogin::class);
    }
}
