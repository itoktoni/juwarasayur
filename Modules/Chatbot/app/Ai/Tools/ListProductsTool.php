<?php

namespace Modules\Chatbot\Ai\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Modules\Chatbot\Models\ChatbotSession;
use Modules\Chatbot\Services\CatalogService;
use Stringable;

/**
 * AI tool: cari/daftar produk aktif beserta harga & stok.
 * Daftar diberi NOMOR dan mapping nomor → id disimpan di sesi,
 * agar customer bisa memesan dengan menyebut nomor (misal "nomer 1, 3kg").
 */
class ListProductsTool implements Tool
{
    public function __construct(
        private readonly CatalogService $catalog,
        private readonly ChatbotSession $session,
    ) {}

    public function description(): Stringable|string
    {
        return 'Cari produk yang dijual toko. Gunakan saat customer bertanya produk/harga/stok. '
            .'sort=murah untuk customer minta produk murah/termurah/promo, sort=terbaru untuk produk baru, flash=1 khusus produk unggulan/flash sale. '
            .'Daftar hasil diberi HURUF (a, b, c) — customer bisa memesan dengan menyebut hurufnya.';
    }

    public function handle(Request $request): Stringable|string
    {
        $keyword = trim((string) $request->string('keyword', ''));
        $sort = (string) $request->string('sort', 'nama');
        $flash = filter_var($request->string('flash', ''), FILTER_VALIDATE_BOOLEAN);
        $products = $this->catalog->search($keyword !== '' ? $keyword : null, $sort, $flash);

        if ($products->isEmpty()) {
            return 'Tidak ada produk yang cocok dengan "'.$keyword.'".';
        }

        // Simpan mapping nomor → product_id di sesi untuk referensi "nomer X"
        // + penanda agar controller merender kartu produk interaktif.
        $this->session->forceFill([
            'meta' => array_merge(is_array($this->session->meta) ? $this->session->meta : [], [
                'list' => $products->pluck('id')->values()->all(),
                'list_at' => now()->format('u'),
            ]),
        ])->save();

        $lines = $products
            ->values()
            ->map(fn ($p, $i) => sprintf(
                '%d. %s — %s',
                $i + 1,
                $p->product_nama,
                $this->catalog->priceLabel($p),
            ))
            ->implode("\n");

        return "Produk tersedia:\n".$lines."\n\nCustomer bisa memesan dengan menyebut nomornya.";
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'keyword' => $schema->string()->description('Nama produk yang dicari, misal "tomat". Kosongkan untuk daftar semua.'),
            'sort' => $schema->string()->description('Urutan hasil: "murah" (harga termurah), "mahal", "terbaru", atau kosongkan untuk nama.'),
            'flash' => $schema->string()->description('"1" untuk hanya produk unggulan/flash sale'),
        ];
    }
}
