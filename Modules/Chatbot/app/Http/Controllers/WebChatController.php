<?php

namespace Modules\Chatbot\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;
use Laravel\Ai\Messages\Message;
use Modules\Chatbot\Ai\Tools\AddToCartTool;
use Modules\Chatbot\Ai\Tools\CheckoutTool;
use Modules\Chatbot\Ai\Tools\ListProductsTool;
use Modules\Chatbot\Ai\Tools\ViewCartTool;
use Modules\Chatbot\Models\ChatbotSession;
use Modules\Chatbot\Models\WebChatMessage;
use Modules\Chatbot\Services\CatalogService;
use Modules\Chatbot\Services\ChatbotOrderService;
use Modules\Chatbot\Services\ChatbotSessionService;

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
            $orders = app(ChatbotOrderService::class);

            $response = agent($this->instructions(), messages: $history, tools: [
                new ListProductsTool($catalog),
                new AddToCartTool($session, $catalog),
                new ViewCartTool($session, $catalog),
                new CheckoutTool($session, $catalog, $orders),
            ])->prompt($userMessage, model: $model);

            $reply = trim((string) $response->text);

            if ($reply === '') {
                $reply = 'Maaf, saya belum bisa menjawab itu. Coba tanya dengan cara lain ya 😊';
            }
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'reply' => 'Waduh, server AI sedang bermasalah 😥 coba beberapa saat lagi ya.',
            ], 503);
        }

        WebChatMessage::create(['session_token' => $token, 'role' => 'user', 'content' => $userMessage]);
        WebChatMessage::create(['session_token' => $token, 'role' => 'assistant', 'content' => $reply]);

        return response()->json(['reply' => $reply]);
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
- SELALU panggil tool list_products untuk data produk/harga/stok. DILARANG keras mengarang nama produk, harga, stok, atau ongkir.
- Tampilkan daftar produk apa adanya dari hasil tool (nama + harga + stok).

CARA MELAYANI PEMESANAN (semua di dalam chat ini):
1. Customer menyebut produk → cari dengan list_products, tampilkan pilihan + harganya.
2. Customer memilih → simpan dengan add_to_cart (id dari list_products), konfirmasi jumlah.
3. Customer ingin lihat pesanan → view_cart lalu tampilkan ringkas.
4. Customer bilang sudah cukup / mau bayar → konfirmasi ringkasan dulu, setelah setuju → checkout.
5. Setelah checkout sukses: tampilkan kode pesanan, rincian, total, dan LINK PEMBAYARAN QRIS apa adanya (biar bisa diklik). Sampaikan status otomatis PAID setelah QRIS discan.

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
