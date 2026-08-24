<?php

namespace Modules\Chatbot\Ai\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Modules\Chatbot\Services\CatalogService;
use Stringable;

/**
 * AI tool: cari/daftar produk aktif beserta harga & stok.
 */
class ListProductsTool implements Tool
{
    public function __construct(private readonly CatalogService $catalog) {}

    public function description(): Stringable|string
    {
        return 'Cari produk yang dijual toko. Gunakan saat customer bertanya produk/harga/stok. Tanpa keyword = daftar semua produk. Hasil berisi id, nama, harga, dan stok.';
    }

    public function handle(Request $request): Stringable|string
    {
        $keyword = trim((string) $request->string('keyword', ''));
        $products = $this->catalog->findByName($keyword);

        if ($products->isEmpty()) {
            return 'Tidak ada produk yang cocok dengan "'.$keyword.'".';
        }

        $lines = $products
            ->map(fn ($p) => sprintf(
                '[id %d] %s — %s (stok: %d %s)',
                $p->id,
                $p->product_nama,
                $this->catalog->priceLabel($p),
                (int) $p->product_stok,
                $p->has_satuan?->satuan_nama ?? 'pcs',
            ))
            ->implode("\n");

        return "Produk tersedia:\n".$lines;
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'keyword' => $schema->string()->description('Nama produk yang dicari, misal "tomat". Kosongkan untuk daftar semua.'),
        ];
    }
}
