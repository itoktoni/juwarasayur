<?php

namespace Modules\Ecommerce\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
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
}
