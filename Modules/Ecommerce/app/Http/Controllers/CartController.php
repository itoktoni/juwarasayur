<?php

namespace Modules\Ecommerce\Http\Controllers;

use App\Enums\UserTypeEnum;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;
use Modules\Catalog\Models\Product;
use Modules\Ecommerce\Services\CartService;

class CartController extends Controller
{
    public function __construct(private CartService $cart) {}

    public function index(Request $request): View
    {
        $items = $this->cart->items();

        $user = Auth::user();
        $isReseller = $user && $user->isReseller();
        // Hanya user bertipe customer yang jadi opsi (bukan diri reseller sendiri)
        $customers = $isReseller
            ? $user->hasCustomers()->where('type', UserTypeEnum::CUSTOMER)->orderBy('name')->get(['id', 'name', 'phone'])
            : collect();

        // Preselect customer dari halaman "Customer Saya" (?customer_id=)
        if ($isReseller && $request->filled('customer_id')) {
            $owned = $user->hasCustomers()->where('type', UserTypeEnum::CUSTOMER)->find((int) $request->input('customer_id'));
            if ($owned) {
                Session::put('reseller_customer_id', $owned->id);
            } else {
                Session::forget('reseller_customer_id');
            }
        }

        $selectedCustomerId = $isReseller ? (int) Session::get('reseller_customer_id', 0) : 0;

        return view('ecommerce::pages.cart.index', [
            'items' => $items,
            'subtotal' => $this->cart->subtotal(),
            'isReseller' => $isReseller,
            'customers' => $customers,
            'selectedCustomerId' => $selectedCustomerId,
        ]);
    }

    /**
     * Reseller memilih customer tujuan pesanan (disimpan di session).
     * AJAX: balas JSON tanpa redirect; web: redirect biasa.
     */
    public function setCustomer(Request $request): RedirectResponse|JsonResponse
    {
        $request->validate(['customer_id' => ['nullable', 'integer', 'exists:users,id']]);

        $user = Auth::user();
        $selectedId = 0;

        if ($user && $user->isReseller()) {
            if ($request->customer_id) {
                // find → kalau id tidak valid, anggap tidak memilih apa-apa
                $customer = User::where('type', UserTypeEnum::CUSTOMER)
                    ->where('reference_id', $user->id)
                    ->find($request->customer_id);

                if (! $customer) {
                    Session::forget('reseller_customer_id');

                    return $this->customerResponse($request, 0);
                }

                Session::put('reseller_customer_id', $customer->id);
                $selectedId = $customer->id;
            } else {
                // Kosong = reseller belanja untuk dirinya sendiri
                Session::forget('reseller_customer_id');
            }
        }

        if ($request->expectsJson()) {
            return $this->customerResponse($request, $selectedId);
        }

        return redirect()->route('cart.index');
    }

    private function customerResponse(Request $request, int $selectedId): JsonResponse
    {
        return response()->json([
            'status' => true,
            'customer_id' => $selectedId,
            'message' => $selectedId ? 'Pesanan akan dibuat atas nama customer terpilih.' : 'Pesanan atas nama Anda sendiri.',
        ]);
    }

    public function add(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:catalog_products,id'],
            'qty' => ['nullable', 'integer', 'min:1', 'max:999'],
        ]);

        $product = Product::where('is_active', true)
            ->where('product_status', 'active')
            ->findOrFail($validated['product_id']);

        $this->cart->add($product, (int) ($validated['qty'] ?? 1));

        if ($request->expectsJson()) {
            return response()->json([
                'status' => true,
                'message' => "{$product->product_nama} ditambahkan ke keranjang.",
                'cart_count' => $this->cart->count(),
            ]);
        }

        flash()->success("{$product->product_nama} ditambahkan ke keranjang.");

        return redirect()->route('cart.index');
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'qty' => ['required', 'array'],
            'qty.*' => ['required', 'integer', 'min:0', 'max:999'],
        ]);

        $this->cart->updateQty($validated['qty']);

        flash()->success('Keranjang diperbarui.');

        return redirect()->route('cart.index');
    }

    public function remove(Request $request): RedirectResponse
    {
        $validated = $request->validate(['cart_item_id' => ['required', 'integer']]);

        $this->cart->remove((int) $validated['cart_item_id']);

        flash()->success('Produk dihapus dari keranjang.');

        return redirect()->route('cart.index');
    }

    /**
     * AJAX badge jumlah item cart.
     */
    public function count(): JsonResponse
    {
        return response()->json(['count' => $this->cart->count()]);
    }
}
