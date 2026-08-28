<?php

namespace Modules\So\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Modules\So\Enums\SoStatusEnum;
use Modules\So\Models\So;

class PaymentWebhookController extends Controller
{
    /** Durasi valid nominal unik (menit) — dari .env QRIS_EXPIRY_MINUTES */
    private const UNIQUE_AMOUNT_VALIDITY_MINUTES_KEY = 'QRIS_EXPIRY_MINUTES';

    /**
     * Webhook pembayaran — POST /api/payment/webhook
     *
     * Payment gateway mengirim data ke endpoint ini.
     * Semua request di-log untuk audit trail.
     *
     * Contoh payload (JSON):
     * {
     *   "amount": 50023,
     *   "status": "paid" | "cancel" | "cancelled",
     *   "reference": "optional-reference-id",
     *   ...
     * }
     */
    public function handle(Request $request)
    {
        // Log semua incoming webhook untuk audit
        Log::channel('webhook')->info('Webhook payment received', [
            'method' => $request->method(),
            'ip' => $request->ip(),
            'payload' => $request->all(),
            'headers' => $request->headers->only([
                'content-type',
                'x-webhook-secret',
                'x-signature',
            ]),
        ]);

        // 1) Otorisasi webhook via shared secret (jika dikonfigurasi)
        $secret = (string) config('so.webhook.secret');
        if ($secret !== '' && $request->header('X-Webhook-Secret') !== $secret) {
            Log::channel('webhook')->warning('Webhook payment unauthorized', [
                'ip' => $request->ip(),
            ]);

            return Response::json([
                'status' => false,
                'code' => 401,
                'message' => 'Unauthorized: secret webhook tidak valid.',
            ], 401);
        }

        // 2) Validasi input
        $data = $request->validate([
            'amount' => ['required_without:so_code', 'numeric', 'min:1'],
            'so_code' => ['required_without:amount', 'string'],
            'status' => ['required', 'string', 'in:paid,pay,cancel,cancelled'],
        ], [], [
            'amount' => 'nominal pembayaran',
            'so_code' => 'kode SO',
            'status' => 'status',
        ]);

        // 3) Cari pesanan
        $so = $this->findOrder($data);

        if (! $so) {
            Log::channel('webhook')->warning('Webhook payment order not found', [
                'amount' => $data['amount'] ?? null,
                'so_code' => $data['so_code'] ?? null,
            ]);

            return Response::json([
                'status' => false,
                'code' => 404,
                'message' => 'Pesanan tidak ditemukan.',
            ], 404);
        }

        // 4) Normalisasi status target
        $target = in_array($data['status'], ['cancel', 'cancelled'], true)
            ? SoStatusEnum::CANCELLED
            : SoStatusEnum::PAID;

        // 5) Validasi transisi status
        $current = $so->so_status;

        if ($target === SoStatusEnum::PAID && $current !== SoStatusEnum::PENDING) {
            return Response::json([
                'status' => false,
                'code' => 409,
                'message' => "Tidak bisa membayar pesanan dengan status '{$current}'.",
                'data' => ['so_code' => $so->so_code, 'so_status' => $current],
            ], 409);
        }

        if ($target === SoStatusEnum::CANCELLED && ! in_array($current, [SoStatusEnum::PENDING, SoStatusEnum::PAID], true)) {
            return Response::json([
                'status' => false,
                'code' => 409,
                'message' => "Tidak bisa membatalkan pesanan dengan status '{$current}'.",
                'data' => ['so_code' => $so->so_code, 'so_status' => $current],
            ], 409);
        }

        // 6) Update status
        $so->update(['so_status' => $target]);

        Log::channel('webhook')->info('Webhook payment processed', [
            'so_code' => $so->so_code,
            'old_status' => $current,
            'new_status' => $target,
            'amount' => $data['amount'] ?? null,
        ]);

        return Response::json([
            'status' => true,
            'code' => 200,
            'message' => $target === SoStatusEnum::PAID
                ? 'Pembayaran berhasil divalidasi, pesanan lunas.'
                : 'Pesanan berhasil dibatalkan.',
            'data' => [
                'so_code' => $so->so_code,
                'so_status' => $so->so_status,
                'so_unique_amount' => (float) $so->so_unique_amount,
            ],
        ], 200);
    }

    /**
     * Cari pesanan berdasarkan nominal unik atau so_code.
     */
    private function findOrder(array $data): ?So
    {
        // Cari berdasarkan nominal unik + time window
        if (! empty($data['amount'])) {
            return So::query()
                ->where('so_unique_amount', $data['amount'])
                ->where('so_status', SoStatusEnum::PENDING)
                ->where('created_at', '>=', now()->subMinutes((int) env(self::UNIQUE_AMOUNT_VALIDITY_MINUTES_KEY, 5)))
                ->first();
        }

        // Cari berdasarkan so_code
        return So::query()
            ->where('so_code', $data['so_code'])
            ->first();
    }
}
