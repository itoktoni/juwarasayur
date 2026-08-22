<?php

namespace Modules\So\Providers;

use Illuminate\Support\Facades\Gate;
use Modules\So\Models\So;
use Modules\So\Models\SoDetail;
use Modules\So\Policies\SoDetailPolicy;
use Modules\So\Policies\SoPolicy;
use Nwidart\Modules\Support\ModuleServiceProvider;

class SoServiceProvider extends ModuleServiceProvider
{
    protected string $name = 'So';

    protected string $nameLower = 'so';

    protected array $providers = [
        EventServiceProvider::class,
        RouteServiceProvider::class,
    ];

    public function boot(): void
    {
        parent::boot();

        Gate::policy(So::class, SoPolicy::class);
        Gate::policy(SoDetail::class, SoDetailPolicy::class);
    }
}
