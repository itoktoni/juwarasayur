<?php

namespace Modules\Chatbot\Ai\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Modules\Chatbot\Models\ChatbotSession;
use Modules\Chatbot\Services\CatalogService;
use Stringable;

/**
 * AI tool: tambahkan produk ke keranjang sesi chat.
 */
class AddToCartTool implements Tool
{
    public function __construct(
        private readonly ChatbotSession $session,
        private readonly CatalogService $catalog,
    ) {}

    public function description(): Stringable|string
    {
        return 'Masukkan produk ke keranjang belanja customer. Produk bisa dirujuk lewat number (nomor dari daftar list_products terakhir) atau product_id. Panggil ulang dengan qty tambahan jika customer ingin menambah jumlah produk yang sama.';
    }

    public function handle(Request $request): Stringable|string
    {
        $productId = (int) $request->integer('product_id');

        // Referensi via nomor daftar terakhir ("beli nomer 1")
        $number = $request->integer('number');
        if ($productId <= 0 && $number > 0) {
            $list = is_array($this->session->meta) ? ($this->session->meta['list'] ?? []) : [];
            $productId = (int) ($list[$number - 1] ?? 0);

            if ($productId <= 0) {
                return 'Nomor '.$number.' tidak ada di daftar terakhir. Tampilkan list_products dulu lalu minta customer memilih nomor yang valid.';
            }
        }

        if ($productId <= 0) {
            return 'Sebutkan produk lewat number (nomor daftar terakhir) atau product_id.';
        }

        $qty = max(1, min(999, $request->integer('qty', 1) ?: 1));

        $product = $this->catalog->findByIds([$productId])->get($productId);

        if (! $product) {
            return "Produk dengan id {$productId} tidak ditemukan. Gunakan list_products untuk melihat daftar yang valid.";
        }

        $cart = is_array($this->session->cart) ? $this->session->cart : [];
        $cart[$productId] = min(($cart[$productId] ?? 0) + $qty, 999);
        $this->session->forceFill(['cart' => $cart])->save();

        return sprintf(
            '%s x%d ditambahkan ke keranjang. Isi keranjang sekarang: %s',
            $product->product_nama,
            $qty,
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
        return [
            'number' => $schema->integer()->description('Nomor produk dari daftar list_products terakhir, misal 1 untuk "nomer 1". Gunakan ini ATAU product_id.'),
            'product_id' => $schema->integer()->description('Id produk dari list_products (opsional jika sudah pakai number)'),
            'qty' => $schema->integer()->description('Jumlah yang dibeli, default 1'),
        ];
    }
}
