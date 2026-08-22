<?php

namespace Modules\Ecommerce\Providers;

use Illuminate\Support\Facades\Gate;
use Modules\Ecommerce\Models\CartItem;
use Modules\Ecommerce\Policies\CartPolicy;
use Nwidart\Modules\Support\ModuleServiceProvider;

class EcommerceServiceProvider extends ModuleServiceProvider
{
    protected string $name = 'Ecommerce';

    protected string $nameLower = 'ecommerce';

    protected array $providers = [
        EventServiceProvider::class,
        RouteServiceProvider::class,
    ];

    public function boot(): void
    {
        parent::boot();

        Gate::policy(CartItem::class, CartPolicy::class);
    }
}
