<?php

namespace Modules\So\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Modules\So\Enums\SoStatusEnum;
use Modules\So\Models\So;

class PaymentWebhookController extends Controller
{
    private const UNIQUE_AMOUNT_VALIDITY_MINUTES_KEY = 'QRIS_EXPIRY_MINUTES';

    private const PACKAGE_MAP = [
        'com.gojek.gopaymerchant' => 'qris',
        'com.gojek.gopay' => 'gopay',
        'id.dana' => 'dana',
        'com.shopeepay.id' => 'shopeepay',
        'id.ovo' => 'ovo',
        'com.ovo' => 'ovo',
        'id.bsi.mobile' => 'transfer',
        'com.bca' => 'transfer',
        'com.bni' => 'transfer',
        'com.bri' => 'transfer',
        'com.mandiri' => 'transfer',
        'com.bcadigital' => 'blu',
    ];

    /**
     * POST /api/payment/webhook
     *
     * NotifyHook (Android notification forwarder):
     *   Header : X-NotifyHook-Signature: hash_hmac('sha256', raw body, NOTIFYHOOK_SECRET)
     *   Body   : {"ip":"127.0.0.1","payload":{"rule":"gopay","package":"com.gojek.gopaymerchant",
     *            "app":"GoPay Merchant","title":"Pembayaran QRIS statis diterima",
     *            "text":"Rp 39 di Home Pimpah, BERBAH.","timestamp":"...","notificationKey":"...","username":"..."}}
     *
     * Auto-detect: NotifyHook format atau standard format {"amount": 39}.
     * Yang penting: amount / unique value → match order → settle.
     */
    public function handle(Request $request)
    {
        $rawBody = $request->getContent();
        $payload = $request->json()->all() ?: $request->all();

        Log::channel('webhook')->info('Webhook received', [
            'ip' => $request->ip(),
            'payload' => $payload,
            'raw_body' => $request->all(),
            'headers' => $request->headers->all(),
        ]);

        // Verifikasi token NotifyHook
        if (! $this->verifyToken($request)) {
            Log::channel('webhook')->warning('Invalid token', ['ip' => $request->ip()]);

            return Response::json(['status' => false, 'message' => 'Invalid token.'], 401);
        }

        // 2) Data notifikasi — NotifyHook membungkus isi notifikasi di key "payload"
        $notification = is_array($payload['payload'] ?? null) ? $payload['payload'] : $payload;

        // 3) Extract amount — support NotifyHook dan standard format
        $amount = $this->extractAmount($payload, $notification);
        $method = $this->resolveMethod($notification);

        Log::channel('webhook')->info('Extract result', [
            'amount' => $amount,
            'method' => $method,
            'package' => $notification['package'] ?? null,
            'notification_text' => $notification['text'] ?? null,
        ]);

        if ($amount <= 0) {
            Log::channel('webhook')->warning('Amount not detected', ['payload' => $payload]);

            return Response::json(['status' => true, 'message' => 'Amount not detected.'], 200);
        }

        // 4) Cari order pending dengan nominal unik
        $so = $this->findPendingOrder($amount);

        if (! $so) {
            Log::channel('webhook')->warning('No pending order', ['amount' => $amount]);

            return Response::json(['status' => true, 'message' => 'No pending order found.'], 200);
        }

        // 5) Update status
        $oldStatus = $so->so_status;
        $so->update(['so_status' => SoStatusEnum::PAID]);

        Log::channel('webhook')->info('Order settled', [
            'so_code' => $so->so_code,
            'amount' => $amount,
            'method' => $method,
            'old_status' => $oldStatus,
        ]);

        return Response::json([
            'status' => true,
            'code' => 200,
            'message' => "Pembayaran Rp {$amount} berhasil, pesanan {$so->so_code} lunas.",
            'data' => ['so_code' => $so->so_code, 'amount' => $amount, 'method' => $method],
        ], 200);
    }

    /**
     * Verifikasi token dari payload.
     */
    private function verifyToken(Request $request): bool
    {
        $secret = (string) env('NOTIFYHOOK_SECRET', '');

        if ($secret === '') {
            Log::channel('webhook')->warning('NOTIFYHOOK_SECRET not set, skipping verification');

            return true;
        }

        $payload = $request->json()->all() ?: $request->all();
        $token = $payload['payload']['token'] ?? $payload['token'] ?? '';

        return hash_equals($secret, $token);
    }

    /**
     * Extract amount dari payload (NotifyHook atau standard).
     */
    private function extractAmount(array $payload, array $notification): int
    {
        // Standard format: {"amount": 85}
        if (! empty($payload['amount']) && is_numeric($payload['amount'])) {
            return (int) $payload['amount'];
        }

        // NotifyHook format: {"payload": {"text": "Rp 85 ..."}}
        if (! empty($notification['text'])) {
            return $this->parseAmount((string) $notification['text']);
        }

        return 0;
    }

    /**
     * Resolve metode pembayaran dari package app / rule NotifyHook.
     */
    private function resolveMethod(array $notification): ?string
    {
        $package = $notification['package'] ?? null;

        if ($package && isset(self::PACKAGE_MAP[$package])) {
            return self::PACKAGE_MAP[$package];
        }

        return $notification['rule'] ?? null;
    }

    /**
     * Parse nominal dari text notifikasi.
     * "Rp 85 di Home Pimpah" → 85
     * "Rp 2.000.000" → 2000000
     */
    private function parseAmount(string $text): int
    {
        if (preg_match('/Rp[\s.]?([\d.]+)/i', $text, $m)) {
            return (int) str_replace('.', '', $m[1]);
        }

        if (preg_match('/([\d.]+)\s*(?:rupiah|IDR)/i', $text, $m)) {
            return (int) str_replace('.', '', $m[1]);
        }

        return 0;
    }

    /**
     * Cari order pending dengan nominal unik.
     */
    private function findPendingOrder(int $amount): ?So
    {
        $expiryMinutes = (int) env(self::UNIQUE_AMOUNT_VALIDITY_MINUTES_KEY, 5);

        return So::query()
            ->where('so_unique_amount', $amount)
            ->where('so_status', SoStatusEnum::PENDING)
            ->where('created_at', '>=', now()->subMinutes($expiryMinutes))
            ->first();
    }
}
