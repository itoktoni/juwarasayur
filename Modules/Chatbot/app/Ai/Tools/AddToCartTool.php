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
        return 'Masukkan produk ke keranjang belanja customer. Gunakan id dari hasil list_products. Panggil ulang dengan qty tambahan jika customer ingin menambah jumlah produk yang sama.';
    }

    public function handle(Request $request): Stringable|string
    {
        $productId = $request->integer('product_id');
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
            'product_id' => $schema->integer()->description('Id produk dari list_products')->required(),
            'qty' => $schema->integer()->description('Jumlah yang dibeli, default 1'),
        ];
    }
}
