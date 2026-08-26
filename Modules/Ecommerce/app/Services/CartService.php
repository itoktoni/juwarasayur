<?php

namespace Modules\Ecommerce\Services;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Modules\Catalog\Models\Product;
use Modules\Ecommerce\Models\CartItem;

/**
 * Satu pintu cart: guest = session browser, login = DB (so_cart_items).
 * Item selalu berbentuk objek dengan ->id, ->qty, ->has_product agar
 * view sama untuk kedua mode.
 */
class CartService
{
    public const SESSION_KEY = 'cart';

    public const MAX_QTY = 999;

    public function isGuestMode(): bool
    {
        return ! Auth::check();
    }

    /**
     * @return Collection<int, object> item dengan properti id, qty, has_product
     */
    public function items(): Collection
    {
        if ($this->isGuestMode()) {
            $cart = $this->sessionCart();

            if (empty($cart)) {
                return collect();
            }

            $products = Product::query()
                ->with('has_satuan')
                ->whereIn('id', array_keys($cart))
                ->get()
                ->keyBy('id');

            // Buang product_id basi dari session
            $stale = array_diff_key($cart, $products->all());
            if (! empty($stale)) {
                Session::put(self::SESSION_KEY, array_diff_key($cart, $stale));
            }

            return collect($cart)
                ->filter(fn ($qty, $productId) => isset($products[$productId]))
                ->map(fn ($qty, $productId) => $this->makeItem((int) $productId, (int) $qty, $products[$productId]))
                ->values();
        }

        return CartItem::with('has_product.has_satuan')
            ->where('so_cart_items.user_id', Auth::id())
            ->join('catalog_products', 'catalog_products.id', '=', 'so_cart_items.product_id')
            ->orderBy('catalog_products.product_nama')
            ->select('so_cart_items.*')
            ->get();
    }

    public function add(Product $product, int $qty = 1): void
    {
        $qty = max(1, min($qty, self::MAX_QTY));

        if ($this->isGuestMode()) {
            $cart = $this->sessionCart();
            $current = (int) ($cart[$product->id] ?? 0);
            $cart[$product->id] = min($current + $qty, self::MAX_QTY);
            Session::put(self::SESSION_KEY, $cart);

            return;
        }

        $item = CartItem::firstOrNew([
            'user_id' => Auth::id(),
            'product_id' => $product->id,
        ]);
        $item->qty = min($item->qty + $qty, self::MAX_QTY);
        $item->save();
    }

    /**
     * @param  array<int|string, int|string>  $qtyMap  [itemId => qty] — guest: itemId = product_id
     */
    public function updateQty(array $qtyMap): void
    {
        if ($this->isGuestMode()) {
            $cart = $this->sessionCart();

            foreach ($qtyMap as $productId => $qty) {
                $productId = (int) $productId;
                $qty = (int) $qty;

                if (! isset($cart[$productId])) {
                    continue;
                }

                if ($qty <= 0) {
                    unset($cart[$productId]);
                } else {
                    $cart[$productId] = min($qty, self::MAX_QTY);
                }
            }

            Session::put(self::SESSION_KEY, $cart);

            return;
        }

        foreach ($qtyMap as $itemId => $qty) {
            $item = CartItem::where('user_id', Auth::id())->find((int) $itemId);
            if (! $item) {
                continue;
            }

            if ((int) $qty <= 0) {
                $item->delete();
            } else {
                $item->update(['qty' => min((int) $qty, self::MAX_QTY)]);
            }
        }
    }

    public function remove(int $id): void
    {
        if ($this->isGuestMode()) {
            $cart = $this->sessionCart();
            unset($cart[$id]);
            Session::put(self::SESSION_KEY, $cart);

            return;
        }

        CartItem::where('user_id', Auth::id())->whereKey($id)->delete();
    }

    public function count(): int
    {
        if ($this->isGuestMode()) {
            return (int) array_sum($this->sessionCart());
        }

        $userId = Auth::id();
        if (! $userId) {
            return 0;
        }

        return (int) CartItem::where('user_id', $userId)->sum('qty');
    }

    public function subtotal(): float
    {
        return (float) $this->items()->sum(
            fn ($i) => (int) $i->qty * (float) ($i->has_product?->product_harga ?? 0)
        );
    }

    public function clear(): void
    {
        if ($this->isGuestMode()) {
            Session::forget(self::SESSION_KEY);

            return;
        }

        CartItem::where('user_id', Auth::id())->delete();
    }

    /**
     * Dipanggil saat login sukses: pindahkan cart session ke DB user.
     */
    public static function mergeSessionToDb(User $user): void
    {
        $cart = Session::get(self::SESSION_KEY, []);

        foreach ($cart as $productId => $qty) {
            $item = CartItem::firstOrNew([
                'user_id' => $user->id,
                'product_id' => (int) $productId,
            ]);
            $item->qty = min($item->qty + (int) $qty, self::MAX_QTY);
            $item->save();
        }

        Session::forget(self::SESSION_KEY);
    }

    private function sessionCart(): array
    {
        $cart = Session::get(self::SESSION_KEY, []);

        return is_array($cart) ? $cart : [];
    }

    private function makeItem(int $productId, int $qty, Product $product): object
    {
        $item = new \stdClass;
        $item->id = $productId;
        $item->qty = $qty;
        $item->has_product = $product;

        return $item;
    }
}
