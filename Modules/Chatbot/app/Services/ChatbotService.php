<?php

namespace Modules\Chatbot\Services;

use Illuminate\Support\Str;
use Modules\Chatbot\Models\ChatbotMessage;
use Modules\Chatbot\Models\ChatbotSession;

/**
 * Chatbot order conversation brain for WhatsApp & Telegram.
 *
 * - Identitas customer unik per channel:
 *     Telegram → telegram id; WhatsApp → notelp.
 * - Cari harga sayur di table product (catalog_products).
 * - Setelah belanjaan ditambah, membangun So order (so_orders) + SoDetail
 *   lalu dikirim link pembayaran QRIS.
 */
class ChatbotService
{
    public function __construct(
        private readonly ChatbotSessionService $sessions,
        private readonly CatalogService $catalog,
        private readonly ChatbotOrderService $orders,
    ) {}

    public function respond(string $channel, string $messengerUser, ?string $text, ?string $phone = null): string
    {
        $session = $this->sessions->findOrCreate($channel, $messengerUser);

        // Log percakapan untuk riwayat admin
        if ($text !== null && trim($text) !== '') {
            ChatbotMessage::log($session, 'user', $text);
        }

        $reply = $this->handleRespond($session, $channel, $text, $phone);

        ChatbotMessage::log($session, 'assistant', $reply);

        return $reply;
    }

    private function handleRespond(ChatbotSession $session, string $channel, ?string $text, ?string $phone = null): string
    {
        $text = trim(Str::lower((string) $text));

        if (! empty($phone) && empty($session->contact_phone)) {
            $session->forceFill(['contact_phone' => $phone])->save();
        }

        // ---- Kontak pertama: kolekt nama customer ----
        if (empty($session->contact_name)) {
            if ($session->state === 'awaiting_name') {
                if (Str::length($text) < 2) {
                    return 'Maaf, nama kamu kurang jelas. Ketik ulang ya.';
                }

                $session->forceFill([
                    'contact_name' => ucwords($text),
                    'last_active_at' => now(),
                ])->save();
                $this->sessions->setState($session, 'ordering');

                return $this->welcome($session);
            }

            $this->sessions->setState($session, 'awaiting_name', ['step' => 'name']);

            return $this->askName($channel);
        }

        // ---- Konfirmasi akhir ----
        if ($session->state === 'confirming') {
            return $this->isAffirmative($text) ? $this->placeOrder($session) : $this->declineConfirm($session);
        }

        // ---- Perintah cepat ----
        $command = $this->matchCommand($text);
        if ($command !== null) {
            return match ($command) {
                'start' => $this->welcome($session),
                'menu' => $this->menu(),
                'cart' => $this->showCart($session),
                'checkout' => $this->startCheckout($session),
                'cancel' => $this->clearCart($session),
                default => $this->menu(),
            };
        }

        // ---- Input angka: pilih produk / set qty ----
        if (is_numeric($text)) {
            if ($session->state === 'picking') {
                return $this->pickProduct($session, (int) $text);
            }

            if ($session->state === 'awaiting_qty') {
                return $this->addSelected($session, (int) $text);
            }
        }

        // ---- Tambah semua produk yang sedang terlist ke keranjang ----
        if (in_array($text, ['semua', 'all', 'ambil semua']) && $session->state === 'picking') {
            return $this->addAllToListed($session);
        }

        // ---- Cari sayur (harga di table product) ----
        return $this->searchProducts($session, $text);
    }

    private function askName(string $channel): string
    {
        $greeting = $channel === 'whatsapp' ? 'Assalamualaikum 👋' : 'Halo 👋';

        return "{$greeting} Selamat datang di *Warung Sayur Online*!\n\n"
            .'Boleh kenalan dulu, siapa nama kamu?'.PHP_EOL
            .'Ketik nama kamu ya (misal: *Budi*).';
    }

    private function welcome(ChatbotSession $session): string
    {
        $name = (string) head(explode(' ', (string) $session->contact_name));

        return "Halo *{$name}*! 👋 Selamat datang di *Warung Sayur Online*.\n\n".$this->menu();
    }

    private function menu(): string
    {
        return "Ini menu belanja sayur kami:\n\n"
            .'🍅 *Cari sayur* — ketik nama sayur, misal *tomat* atau *kangkung*'.PHP_EOL
            .'🛒 *keranjang* — lihat daftar belanjaan'.PHP_EOL
            .'✅ *checkout* — selesaikan pesanan & bisa link pembayaran'.PHP_EOL
            .'🗑 *batal* — kosongkan keranjang'.PHP_EOL
            .'❓ *bantuan* — tampilkan menu ini'.PHP_EOL.PHP_EOL
            .'Ada yang bisa saya bantu hari ini?';
    }

