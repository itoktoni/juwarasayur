<?php

namespace Modules\Chatbot\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Laravel\Ai\Messages\Message;
use Modules\Catalog\Models\Product;
use Modules\Chatbot\Ai\Tools\AddToCartTool;
use Modules\Chatbot\Ai\Tools\ListProductsTool;
use Modules\Chatbot\Ai\Tools\RequestCheckoutTool;
use Modules\Chatbot\Ai\Tools\SearchFaqTool;
use Modules\Chatbot\Ai\Tools\ViewCartTool;
use Modules\Chatbot\Models\ChatbotMessage;
use Modules\Chatbot\Models\ChatbotSession;
use Modules\Chatbot\Models\WebChatMessage;
use Modules\Chatbot\Services\CatalogService;
use Modules\Chatbot\Services\ChatbotOrderService;
use Modules\Chatbot\Services\ChatbotSessionService;
use Modules\Ecommerce\Models\CodLocation;
use Modules\Ecommerce\Services\CodShippingService;

use function Laravel\Ai\agent;

/**
 * Halaman chat WhatsApp-like untuk pengunjung (guest).
 * Identitas anonim per browser via cookie chat_web_token.
 * Balasan memakai Laravel AI SDK (laravel/ai) — flow pemesanan
 * WA/Telegram yang lama tidak diubah.
 */
class WebChatController extends Controller
{
    private const COOKIE = 'chat_web_token';

    private const HISTORY_LIMIT = 30;

    public function index(Request $request): View
    {
        $token = $this->sessionToken($request);
        Cookie::queue(self::COOKIE, $token, 60 * 24 * 365);

        $messages = WebChatMessage::where('session_token', $token)
            ->orderBy('id')
            ->limit(100)
            ->get();

        return view('chatbot::chat.whatsapp', [
            'messages' => $messages,
        ]);
    }

