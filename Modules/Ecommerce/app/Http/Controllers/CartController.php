<?php

namespace Modules\Ecommerce\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Modules\Catalog\Models\Product;
use Modules\Ecommerce\Models\CartItem;

class CartController extends Controller
{
    public function index(): View
    {
        $items = $this->items();

        return view('ecommerce::pages.cart.index', [
            'items' => $items,
            'subtotal' => $this->subtotal($items),
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

        $qty = (int) ($validated['qty'] ?? 1);

        $item = CartItem::firstOrNew([
            'user_id' => Auth::id(),
            'product_id' => $product->id,
        ]);
        $item->qty = min($item->qty + $qty, 999);
        $item->save();

        if ($request->expectsJson()) {
            return response()->json([
                'status' => true,
                'message' => "{$product->product_nama} ditambahkan ke keranjang.",
                'cart_count' => $this->totalCount(),
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

        foreach ($validated['qty'] as $itemId => $qty) {
            $item = CartItem::where('user_id', Auth::id())->find((int) $itemId);
            if (! $item) {
                continue;
            }

            if ($qty <= 0) {
                $item->delete();
            } else {
                $item->update(['qty' => (int) $qty]);
            }
        }

        flash()->success('Keranjang diperbarui.');

        return redirect()->route('cart.index');
    }

    public function remove(Request $request): RedirectResponse
    {
        $validated = $request->validate(['cart_item_id' => ['required', 'integer']]);

        CartItem::where('user_id', Auth::id())->whereKey($validated['cart_item_id'])->delete();

        flash()->success('Produk dihapus dari keranjang.');

        return redirect()->route('cart.index');
    }

    /**
     * AJAX badge jumlah item cart.
     */
    public function count(): JsonResponse
    {
        return response()->json(['count' => $this->totalCount()]);
    }

    private function items()
    {
        return CartItem::with('has_product.has_satuan')
            ->where('user_id', Auth::id())
            ->join('catalog_products', 'catalog_products.id', '=', 'so_cart_items.product_id')
            ->orderBy('catalog_products.product_nama')
            ->select('so_cart_items.*')
            ->get();
    }

    private function subtotal($items): float
    {
        return (float) $items->sum(fn ($i) => (int) $i->qty * (float) ($i->has_product?->product_harga ?? 0));
    }

    private function totalCount(): int
    {
        $userId = Auth::id();
        if (! $userId) {
            return 0;
        }

        return (int) CartItem::where('user_id', $userId)->sum('qty');
    }
}
