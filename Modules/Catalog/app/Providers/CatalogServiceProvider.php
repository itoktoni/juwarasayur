<?php

namespace Modules\Catalog\Providers;

use Illuminate\Support\Facades\Gate;
use Modules\Catalog\Models\Brand;
use Modules\Catalog\Models\Category;
use Modules\Catalog\Models\Product;
use Modules\Catalog\Models\ProductMaster;
use Modules\Catalog\Models\Satuan;
use Modules\Catalog\Models\Tag;
use Modules\Catalog\Policies\BrandPolicy;
use Modules\Catalog\Policies\CategoryPolicy;
use Modules\Catalog\Policies\ProductMasterPolicy;
use Modules\Catalog\Policies\ProductPolicy;
use Modules\Catalog\Policies\SatuanPolicy;
use Modules\Catalog\Policies\TagPolicy;
use Nwidart\Modules\Support\ModuleServiceProvider;

class CatalogServiceProvider extends ModuleServiceProvider
{
    protected string $name = 'Catalog';

    protected string $nameLower = 'catalog';

    protected array $providers = [
        EventServiceProvider::class,
        RouteServiceProvider::class,
    ];

    public function boot(): void
    {
        parent::boot();

        Gate::policy(Brand::class, BrandPolicy::class);
        Gate::policy(Satuan::class, SatuanPolicy::class);
        Gate::policy(Category::class, CategoryPolicy::class);
        Gate::policy(Tag::class, TagPolicy::class);
        Gate::policy(Product::class, ProductPolicy::class);
        Gate::policy(ProductMaster::class, ProductMasterPolicy::class);
    }
}
