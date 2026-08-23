<?php

namespace Modules\So\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Modules\So\Enums\SoStatusEnum;
use Modules\So\Models\So;

class PaymentWebhookController extends Controller
{
    /**
     * Webhook pembayaran — dipanggil dari aplikasi Android / Postman
     * untuk memvalidasi pembayaran secara otomatis dan mengubah
     * status so_orders menjadi "paid" atau "cancelled".
     *
     * Identitas & otorisasi pesanan menggunakan so_payment_token
     * (UUID acak per-SO). Opsional: header X-Webhook-Secret wajib cocok
     * bila config('so.webhook.secret') diisi.
     *
     * Contoh payload (JSON / form):
     * {
     *   "token": "uuid-payment-token",
     *   "status": "paid" | "cancel" | "cancelled"
     * }
     */
    public function handle(Request $request, ?string $token = null)
    {
        // 1) Otorisasi webhook via shared secret (jika dikonfigurasi)
        $secret = (string) config('so.webhook.secret');
        if ($secret !== '' && $request->header('X-Webhook-Secret') !== $secret) {
            return Response::json([
                'status' => false,
                'code' => 401,
                'message' => 'Unauthorized: secret webhook tidak valid.',
            ], 401);
        }

        // 2) Validasi input
        $data = $request->validate([
            'token' => ['required_without:so_code', 'string'],
            'so_code' => ['required_without:token', 'string'],
            'status' => ['required', 'string', 'in:paid,pay,cancel,cancelled'],
        ], [], [
            'token' => 'token pembayaran',
            'so_code' => 'kode SO',
            'status' => 'status',
        ]);

        $token = $token ?: ($data['token'] ?? null);

        // 3) Cari pesanan berdasarkan token pembayaran (utama) atau so_code
        $query = So::query();
        if (! empty($token)) {
            $query->where('so_payment_token', $token);
        } else {
            $query->where('so_code', $data['so_code']);
        }

        $so = $query->first();
        if (! $so) {
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

        return Response::json([
            'status' => true,
            'code' => 200,
            'message' => $target === SoStatusEnum::PAID
                ? 'Pembayaran berhasil divalidasi, pesanan lunas.'
                : 'Pesanan berhasil dibatalkan.',
            'data' => [
                'so_code' => $so->so_code,
                'so_status' => $so->so_status,
            ],
        ], 200);
    }
}
