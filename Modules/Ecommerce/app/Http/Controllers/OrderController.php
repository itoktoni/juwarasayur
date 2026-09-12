<?php

namespace Modules\Ecommerce\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Modules\So\Enums\ShippingMethodEnum;
use Modules\So\Enums\SoStatusEnum;
use Modules\So\Models\So;

class OrderController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();
        // Affiliator/reseller: pesanan yang mereka buat tercatat sebagai so_id_reseller.
        // Customer/user biasa: pesanan di mana mereka jadi so_id_customer.
        $ownerColumn = ($user->isReseller() || $user->isAffiliator()) ? 'so_id_reseller' : 'so_id_customer';

        $data = So::query()
            ->with(['has_details.has_product'])
            ->where($ownerColumn, Auth::id())
            ->orderByDesc('id')
            ->paginate(15);

        return view('ecommerce::pages.orders.index', [
            'data' => $data,
            'isReseller' => $user->isReseller(),
        ]);
    }

    public function show(int $id): View
    {
        $user = Auth::user();
        $ownerColumn = ($user->isReseller() || $user->isAffiliator()) ? 'so_id_reseller' : 'so_id_customer';

        $so = So::query()
            ->with(['has_details.has_product.has_satuan', 'has_reseller'])
            ->where($ownerColumn, $user->id)
            ->findOrFail($id);

        return view('ecommerce::pages.orders.show', [
            'model' => $so,
            'statusLabel' => SoStatusEnum::getDescription($so->so_status),
            'methodLabel' => ShippingMethodEnum::getDescription($so->so_shipping_method),
        ]);
    }

    /**
     * Regenerate pembayaran untuk order pending: buat token + nominal unik baru,
     * update DB, dan redirect ke halaman pembayaran baru (timer reset).
     */
    public function regenerate(int $id): RedirectResponse
    {
        $user = Auth::user();
        $ownerColumn = ($user->isReseller() || $user->isAffiliator()) ? 'so_id_reseller' : 'so_id_customer';

        $so = So::query()
            ->where($ownerColumn, $user->id)
            ->where('so_status', SoStatusEnum::PENDING)
            ->findOrFail($id);

        $newToken = (string) Str::uuid();
        $newUnique = (int) ((int) $so->so_grand_total + random_int(0, So::uniqueCodeMax()));

        // Update DB: token + unique_amount baru + reset created_at agar timer 5 menit mulai lagi
        // pakai forceFill + query builder agar created_at tidak terblokir Fillable
        \Illuminate\Support\Facades\DB::table('so_orders')->where('id', $so->id)->update([
            'so_payment_token' => $newToken,
            'so_unique_amount' => $newUnique,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $so->refresh();

        flash()->success('Link pembayaran diperbarui. Silakan lanjutkan pembayaran.');

        return redirect()->route('payment.show', ['token' => $newToken]);
    }
}
