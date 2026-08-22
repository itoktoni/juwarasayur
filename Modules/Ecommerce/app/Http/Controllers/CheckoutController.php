<?php

namespace Modules\Ecommerce\Http\Controllers;

use App\Enums\UserTypeEnum;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Modules\Ecommerce\Models\CartItem;
use Modules\So\Enums\ShippingMethodEnum;
use Modules\So\Enums\SoStatusEnum;
use Modules\So\Models\So;
use Modules\So\Models\SoDetail;

class CheckoutController extends Controller
{
    public function show(): View|RedirectResponse
    {
        $items = CartItem::with('has_product')->where('user_id', Auth::id())->get();

        if ($items->isEmpty()) {
            flash()->warning('Keranjang masih kosong.');

            return redirect()->route('cart.index');
        }

        return view('ecommerce::pages.checkout.form', [
            'items' => $items,
            'subtotal' => (float) $items->sum(fn ($i) => (int) $i->qty * (float) ($i->has_product?->product_harga ?? 0)),
            'customer' => Auth::user(),
        ]);
    }

    /**
     * Checkout sederhana: cukup nama & no. HP.
     * Pesanan diambil di gudang (pickup), ongkir 0.
     */
    public function placeOrder(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:20'],
        ]);

        $customer = Auth::user();

        abort_if($customer->type !== UserTypeEnum::CUSTOMER, 403, 'Hanya customer yang dapat melakukan pemesanan.');

        /** @var Collection<int, CartItem> $cartItems */
        $cartItems = CartItem::with('has_product')->where('user_id', $customer->id)->get();
        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index');
        }

        // Validasi stok & produk aktif
        foreach ($cartItems as $item) {
            $product = $item->has_product;
            if (! $product || ! $product->is_active || $product->product_status !== 'active') {
                return back()->withErrors(['cart' => "Produk '{$product?->product_nama}' sudah tidak tersedia."])->withInput();
            }
            if (! empty($product->product_stok) && (int) $item->qty > (int) $product->product_stok) {
                return back()->withErrors(['cart' => "Stok '{$product->product_nama}' tidak cukup (sisa {$product->product_stok})."])->withInput();
            }
        }

        $so = DB::transaction(function () use ($validated, $customer, $cartItems) {
            // Simpan kontak ke akun user jika masih kosong
            $customer->fill([
                'name' => $validated['customer_name'],
                'phone' => $validated['customer_phone'],
            ])->save();

            $subtotal = (float) $cartItems->sum(fn ($i) => (int) $i->qty * (float) ($i->has_product?->product_harga ?? 0));

            /** @var So $so */
            $so = So::create([
                'so_tanggal' => now(),
                // Customer milik reseller terkait; fallback ke dirinya sendiri
                'so_id_reseller' => $customer->reference_id ?: $customer->id,
                'so_id_customer' => $customer->id,
                'so_customer_name' => $validated['customer_name'],
                'so_customer_phone' => $validated['customer_phone'],
                'so_status' => SoStatusEnum::PENDING,
                'so_shipping_method' => ShippingMethodEnum::PICKUP,
                'so_shipping_fee' => 0,
                'so_subtotal' => $subtotal,
                'so_discount' => 0,
                'so_discount_type' => 'nominal',
                'so_ppn_rate' => 0,
                'so_pph_rate' => 0,
                'so_grand_total' => $subtotal,
            ]);

            $seq = 1;
            foreach ($cartItems as $item) {
                SoDetail::create([
                    'so_detail_code' => sprintf('%s-%03d', $so->so_code, $seq),
                    'so_detail_id_so' => $so->id,
                    'so_detail_id_product' => $item->product_id,
                    'so_detail_qty' => (int) $item->qty,
                    'so_detail_harga' => (float) ($item->has_product?->product_harga ?? 0),
                ]);
                $seq++;
            }

            CartItem::where('user_id', $customer->id)->delete();

            return $so;
        });

        flash()->success("Pesanan berhasil dibuat dengan kode {$so->so_code}.");

        return redirect()->route('ecommerce.orders.show', ['id' => $so->id]);
    }
}
