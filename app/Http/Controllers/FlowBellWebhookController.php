<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Modules\So\Enums\SoStatusEnum;
use Modules\So\Models\So;

class FlowBellWebhookController extends Controller
{
    private const CHANNEL = 'webhook';

    /**
     * Map package name → payment method.
     * Digunakan untuk resolve metode pembayaran dari notifikasi Android.
     */
    private const PACKAGE_MAP = [
        'com.gojek.gopaymerchant' => 'qris',
        'com.gojek.gopay' => 'gopay',
        'id.dana' => 'dana',
        'id.dana商家' => 'dana',
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
     * FlowBell notification webhook — POST /api/flowbell/webhook
     *
     * Menerima notifikasi pembayaran dari Android via FlowBell.
     * Parse amount, resolve metode, auto-match ke order pending.
     */
    public function handle(Request $request)
    {
        $payload = $request->all();

        Log::channel(self::CHANNEL)->info('FlowBell webhook received', [
            'payload' => $payload,
            'ip' => $request->ip(),
        ]);

        // 1) Validasi payload
        if (empty($payload['notification']) || empty($payload['app'])) {
            return Response::json([
                'status' => false,
                'message' => 'Invalid payload.',
            ], 400);
        }

        $packageName = $payload['app']['packageName'] ?? '';
        $appName = $payload['app']['name'] ?? '';
        $title = $payload['notification']['title'] ?? '';
        $text = $payload['notification']['text'] ?? '';

        // 2) Verifikasi device (opsional — set FLOWBELL_DEVICE_ID di .env)
        $allowedDevice = config('flowbell.device_id');
        if ($allowedDevice) {
            $deviceId = $payload['device']['id'] ?? '';
            if ($deviceId !== $allowedDevice) {
                Log::channel(self::CHANNEL)->warning('FlowBell device not authorized', [
                    'device' => $deviceId,
                    'ip' => $request->ip(),
                ]);

                return Response::json([
                    'status' => false,
                    'message' => 'Device not authorized.',
                ], 403);
            }
        }

        // 3) Resolve metode pembayaran
        $metode = $this->resolveMethod($packageName, $title);

        // 4) Extract amount dari text
        $amount = $this->extractAmount($text);

        Log::channel(self::CHANNEL)->info('FlowBell parsed', [
            'package' => $packageName,
            'app_name' => $appName,
            'title' => $title,
            'text' => $text,
            'metode' => $metode,
            'amount' => $amount,
        ]);

        if ($amount <= 0) {
            Log::channel(self::CHANNEL)->warning('FlowBell: amount not extracted', [
                'text' => $text,
            ]);

            return Response::json([
                'status' => true,
                'message' => 'Amount not detected.',
            ], 200);
        }

        // 5) Cari order pending dengan nominal unik
        $so = $this->findPendingOrder($amount);

        if (! $so) {
            Log::channel(self::CHANNEL)->warning('FlowBell: no pending order found', [
                'amount' => $amount,
                'metode' => $metode,
            ]);

            return Response::json([
                'status' => true,
                'message' => 'No pending order found.',
            ], 200);
        }

        // 6) Update status order
        $oldStatus = $so->so_status;
        $so->update([
            'so_status' => SoStatusEnum::PAID,
        ]);

        Log::channel(self::CHANNEL)->info('FlowBell: order settled', [
            'so_code' => $so->so_code,
            'amount' => $amount,
            'metode' => $metode,
            'old_status' => $oldStatus,
            'new_status' => SoStatusEnum::PAID,
        ]);

        return Response::json([
            'status' => true,
            'code' => 200,
            'message' => "Pembayaran Rp {$amount} berhasil, pesanan {$so->so_code} lunas.",
            'data' => [
                'so_code' => $so->so_code,
                'amount' => $amount,
                'metode' => $metode,
            ],
        ], 200);
    }

    /**
     * Resolve payment method dari package name + notification title.
     */
    private function resolveMethod(string $packageName, string $title): string
    {
        return match (true) {
            isset(self::PACKAGE_MAP[$packageName]) => self::PACKAGE_MAP[$packageName],
            str_contains($packageName, 'com.gojek.gopaymerchant')
                && str_contains($title, 'Pembayaran QRIS statis diterima') => 'qris',
            str_contains($packageName, 'com.shopeepay.id')
                && str_contains($title, 'Pembayaran')
                && str_contains($title, 'diterima') => 'qris',
            str_contains($packageName, 'com.gojek.gopay')
                && str_contains($title, 'Transfer masuk') => 'gopay',
            str_contains($packageName, 'com.bcadigital')
                && str_contains($title, 'Kamu Menerima Dana Nih!') => 'blu',
            default => 'lainnya',
        };
    }

    /**
     * Extract nominal dari text notifikasi.
     * Support: "Rp 2.000.000", "Rp2000000", "Rp 2", "2000000 IDR", dll.
     */
    private function extractAmount(string $text): int
    {
        // Pattern 1: "Rp" + angka dengan separator titik/koma
        if (preg_match('/Rp[\s.]?([\d.]+)/i', $text, $m)) {
            return (int) str_replace('.', '', $m[1]);
        }

        // Pattern 2: angka + "rupiah" atau "IDR"
        if (preg_match('/([\d.]+)\s*(?:rupiah|IDR)/i', $text, $m)) {
            return (int) str_replace('.', '', $m[1]);
        }

        return 0;
    }

    /**
     * Cari order pending dengan nominal unik + belum expired.
     */
    private function findPendingOrder(int $amount): ?So
    {
        $expiryMinutes = (int) env('QRIS_EXPIRY_MINUTES', 5);

        return So::query()
            ->where('so_unique_amount', $amount)
            ->where('so_status', SoStatusEnum::PENDING)
            ->where('created_at', '>=', now()->subMinutes($expiryMinutes))
            ->first();
    }
}
