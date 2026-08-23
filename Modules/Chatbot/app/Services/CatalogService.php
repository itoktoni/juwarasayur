<?php

namespace Modules\Chatbot\Services;

use Illuminate\Database\Eloquent\Collection;
use Modules\Catalog\Models\Product;

/**
 * Product lookup for the chatbot — only sellable & active products.
 */
class CatalogService
{
    public function findByName(string $keyword): Collection
    {
        return Product::query()
            ->with('has_satuan')
            ->where('is_active', true)
            ->where('product_status', 'active')
            ->where('product_nama', 'like', '%'.$keyword.'%')
            ->orderBy('product_nama')
            ->limit(10)
            ->get();
    }

    public function findByIds(array $ids): Collection
    {
        if (empty($ids)) {
            return new Collection;
        }

        return Product::query()
            ->whereIn('id', $ids)
            ->where('is_active', true)
            ->get(['id', 'product_nama', 'product_harga', 'product_stok'])
            ->keyBy('id');
    }

    public function priceLabel(Product $product): string
    {
        return 'Rp '.number_format((float) $product->product_harga, 0, ',', '.');
    }
}
