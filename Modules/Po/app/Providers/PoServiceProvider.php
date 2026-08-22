<?php

namespace Modules\Po\Providers;

use Illuminate\Support\Facades\Gate;
use Modules\Po\Models\Po;
use Modules\Po\Models\PoDetail;
use Modules\Po\Models\Supplier;
use Modules\Po\Policies\PoDetailPolicy;
use Modules\Po\Policies\PoPolicy;
use Modules\Po\Policies\SupplierPolicy;
use Nwidart\Modules\Support\ModuleServiceProvider;

class PoServiceProvider extends ModuleServiceProvider
{
    protected string $name = 'Po';

    protected string $nameLower = 'po';

    protected array $providers = [
        EventServiceProvider::class,
        RouteServiceProvider::class,
    ];

    public function boot(): void
    {
        parent::boot();

        Gate::policy(Supplier::class, SupplierPolicy::class);
        Gate::policy(Po::class, PoPolicy::class);
        Gate::policy(PoDetail::class, PoDetailPolicy::class);
    }
}
