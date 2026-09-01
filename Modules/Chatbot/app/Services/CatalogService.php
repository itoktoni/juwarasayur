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
        return $this->search($keyword);
    }

    /**
     * Pencarian fleksibel untuk chatbot AI.
     *
     * @param  string|null  $keyword  nama produk (kosong = semua)
     * @param  string  $sort  'nama'|'murah'|'mahal'|'terbaru'
     * @param  bool  $featuredOnly  hanya produk unggulan / flash sale
     */
    public function search(?string $keyword = null, string $sort = 'nama', bool $featuredOnly = false): Collection
    {
        return Product::query()
            ->with('has_satuan')
            ->where('is_active', true)
            ->where('product_status', 'active')
            ->when($keyword !== null && $keyword !== '', function ($q) use ($keyword) {
                $stopwords = ['cabe', 'cabai', 'sayur', 'sayuran', 'beli', 'belikan', 'cari', 'cariin', 'mau', 'yg', 'yang'];
                $words = array_values(array_filter(explode(' ', strtolower($keyword)), fn ($w) => ! in_array($w, $stopwords, true) && mb_strlen($w) > 1));

                foreach ($words as $word) {
                    $q->whereRaw('LOWER(product_nama) LIKE ?', ['%'.$word.'%']);
                }
            })
            ->when($featuredOnly, fn ($q) => $q->where('is_featured', true))
            ->when($sort === 'murah', fn ($q) => $q->orderBy('product_harga'))
            ->when($sort === 'mahal', fn ($q) => $q->orderByDesc('product_harga'))
            ->when($sort === 'terbaru', fn ($q) => $q->orderByDesc('id'))
            ->when(! in_array($sort, ['murah', 'mahal', 'terbaru']), fn ($q) => $q->orderBy('product_nama'))
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
