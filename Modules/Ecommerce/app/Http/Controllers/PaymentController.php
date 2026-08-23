<?php

namespace Modules\Ecommerce\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Modules\So\Enums\ShippingMethodEnum;
use Modules\So\Enums\SoStatusEnum;
use Modules\So\Models\So;

/**
 * Halaman pembayaran QRIS (mockup) — timer 5 menit + simulasi bayar.
 * Guest mengakses via session guest_orders; user login via kepemilikan SO.
 */
class PaymentController extends Controller
{
    public const EXPIRY_MINUTES = 5;

    public function show(int $id): View|RedirectResponse
    {
        $so = $this->findAuthorized($id);

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
    public function invoice(int $id): View
    {
        $so = $this->findAuthorized($id);

        return view('ecommerce::pages.payment.invoice', [
            'so' => $so,
            'methodLabel' => ShippingMethodEnum::getDescription($so->so_shipping_method),
        ]);
    }

    public function simulate(int $id): RedirectResponse
    {
        $so = $this->findAuthorized($id);

        if ($so->so_status !== SoStatusEnum::PENDING) {
            flash()->error('Pesanan ini sudah diproses.');

            return redirect()->route('payment.show', ['id' => $so->id]);
        }

        if ($this->secondsLeft($so) <= 0) {
            flash()->error('Waktu pembayaran sudah habis. Hubungi admin untuk konfirmasi pembayaran.');

            return redirect()->route('payment.show', ['id' => $so->id]);
        }

        $so->update(['so_status' => SoStatusEnum::PAID]);

        flash()->success("Pembayaran untuk pesanan {$so->so_code} berhasil.");

        return redirect()->route('payment.show', ['id' => $so->id]);
    }

    private function secondsLeft(So $so): int
    {
        return max(0, self::EXPIRY_MINUTES * 60 - (int) $so->created_at?->diffInSeconds(now()));
    }

    /**
     * SO milik user login, atau tercatat di session browser (guest).
     */
    private function findAuthorized(int $id): So
    {
        $so = So::query()->with(['has_details.has_product.has_satuan'])->findOrFail($id);

        $user = Auth::user();

        if ($user) {
            if ((int) $so->so_id_customer === (int) $user->id || $user->isAdmin() || $user->isDeveloper()) {
                return $so;
            }
        }

        if (in_array($so->id, array_map('intval', (array) session('guest_orders', [])), true)) {
            return $so;
        }

        abort(403, 'Anda tidak memiliki akses ke pesanan ini.');
    }
}
