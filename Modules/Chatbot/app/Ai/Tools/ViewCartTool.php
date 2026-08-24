<?php

namespace Modules\Chatbot\Ai\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Modules\Chatbot\Models\ChatbotSession;
use Modules\Chatbot\Services\CatalogService;
use Stringable;

/**
 * AI tool: tampilkan isi keranjang + total.
 */
class ViewCartTool implements Tool
{
    public function __construct(
        private readonly ChatbotSession $session,
        private readonly CatalogService $catalog,
    ) {}

    public function description(): Stringable|string
    {
        return 'Lihat isi keranjang belanja customer beserta total harga.';
    }

    public function handle(Request $request): Stringable|string
    {
        $cart = is_array($this->session->cart) ? $this->session->cart : [];

        if (empty($cart)) {
            return 'Keranjang masih kosong.';
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
            $lines[] = sprintf('%s x%d — Rp %s', $product->product_nama, $qty, number_format($line, 0, ',', '.'));
        }

        return "Keranjang:\n".implode("\n", $lines)."\nTotal: Rp ".number_format($subtotal, 0, ',', '.');
    }

    public function schema(JsonSchema $schema): array
    {
        return [];
    }
}
