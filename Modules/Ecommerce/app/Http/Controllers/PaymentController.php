<?php

namespace Modules\Ecommerce\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Modules\So\Enums\ShippingMethodEnum;
use Modules\So\Enums\SoStatusEnum;
use Modules\So\Models\So;

/**
 * Halaman pembayaran QRIS (mockup) — timer 5 menit + simulasi bayar.
 * URL memakai so_payment_token (uuid acak), bukan id berurutan.
 * Guest mengakses via session guest_orders; user login via kepemilikan SO.
 */
class PaymentController extends Controller
{
    public const EXPIRY_MINUTES = 5;

    public function show(string $token): View|RedirectResponse
    {
        $so = $this->findAuthorized($token);

        if ($so->so_status === SoStatusEnum::PENDING && $this->secondsLeft($so) <= 0) {
            flash()->warning('Waktu pembayaran habis. Pesanan tetap tersimpan dengan status Pending.');
        }

        return view('ecommerce::pages.payment.qris', [
            'so' => $so,
            'secondsLeft' => $this->secondsLeft($so),
            'methodLabel' => ShippingMethodEnum::getDescription($so->so_shipping_method),
        ]);
    }

    /**
     * Invoice cetak (print-friendly) — akses sama dengan halaman pembayaran.
     */
    public function invoice(string $token): View
    {
        $so = $this->findAuthorized($token);

        return view('ecommerce::pages.payment.invoice', [
            'so' => $so,
            'methodLabel' => ShippingMethodEnum::getDescription($so->so_shipping_method),
        ]);
    }

    /**
     * Polling status pembayaran (GET berkala dari halaman QRIS).
     */
    public function status(string $token): JsonResponse
    {
        $so = So::query()->where('so_payment_token', $token)->firstOrFail();

        return response()->json([
            'status' => $so->so_status,
            'paid' => $so->so_status === SoStatusEnum::PAID,
        ]);
    }

    private function secondsLeft(So $so): int
    {
        return max(0, self::EXPIRY_MINUTES * 60 - (int) $so->created_at?->diffInSeconds(now()));
    }

    /**
     * Cari pesanan berdasarkan token pembayaran.
     * Token UUID bersifat unguessable — siapa pun yang punya link
     * (customer hasil share, reseller, guest) berhak membuka halaman ini.
     */
    private function findAuthorized(string $token): So
    {
        return So::query()
            ->with(['has_details.has_product.has_satuan'])
            ->where('so_payment_token', $token)
            ->firstOrFail();
    }
}