    private function declineConfirm(ChatbotSession $session): string
    {
        $this->sessions->setState($session, 'ordering');

        return $this->showCart($session).PHP_EOL.PHP_EOL
            .'Kalau sudah yakin, ketik *checkout*. Atau tambah belanjaan lagi.';
    }

    private function searchProducts(ChatbotSession $session, string $keyword): string
    {
        $products = $this->catalog->findByName($keyword);

        if ($products->isEmpty()) {
            return "Sayur dengan nama *{$keyword}* belum ketemu 🙏\n\n"
                .'Coba ketik nama lain, contoh: *tomat*, *cabai*, *kangkung*.'
                .' Untuk lihat menu ketik *bantuan*.';
        }

        if ($products->count() === 1) {
            $product = $products->first();
            $this->sessions->setState($session, 'awaiting_qty', [
                'product_id' => $product->id,
                'product_name' => $product->product_nama,
            ]);

            return "Ada! *{$product->product_nama}* harga {$this->price($product->product_harga)} per unit.\n\n"
                .'Mau berapa? Ketik jumlahnya (misal *2*).';
        }

        $lines = $products
            ->values()
            ->map(fn ($p, $i) => '*'.($i + 1)."*. {$p->product_nama} — {$this->price($p->product_harga)}")
            ->implode(PHP_EOL);

        $this->sessions->setState($session, 'picking', [
            'list' => $products->pluck('id')->values()->all(),
        ]);

        return "Beberapa pilihan cocok:\n\n{$lines}\n\nKetik nomornya untuk memilih, misal *1*.";
    }

    private function pickProduct(ChatbotSession $session, int $index): string
    {
        $ids = (array) ($session->meta['list'] ?? []);
        $productId = $ids[$index - 1] ?? null;

        if ($productId === null) {
            return 'Nomor tidak valid. Ketik nomor benar ketik cari sayur lagi.';
        }

        $product = $this->catalog->findByIds([$productId])->get($productId);

        if (! $product) {
            return 'Produk tidak tersedia lagi. Coba cari belanjaan yang lain.';
        }

        $this->sessions->setState($session, 'awaiting_qty', [
            'product_id' => $product->id,
            'product_name' => $product->product_nama,
        ]);

        return "Pilihan bagus! *{$product->product_nama}* harga {$this->price($product->product_harga)} per unit.\n\n"
            .'Berapa banyak? Ketik jumlahnya (misal *3*).';
    }

    private function addAllToListed(ChatbotSession $session): string
    {
        $ids = (array) ($session->meta['list'] ?? []);

        if (empty($ids)) {
            $this->sessions->setState($session, 'ordering');

            return 'Tidak ada produk yang sedang terlist. Ketik nama sayur untuk mencari.';
        }

        $products = $this->catalog->findByIds($ids);
        $cart = is_array($session->cart) ? $session->cart : [];
        $added = [];

        foreach ($ids as $productId) {
            $product = $products->get($productId);
            if (! $product) {
                continue;
            }
            $current = (int) ($cart[$productId] ?? 0);
            $cart[$productId] = $current + 1;
            $added[] = $product->product_nama;
        }

        $this->sessions->saveCart($session, $cart);
        $this->sessions->setState($session, 'ordering');

        return '✅ *Semua produk sudah masuk keranjang (qty 1 per item):*'.PHP_EOL
            .implode(PHP_EOL, array_map(fn ($name) => "- {$name}", $added)).PHP_EOL.PHP_EOL
            .$this->showCart($session);
    }

    private function addSelected(ChatbotSession $session, int $qty): string
    {
        if ($qty < 1 || $qty > 999) {
            return 'Jumlah maksimal 999. Ketik ulang ya.';
        }

        $productId = (int) ($session->meta['product_id'] ?? 0);
        $productName = (string) ($session->meta['product_name'] ?? '');

        if ($productId <= 0) {
            $this->sessions->setState($session, 'ordering');

            return 'Oops, sesi pilihan berubah. Cari sayur lagi ya.';
        }

        $cart = is_array($session->cart) ? $session->cart : [];
        $current = (int) ($cart[$productId] ?? 0);
        $cart[$productId] = min($current + $qty, 999);

        $this->sessions->saveCart($session, $cart);
        $this->sessions->setState($session, 'ordering');

        return "*{$productName}* jumlah {$qty} sudah masuk keranjang ✅\n\n{$this->showCart($session)}";
    }

