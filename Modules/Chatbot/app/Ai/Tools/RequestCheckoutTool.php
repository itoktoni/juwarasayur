<?php

namespace Modules\Chatbot\Ai\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Modules\Chatbot\Models\ChatbotSession;
use Modules\Chatbot\Services\CatalogService;
use Stringable;

/**
 * AI tool: mulai proses checkout. Tidak membuat pesanan langsung —
 * sistem akan menampilkan wizard nama/telepon/metode kirim ke customer,
 * lalu pesanan dibuat otomatis setelah semua lengkap.
 */
class RequestCheckoutTool implements Tool
{
    public function __construct(
        private readonly ChatbotSession $session,
        private readonly CatalogService $catalog,
    ) {}

    public function description(): Stringable|string
    {
        return 'Mulai checkout dari isi keranjang. Panggil HANYA setelah customer mengonfirmasi pesanannya. Sistem akan menampilkan form data diri + metode pengiriman, jadi jangan tanyakan nama/telepon/alamat secara manual.';
    }

    public function handle(Request $request): Stringable|string
    {
        $cart = is_array($this->session->cart) ? $this->session->cart : [];

        if (empty($cart)) {
            return 'Keranjang masih kosong, tidak ada yang bisa di-checkout.';
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
            $lines[] = sprintf('%s x%d — Rp %s', $product->product_nama, (int) $qty, number_format($line, 0, ',', '.'));
        }

        // Pindahkan kontrol ke wizard pengiriman (deterministik, bukan AI).
        $this->session->forceFill(['state' => 'shipping'])->save();

        return "__CHECKOUT_WIZARD__\nRingkasan pesanan:\n".implode("\n", $lines)
            ."\nSubtotal: Rp ".number_format($subtotal, 0, ',', '.')
            ."\nSampaikan singkat bahwa customer tinggal melengkapi data pengiriman pada form yang muncul.";
    }

    public function schema(JsonSchema $schema): array
    {
        return [];
    }
}