    public function send(Request $request): JsonResponse
    {
        $request->validate([
            'message' => ['required', 'string', 'max:2000'],
        ]);

        $token = $this->sessionToken($request);
        Cookie::queue(self::COOKIE, $token, 60 * 24 * 365);

        // Rate limit sederhana per sesi: 20 pesan/menit
        $executed = RateLimiter::attempt(
            key: 'chat-web:'.$token,
            maxAttempts: 20,
            callback: fn () => true,
            decaySeconds: 60,
        );

        if (! $executed) {
            return response()->json([
                'reply' => 'Sabar ya, kamu mengirim pesan terlalu cepat 😊 coba lagi sebentar lagi.',
                'throttled' => true,
            ], 429);
        }

        $userMessage = trim($request->input('message'));

        // Intent daftar produk → langsung tampilkan picker interaktif (tanpa AI),
        // supaya tidak bisa "bocor" jadi daftar teks dari ingatan model.
        if ($this->isProductListingIntent($userMessage)) {
            return $this->respondWithProductPicker($token);
        }

        // Sapaan → balasan instan dengan link katalog
        if ($this->isGreetingIntent($userMessage)) {
            $reply = 'Halo kak! 👋 Mau belanja? Langsung sebut sayur & sembako yang dicari, '
                .'atau ketik *"sayur apa aja"* untuk lihat daftar produk — aku bantu! 😊';

            WebChatMessage::create(['session_token' => $token, 'role' => 'user', 'content' => $userMessage]);
            WebChatMessage::create(['session_token' => $token, 'role' => 'assistant', 'content' => $reply]);

            // Log ke riwayat admin (channel web)
            $chatSession = $this->chatbotSession($token);
            ChatbotMessage::log($chatSession, 'user', $userMessage);
            ChatbotMessage::log($chatSession, 'assistant', $reply);

            return response()->json(['reply' => $reply]);
        }

        $history = WebChatMessage::where('session_token', $token)
            ->orderByDesc('id')
            ->limit(self::HISTORY_LIMIT)
            ->get()
            ->reverse()
            ->map(fn ($m) => new Message($m->role, (string) $m->content))
            ->values()
            ->all();

        try {
            // Model eksplisit dari config (wajib untuk driver openai-compatible)
            $model = trim((string) config('ai.chat_model', '')) ?: null;

            $session = $this->chatbotSession($token);
            $catalog = app(CatalogService::class);

            $metaBefore = is_array($session->meta) ? ($session->meta['list_at'] ?? null) : null;
            $stateBefore = $session->state;

            $response = agent($this->instructions(), messages: $history, tools: [
                new ListProductsTool($catalog, $session),
                new AddToCartTool($session, $catalog),
                new ViewCartTool($session, $catalog),
                new RequestCheckoutTool($session, $catalog),
                new SearchFaqTool,
            ])->prompt($userMessage, model: $model);

            $reply = trim((string) $response->text);

            if ($reply === '') {
                $reply = 'Maaf, saya belum bisa menjawab itu. Coba tanya dengan cara lain ya 😊';
            }

            // Marker wizard / transisi state → frontend menampilkan form pengiriman.
            $session->refresh();
            $listAtAfter = is_array($session->meta) ? ($session->meta['list_at'] ?? null) : null;
            $wizard = str_contains($reply, '__CHECKOUT_WIZARD__')
                || ($stateBefore !== 'shipping' && $session->state === 'shipping');

            if ($wizard) {
                $reply = trim(str_replace('__CHECKOUT_WIZARD__', '', $reply));
            }

            // Daftar produk baru → frontend menampilkan kartu pilihan interaktif.
            $productUi = null;
            if (! $wizard && $listAtAfter !== null && $listAtAfter !== $metaBefore) {
                $products = $catalog->findByIds($session->meta['list'] ?? []);
                $productUi = array_values($products->map(fn ($p) => [
                    'id' => $p->id,
                    'name' => $p->product_nama,
                    'price' => (float) $p->product_harga,
                    'image' => ! empty($p->product_gambar) ? fileUrl($p->product_gambar) : '',
                ])->all());
            }
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'reply' => 'Waduh, server AI sedang bermasalah 😥 coba beberapa saat lagi ya.',
            ], 503);
        }

        WebChatMessage::create(['session_token' => $token, 'role' => 'user', 'content' => $userMessage]);
        WebChatMessage::create(['session_token' => $token, 'role' => 'assistant', 'content' => $reply, 'ui' => $productUi ? 'products' : null]);

        return response()->json(array_filter([
            'reply' => $reply,
            'ui' => $wizard
                ? ['type' => 'shipping']
                : ($productUi ? ['type' => 'products', 'products' => $productUi] : null),
        ]));
    }

    /**
     * Isi keranjang sesi untuk ikon cart di header.
     */
    public function cart(Request $request): JsonResponse
    {
        $token = (string) $request->cookie(self::COOKIE, '');
        $session = $this->chatbotSession($token);

        return response()->json($this->cartPayload($session));
    }

    /**
     * @return array{items: array, subtotal: float}
     */
    private function cartPayload(ChatbotSession $session): array
    {
        $catalog = app(CatalogService::class);
        $products = $catalog->findByIds(array_keys($session->cart ?? []));

        $items = [];
        $subtotal = 0.0;

        foreach ($session->cart ?? [] as $id => $qty) {
            if (! $p = $products->get($id)) {
                continue;
            }

            $line = (float) $qty * (float) $p->product_harga;
            $subtotal += $line;
            $items[] = [
                'id' => (int) $id,
                'name' => $p->product_nama,
                'qty' => (int) $qty,
                'price' => (float) $p->product_harga,
                'line_total' => $line,
                'image' => ! empty($p->product_gambar) ? fileUrl($p->product_gambar) : '',
            ];
        }

        return ['items' => $items, 'subtotal' => $subtotal];
    }

    /**
     * Daftar produk aktif untuk picker interaktif.
     */
    public function products(): JsonResponse
    {
        $products = $this->activeProducts();

        return response()->json(['products' => $products->map(fn ($p) => [
            'id' => $p->id,
            'name' => $p->product_nama,
            'price' => (float) $p->product_harga,
            'image' => ! empty($p->product_gambar) ? fileUrl($p->product_gambar) : '',
        ])]);
    }

    /**
     * Tambah beberapa produk sekaligus dari kartu pilihan interaktif.
     */
    public function addItems(Request $request): JsonResponse
    {
        $token = (string) $request->cookie(self::COOKIE, '');
        $items = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['required', 'integer'],
            'items.*.qty' => ['nullable', 'integer', 'min:1', 'max:999'],
        ])['items'];

        $session = $this->chatbotSession($token);
        $catalog = app(CatalogService::class);
        $cart = is_array($session->cart) ? $session->cart : [];

        foreach ($items as $item) {
            $cart[(int) $item['id']] = min(($cart[(int) $item['id']] ?? 0) + max(1, (int) ($item['qty'] ?? 1)), 999);
        }

        $session->forceFill(['cart' => $cart])->save();

        // Ringkasan keranjang setelah ditambah
        $products = $catalog->findByIds(array_keys($cart));
        $subtotal = 0.0;
        $lines = [];
        $rows = [];
        foreach ($cart as $id => $qty) {
            if ($p = $products->get($id)) {
                $line = (float) $qty * (float) $p->product_harga;
                $subtotal += $line;
                $lines[] = sprintf('%s x%d - Rp %s', $p->product_nama, (int) $qty, number_format($line, 0, ',', '.'));
                $rows[] = [
                    'name' => $p->product_nama,
                    'qty' => (int) $qty,
                    'line_total' => $line,
                    'image' => ! empty($p->product_gambar) ? fileUrl($p->product_gambar) : '',
                ];
            }
        }

        return response()->json([
            'summary' => implode("\n", $lines),
            'items' => $rows,
            'subtotal' => $subtotal,
        ]);
    }

    /**
     * Hapus item dari keranjang sesi.
     */
    public function removeItem(Request $request): JsonResponse
    {
        $token = (string) $request->cookie(self::COOKIE, '');
        $session = $this->chatbotSession($token);
        $id = (int) $request->validate(['id' => ['required', 'integer']])['id'];

        $cart = is_array($session->cart) ? $session->cart : [];
        unset($cart[$id]);
        $session->forceFill(['cart' => $cart ?: null])->save();

        return response()->json($this->cartPayload($session));
    }

    /**
     * Mulai checkout dari keranjang (dipicu tombol Checkout, bukan AI).
     */
    public function start(Request $request): JsonResponse
    {
        $token = (string) $request->cookie(self::COOKIE, '');
        $session = $this->chatbotSession($token);

        if (empty($session->cart)) {
            return response()->json(['message' => 'Keranjang masih kosong. Pilih produk dulu ya 😊'], 422);
        }

        $session->forceFill(['state' => 'shipping'])->save();

        $products = app(CatalogService::class)->findByIds(array_keys($session->cart));
        $subtotal = 0.0;
        $lines = [];
        foreach ($session->cart as $id => $qty) {
            if ($p = $products->get($id)) {
                $line = (float) $qty * (float) $p->product_harga;
                $subtotal += $line;
                $lines[] = sprintf('%s x%d — Rp %s', $p->product_nama, (int) $qty, number_format($line, 0, ',', '.'));
            }
        }

        return response()->json([
            'summary' => implode("\n", $lines)."\nSubtotal: Rp ".number_format($subtotal, 0, ',', '.'),
        ]);
    }

    /**
     * Data penerima yang sudah tersimpan di sesi (untuk prefill form).
     */
    public function contact(Request $request): JsonResponse
    {
        $token = (string) $request->cookie(self::COOKIE, '');
        $session = $this->chatbotSession($token);

        return response()->json([
            'name' => (string) ($session->contact_name ?? ''),
            'phone' => (string) ($session->contact_phone ?? ''),
        ]);
    }

    /**
     * Wizard langkah 1: simpan nama & no. HP customer.
     */
    public function shippingDetails(Request $request): JsonResponse
    {
        $token = (string) $request->cookie(self::COOKIE, '');
        $data = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'phone' => ['required', 'string', 'min:8', 'max:20'],
        ]);

        $session = $this->chatbotSession($token);
        $session->forceFill([
            'contact_name' => trim($data['name']),
            'contact_phone' => trim($data['phone']),
        ])->save();

        return response()->json(['status' => true]);
    }

    /**
     * Daftar titik COD aktif untuk radio pilihan.
     */
    public function codLocations(): JsonResponse
    {
        $locations = CodLocation::active()
            ->map(fn ($l) => [
                'id' => $l->id,
                'name' => $l->location_name,
                'address' => $l->address,
                'fee' => $l->fee !== null ? (float) $l->fee : null,
            ]);

        return response()->json(['locations' => $locations]);
    }

    /**
     * Wizard langkah 2: metode pengiriman + hitung ongkir.
     */
    public function setShipping(Request $request): JsonResponse
    {
        $token = (string) $request->cookie(self::COOKIE, '');
        $session = $this->chatbotSession($token);

        if (empty($session->cart)) {
            return response()->json(['message' => 'Keranjang masih kosong.'], 422);
        }

        $method = $request->validate([
            'method' => ['required', 'in:pickup,cod,delivery'],
            'cod_location_id' => ['nullable', 'integer'],
            'lat' => ['nullable', 'numeric'],
            'lng' => ['nullable', 'numeric'],
            'address' => ['nullable', 'string', 'max:500'],
        ])['method'];

        $shippingService = app(CodShippingService::class);

        // Delivery wajib koordinat + alamat
        if ($method === 'delivery') {
            if (! $request->filled('lat') || ! $request->filled('lng')) {
                return response()->json(['message' => 'Lokasi belum dibagikan. Gunakan tombol lokasi.'], 422);
            }

            if (trim((string) $request->input('address')) === '') {
                return response()->json(['message' => 'Alamat lengkap wajib diisi untuk pengiriman.'], 422);
            }

            $quote = $shippingService->deliveryQuote((float) $request->input('lat'), (float) $request->input('lng'));

            if ($quote === null) {
                return response()->json(['message' => 'Maaf, lokasimu di luar radius layanan pengiriman kami.'], 422);
            }

            $shipping = [
                'method' => 'delivery',
                'fee' => $quote['shipping_fee'],
                'distance_km' => $quote['distance_km'],
                'address' => trim((string) $request->input('address')),
                'lat' => (float) $request->input('lat'),
                'lng' => (float) $request->input('lng'),
            ];
        } elseif ($method === 'cod') {
            $location = CodLocation::active()->find((int) $request->input('cod_location_id'));

            if (! $location) {
                return response()->json(['message' => 'Pilih salah satu titik COD dulu ya.'], 422);
            }

            $quote = $shippingService->quoteForLocation($location, null, null);

            $shipping = [
                'method' => 'cod',
                'fee' => $quote['shipping_fee'],
                'distance_km' => $quote['distance_km'],
                'cod_location' => $location->location_name,
                'address' => $location->address,
            ];
        } else {
            $shipping = ['method' => 'pickup', 'fee' => 0];
        }

        // Simpan pilihan + hitung total final
        $products = app(CatalogService::class)->findByIds(array_keys($session->cart ?? []));
        $subtotal = 0.0;
        foreach ($session->cart ?? [] as $id => $qty) {
            if ($p = $products->get($id)) {
                $subtotal += (float) $qty * (float) $p->product_harga;
            }
        }

        $total = $subtotal + $shipping['fee'];
        $meta = is_array($session->meta) ? $session->meta : [];
        $meta['shipping'] = $shipping;
        $session->forceFill(['meta' => $meta, 'state' => 'awaiting_payment'])->save();

        $labels = [
            'pickup' => 'Diambil di Gudang',
            'cod' => 'COD'.(filled($shipping['cod_location'] ?? null) ? ' — '.$shipping['cod_location'] : ''),
            'delivery' => 'Dikirim ke alamat',
        ];
        $lines = ["Metode: {$labels[$method]}"];
        if (! empty($shipping['distance_km'])) {
            $lines[] = 'Jarak: '.rtrim(rtrim(number_format($shipping['distance_km'], 2, '.', ''), '0'), '.').' km';
        }

        return response()->json([
            'status' => true,
            'summary' => implode("\n", $lines)
                ."\nSubtotal: Rp ".number_format($subtotal, 0, ',', '.')
                ."\nOngkir: Rp ".number_format($shipping['fee'], 0, ',', '.')
                ."\nTotal bayar: Rp ".number_format($total, 0, ',', '.'),
        ]);
    }

    /**
     * Wizard akhir: buat pesanan + link pembayaran QRIS.
     */
    public function pay(Request $request): JsonResponse
    {
        $token = (string) $request->cookie(self::COOKIE, '');
        $session = $this->chatbotSession($token);
        $meta = is_array($session->meta) ? $session->meta : [];

        if (($session->state !== 'awaiting_payment') || empty($session->cart) || empty($meta['shipping'])) {
            return response()->json(['message' => 'Pesanan belum siap dibayar.'], 422);
        }

        try {
            $result = app(ChatbotOrderService::class)->createOrder($session, $session->cart, $meta['shipping']);
        } catch (\Throwable $e) {
            report($e);

            return response()->json(['message' => 'Gagal membuat pesanan, coba lagi ya.'], 500);
        }

        $so = $result['so']->load(['has_details.has_product']);
        $session->forceFill(['cart' => null, 'state' => null])->save();

        $lines = $so->has_details
            ->map(fn ($d) => sprintf('%s x%d — Rp %s',
                $d->has_product?->product_nama ?? '-',
                (int) $d->so_detail_qty,
                number_format((float) ($d->so_detail_qty * $d->so_detail_harga), 0, ',', '.')))
            ->implode("\n");

        return response()->json([
            'code' => $so->so_code,
            'summary' => $lines."\nOngkir: Rp ".number_format((float) $so->so_shipping_fee, 0, ',', '.')
                ."\nTotal: Rp ".number_format((float) $so->so_grand_total, 0, ',', '.'),
            'payment_url' => $result['payment_url'],
        ]);
    }

    /**
     * Intent "daftar produk" → balas deterministik dengan picker interaktif.
     */
    private function isProductListingIntent(string $text): bool
    {
        $t = Str::lower(trim($text));
        // Samakan ejaan umum: product/productnya → produk, dsb.
        $t = str_replace(['product', 'prodak'], 'produk', $t);
        $t = preg_replace('/\b(nya|donk|dong|dunk|deh)\b/u', '', $t);

        // Pesan pendek yang menyebut kata kunci produk → anggap minta daftar
        if (mb_strlen(trim($t)) <= 32
            && preg_match('/(produk|sayur|menu|sembako|belanja|dagangan|\blist\b)/', trim($t))) {
            return true;
        }

        if (in_array($t, ['menu', 'produk', 'sayur', 'belanja', 'list', 'daftar sayur', 'daftar produk'], true)) {
            return true;
        }

        return (bool) preg_match(
            '/(apa|apah)\s*(saja|aja)?\s*(yang)?.{0,12}(ready|tersedia|ada|jual)'
            .'|(ready|tersedia).{0,15}\?'
            .'|(daftar|lihat|liat|tampilkan|show)\s+(produk|sayur|menu|barang|sembako)'
            .'|(sayur|produk|sembako).{0,16}(ready|tersedia)/',
            $t,
        );
    }

    /**
     * Intent sapaan → balasan deterministik (konsisten & instan).
     */
    private function isGreetingIntent(string $text): bool
    {
        $t = Str::lower(trim(preg_replace('/[^a-z\s]/', '', Str::lower($text))));

        if ($t === '') {
            return false;
        }

        return (bool) preg_match(
            '/^(halo+|hallow|hai+|hi+|hello+|hei+|hey+|woi+|oy+|p|pagi|siang|sore|malam|assalamualaikum|assalamuallaikum|selamat\s+(pagi|siang|sore|malam))$/',
            $t,
        );
    }

    /**
     * Balasan picker produk + simpan mapping nomor untuk referensi.
     */
    private function respondWithProductPicker(string $token): JsonResponse
    {
        $session = $this->chatbotSession($token);
        $products = $this->activeProducts();

        // Simpan mapping nomor → id agar "nomer X" tetap berfungsi
        $meta = is_array($session->meta) ? $session->meta : [];
        $meta['list'] = $products->pluck('id')->values()->all();
        $meta['list_at'] = now()->format('u');
        $session->forceFill(['meta' => $meta])->save();

        $reply = 'Selamat datang di *'.config('website.name', 'Mayur').'*.
'.'Ada yang bisa saya bantu? Untuk daftar produk klik tombol Lihat Produk berwarna hijau di atas 😊';

        WebChatMessage::create(['session_token' => $token, 'role' => 'user', 'content' => 'sayur apa aja yang ready?']);
        WebChatMessage::create(['session_token' => $token, 'role' => 'assistant', 'content' => $reply]);

        return response()->json([
            'reply' => $reply,
            'ui' => ['type' => 'products', 'products' => $products->map(fn ($p) => [
                'id' => $p->id,
                'name' => $p->product_nama,
                'price' => (float) $p->product_harga,
                'image' => ! empty($p->product_gambar) ? fileUrl($p->product_gambar) : '',
            ])->values()->all()],
        ]);
    }

    private function activeProducts(): Collection
    {
        return Product::query()
            ->with('has_satuan')
            ->where('is_active', true)
            ->where('product_status', 'active')
            ->orderBy('product_nama')
            ->limit(20)
            ->get();
    }

    /**
     * Token anonim per browser — dibuat di client jika belum ada.
     */
    private function sessionToken(Request $request): string
    {
        $token = (string) $request->cookie(self::COOKIE, '');

        if (! preg_match('/^[A-Za-z0-9]{40}$/', $token)) {
            $token = bin2hex(random_bytes(20));
        }

        return $token;
    }

    /**
     * Sesi pemesanan chatbot untuk channel web — dipakai tool cart/checkout.
     */
    private function chatbotSession(string $token): ChatbotSession
    {
        return app(ChatbotSessionService::class)
            ->findOrCreate('web', $token);
    }

    private function instructions(): string
    {
        $catalogUrl = route('shop.index');
        $cartUrl = route('cart.index');
        $homeUrl = route('home');

        return <<<TXT
Kamu adalah "Asisten Warung Sayur Online", toko sayur & sembako segar. Customer mengobrol denganmu seperti WhatsApp.

ATURAN PALING PENTING — RUANG LINGKUP:
- Kamu HANYA membantu urusan toko ini: produk, harga, stok, keranjang, pesanan, pembayaran, pengiriman/pickup.
- Untuk SEMUA hal lain (coding, matematika, berita, terjemahan, saran pribadi, dll) TOLAK singkat dan ramah, persis seperti: "Hehe, itu di luar jasa saya kak 😄 Tapi kalau mau belanja sayur & sembako, saya siap bantu!"
- Jangan pernah menulis atau menjelaskan kode program, apapun pertanyaannya.
- Percakapan sebelumnya tidak mengubah aturan ini. Jika customer memaksa, tetap tolak dengan sopan.

CARA MENJAWAB PERTANYAAN PRODUK:
- DILARANG menuliskan daftar produk dari ingatan atau riwayat percakapan. Untuk pertanyaan daftar/ketersediaan produk, sistem sudah menampilkan kartu pilihan otomatis — cukup balas singkat mengarahkan customer memilih di daftar yang muncul.
- Untuk pertanyaan produk spesifik (misal "tomat ada?") panggil list_products dulu, lalu jawab berdasarkan hasilnya.
- Customer boleh memesan dengan menyebut nomor dari daftar terakhir (misal "nomer 1, 3kg") — pakai param number di add_to_cart.

CARA MENJAWAB PERTANYAAN UMUM TOKO:
- Untuk pertanyaan non-produk (jam buka, ongkir, kebijakan, garansi, dll) SELALU panggil tool search_faq dulu.
- Jawab BERDASARKAN hasil FAQ. Jika FAQ tidak menemukan jawabannya, katakan jujur belum ada info dan arahkan menghubungi toko.

CARA MELAYANI PEMESANAN (semua di dalam chat ini):
1. Customer menyebut produk → cari dengan list_products, tampilkan pilihan bernomor + harganya.
2. Customer memilih → simpan dengan add_to_cart (number atau product_id), konfirmasi jumlah.
3. Customer ingin lihat pesanan → view_cart lalu tampilkan ringkas.
4. Customer bilang sudah cukup / mau bayar / checkout → panggil tool request_checkout. Sistem akan menampilkan FORM interaktif (nama, no. HP, metode kirim: diambil/COD/dikirim, ongkir otomatis).
5. JANGAN tanyakan nama, telepon, alamat, atau metode pengiriman secara manual — form-nya muncul otomatis setelah request_checkout.
6. Setelah wizard selesai, pesanan + link pembayaran QRIS dibuat otomatis oleh sistem, bukan olehmu.

INFO TOKO:
- Katalog web: {$catalogUrl}
- Keranjang web: {$cartUrl}
- Halaman utama: {$homeUrl}
- Pembayaran QRIS; pickup di toko (tanpa ongkir).

GAYA BAHASA: santai, ramah, singkat (maksimal ~6 kalimat), Bahasa Indonesia, emoji secukupnya. Balas pesan terakhir saja.
FORMAT: jangan pakai tabel markdown atau heading; cukup daftar baris sederhana dengan tanda "-". Link ditulis apa adanya.
TXT;
    }
}
