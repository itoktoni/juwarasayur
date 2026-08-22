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
        $data = So::query()
            ->with(['has_details.has_product'])
            ->where('so_id_customer', Auth::id())
            ->orderByDesc('id')
            ->paginate(15);

        return view('ecommerce::pages.orders.index', ['data' => $data]);
    }

    public function show(int $id): View
    {
        $so = So::query()
            ->with(['has_details.has_product.has_satuan', 'has_reseller'])
            ->where('so_id_customer', Auth::id())
            ->findOrFail($id);

        return view('ecommerce::pages.orders.show', [
            'model' => $so,
            'statusLabel' => SoStatusEnum::getDescription($so->so_status),
            'methodLabel' => ShippingMethodEnum::getDescription($so->so_shipping_method),
        ]);
    }
}
