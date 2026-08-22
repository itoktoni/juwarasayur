<?php

namespace Modules\Ecommerce\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Catalog\Models\Product;
use Modules\Ecommerce\Services\CartService;

class CartController extends Controller
{
    public function __construct(private CartService $cart) {}

    public function index(): View
    {
        $items = $this->cart->items();

        return view('ecommerce::pages.cart.index', [
            'items' => $items,
            'subtotal' => $this->cart->subtotal(),
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