    private function showCart(ChatbotSession $session): string
    {
        $cart = is_array($session->cart) ? $session->cart : [];

        if (empty($cart)) {
            return '🛒 Keranjang masih kosong. Ketik nama sayur untuk mulai belanja.';
        }

        $products = $this->catalog->findByIds(array_keys($cart));
        $lines = [];
        $subtotal = 0.0;

        foreach ($cart as $productId => $qty) {
            $product = $products->get($productId);

            if (! $product) {
                continue;
            }

            $lineTotal = (int) $qty * (float) $product->product_harga;
            $subtotal += $lineTotal;
            $lines[] = "*{$product->product_nama} x{$qty} — {$this->price($lineTotal)}";
        }

        return '🛒 *Keranjang kamu:*'.PHP_EOL
            .implode(PHP_EOL, $lines).PHP_EOL.PHP_EOL
            .'Total *'.$this->price($subtotal).'*'.PHP_EOL
            .'Ketik *checkout* untuk lanjut ke pembayaran, *batal* untuk kosongkan.';
    }

    private function startCheckout(ChatbotSession $session): string
    {
        $cart = is_array($session->cart) ? $session->cart : [];

        if (empty($cart)) {
            return $this->showCart($session);
        }

        $this->sessions->setState($session, 'confirming', ['step' => 'confirm']);

        return $this->showCart($session).PHP_EOL.PHP_EOL
            .'*Yakin kamu?* Konfirmasi pesanan ini untuk buat link pembayaran QRIS.'.PHP_EOL
            .'Ketik *ya* untuk setuju, atau *batal* untuk membatalkan.';
    }

    private function placeOrder(ChatbotSession $session): string
    {
        try {
            $result = $this->orders->createOrder($session, (array) $session->cart ?? []);
        } catch (\Throwable $e) {
            return 'Ups, gagal membuat pesanan 😥 coba ulangi nanti ya!';
        }

        $so = $result['so']->load(['has_details.has_product']);
        $lines = $so->has_details
            ->map(fn ($d) => "*{$d->has_product?->product_nama} x{$d->so_detail_qty}* — {$this->price($d->so_detail_qty * $d->so_detail_harga)}")
            ->implode(PHP_EOL);

        $this->sessions->saveCart($session, []);
        $this->sessions->setState($session, 'ordering');

        return "🎉 *Pesanan berhasil!* ➡️ *{$so->so_code}*\n\n"
            .$lines.PHP_EOL.PHP_EOL
            .'Total *'.$this->price($so->so_grand_total).'*'.PHP_EOL.PHP_EOL
            .'💳 *Selesaikan pembayaran QRIS di link ini:*'.PHP_EOL
            .$result['payment_url'].PHP_EOL.PHP_EOL
            .'Setelah kamu bayar, status pesanan otomatis *Dibayar (PAID)* dan kami akan proses.'.PHP_EOL
            .'Terima kasih 🥰';
    }

    private function clearCart(ChatbotSession $session): string
    {
        $this->sessions->saveCart($session, []);
        $this->sessions->setState($session, 'ordering');

        return '🗑 Keranjang sudah dikosongkan. Mulai belanja lagi kapan saja ya.';
    }

    private function price(mixed $value): string
    {
        return 'Rp '.number_format((float) $value, 0, ',', '.');
    }

    private function matchCommand(string $text): ?string
    {
        $text = trim(ltrim($text, "/!#. \t\r\n"));

        if ($text === '') {
            return null;
        }

        $map = [
            'start' => ['start', 'menu', 'mulai', 'hello', 'halo', 'hai', 'hi', 'assalam', 'wassalam'],
            'menu' => ['bantuan', 'help'],
            'cart' => ['cart', 'keranjang', 'liat', 'lihat', 'list', 'tart'],
            'checkout' => ['checkout', 'cekout', 'confirm', 'konfirmasi', 'order', 'pesan', 'belanjaan'],
            'cancel' => ['cancel', 'batal', 'hapus', 'clear', 'reset', 'kosongkan'],
        ];

        foreach ($map as $key => $words) {
            if (in_array($text, $words, true)) {
                return $key;
            }
        }

        return null;
    }

    private function isAffirmative(string $text): bool
    {
        return in_array($text, ['ya', 'yes', 'ok', 'oke', 'setuju', 'iya', 'konfirm', 'jaldi'], true);
    }
}
