<?php

namespace Modules\Inventory\Providers;

use Illuminate\Support\Facades\Gate;
use Modules\Inventory\Models\Gudang;
use Modules\Inventory\Models\Lokasi;
use Modules\Inventory\Models\Stock;
use Modules\Inventory\Models\StockMovement;
use Modules\Inventory\Policies\GudangPolicy;
use Modules\Inventory\Policies\LokasiPolicy;
use Modules\Inventory\Policies\StockMovementPolicy;
use Modules\Inventory\Policies\StockPolicy;
use Nwidart\Modules\Support\ModuleServiceProvider;

class InventoryServiceProvider extends ModuleServiceProvider
{
    protected string $name = 'Inventory';

    protected string $nameLower = 'inventory';

    protected array $providers = [
        EventServiceProvider::class,
        RouteServiceProvider::class,
    ];

    public function boot(): void
    {
        parent::boot();

        Gate::policy(Gudang::class, GudangPolicy::class);
        Gate::policy(Lokasi::class, LokasiPolicy::class);
        Gate::policy(Stock::class, StockPolicy::class);
        Gate::policy(StockMovement::class, StockMovementPolicy::class);
    }
}
