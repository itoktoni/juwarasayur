<?php

namespace Modules\Chatbot\Ai\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Modules\Chatbot\Models\ChatbotSession;
use Modules\Chatbot\Services\CatalogService;
use Stringable;

/**
 * AI tool: tambahkan semua produk dari daftar terakhir ke keranjang.
 */
class AddAllToCartTool implements Tool
{
    public function __construct(
        private readonly ChatbotSession $session,
        private readonly CatalogService $catalog,
    ) {}

    public function description(): Stringable|string
    {
        return 'Masukkan SEMUA produk dari daftar list_products terakhir ke keranjang belanja sekaligus (qty 1 per produk). '
            .'Gunakan saat customer bilang "semua", "ambil semua", "all", atau menyetujui seluruh rekomendasi paket.';
    }

    public function handle(Request $request): Stringable|string
    {
        $list = is_array($this->session->meta) ? ($this->session->meta['list'] ?? []) : [];

        if (empty($list)) {
            return 'Tidak ada daftar produk yang sedang tampil. Gunakan list_products dulu untuk menampilkan produk.';
        }

        $products = $this->catalog->findByIds($list);
        $cart = is_array($this->session->cart) ? $this->session->cart : [];
        $added = [];

        foreach ($list as $productId) {
            $product = $products->get($productId);

            if (! $product) {
                continue;
            }

            $current = (int) ($cart[$productId] ?? 0);
            $cart[$productId] = $current + 1;
            $added[] = $product->product_nama;
        }

        $this->session->forceFill(['cart' => $cart])->save();

        if (empty($added)) {
            return 'Tidak ada produk yang bisa ditambahkan. Produk mungkin sudah tidak tersedia.';
        }

        return sprintf(
            'Semua %d produk sudah ditambahkan ke keranjang (qty 1 per item): %s. Isi keranjang sekarang: %s',
            count($added),
            implode(', ', $added),
            $this->describeCart($cart),
        );
    }

    private function describeCart(array $cart): string
    {
        if (empty($cart)) {
            return 'kosong';
        }

        $products = $this->catalog->findByIds(array_keys($cart));
        $subtotal = 0.0;
        $lines = [];

        foreach ($cart as $id => $qty) {
            $product = $products->get($id);

            if (! $product) {
                continue;
            }

            $line = (float) $qty * (float) $product->product_harga;
            $subtotal += $line;
            $lines[] = sprintf('%s x%d (%s)', $product->product_nama, $qty, 'Rp '.number_format($line, 0, ',', '.'));
        }

        return implode(', ', $lines).'. Total '.number_format($subtotal, 0, ',', '.');
    }

    public function schema(JsonSchema $schema): array
    {
        return [];
    }
}
