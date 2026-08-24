<?php

namespace Modules\Chatbot\Ai\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Modules\Chatbot\Models\ChatbotSession;
use Modules\Chatbot\Services\CatalogService;
use Modules\Chatbot\Services\ChatbotOrderService;
use Stringable;

/**
 * AI tool: finalisasi pesanan — buat order + link pembayaran QRIS.
 */
class CheckoutTool implements Tool
{
    public function __construct(
        private readonly ChatbotSession $session,
        private readonly CatalogService $catalog,
        private readonly ChatbotOrderService $orders,
    ) {}

    public function description(): Stringable|string
    {
        return 'Selesaikan pesanan: buat order dari isi keranjang dan dapatkan link pembayaran QRIS. Hanya panggil setelah customer mengonfirmasi pesanannya.';
    }

    public function handle(Request $request): Stringable|string
    {
        $cart = is_array($this->session->cart) ? $this->session->cart : [];

        if (empty($cart)) {
            return 'Keranjang masih kosong, tidak ada yang bisa di-checkout.';
        }

        try {
            $result = $this->orders->createOrder($this->session, $cart);
        } catch (\Throwable $e) {
            report($e);

            return 'Gagal membuat pesanan. Beri tahu customer untuk mencoba beberapa saat lagi.';
        }

        $so = $result['so']->load(['has_details.has_product']);
        $this->session->forceFill(['cart' => null])->save();

        $lines = $so->has_details
            ->map(fn ($d) => sprintf(
                '%s x%d — Rp %s',
                $d->has_product?->product_nama ?? '-',
                (int) $d->so_detail_qty,
                number_format((float) ($d->so_detail_qty * $d->so_detail_harga), 0, ',', '.'),
            ))
            ->implode("\n");

        return sprintf(
            "Pesanan %s BERHASIL dibuat.\n%s\nTotal bayar: Rp %s\nLink pembayaran QRIS (tampilkan apa adanya): %s\nSampaikan ke customer bahwa status otomatis PAID setelah QRIS discan.",
            $so->so_code,
            $lines,
            number_format((float) $result['grand_total'], 0, ',', '.'),
            $result['payment_url'],
        );
    }

    public function schema(JsonSchema $schema): array
    {
        return [];
    }
}
