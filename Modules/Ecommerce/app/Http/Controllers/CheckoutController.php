<?php

namespace Modules\Ecommerce\Http\Controllers;

use App\Enums\UserTypeEnum;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Modules\Ecommerce\Models\CodLocation;
use Modules\Ecommerce\Services\CartService;
use Modules\Ecommerce\Services\CodShippingService;
use Modules\So\Enums\ShippingMethodEnum;
use Modules\So\Enums\SoStatusEnum;
use Modules\So\Models\So;
use Modules\So\Models\SoDetail;
use Modules\So\Services\DistanceService;

class CheckoutController extends Controller
{
    public function __construct(
        private CartService $cart,
        private CodShippingService $cod,
        private DistanceService $distance,
    ) {}

    public function show(): View|RedirectResponse
    {
        $items = $this->cart->items();

        if ($items->isEmpty()) {
            flash()->warning('Keranjang masih kosong.');

            return redirect()->route('cart.index');
        }

        return view('ecommerce::pages.checkout.form', [
            'items' => $items,
            'subtotal' => $this->cart->subtotal(),
            'customer' => Auth::user(),
            // Titik awal peta delivery = gudang utama (.env: SO_WAREHOUSE_*)
            'warehouse' => config('so.shipping.warehouse'),
            // Daftar lokasi COD aktif untuk pilihan di checkout
            'codLocations' => CodLocation::active(),
        ]);
    }

    /**
     * AJAX: quote COD — titik terdekat dari lokasi customer + ongkir.
     */
    public function quoteCod(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
        ]);

        return response()->json($this->cod->quote((float) $validated['lat'], (float) $validated['lng']));
    }

    /**
     * AJAX: quote untuk lokasi COD terpilih (+ titik customer jika perlu).
     */
    public function quoteCodLocation(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'location' => ['required', 'string', 'max:255'],
            'lat' => ['nullable', 'numeric', 'between:-90,90'],
            'lng' => ['nullable', 'numeric', 'between:-180,180'],
        ]);

        $location = CodLocation::where('location_name', $validated['location'])
            ->where('is_active', true)
            ->first();

        if (! $location) {
            return response()->json(['status' => false, 'message' => 'Lokasi COD tidak ditemukan.']);
        }

        return response()->json($this->cod->quoteForLocation(
            $location,
            isset($validated['lat']) ? (float) $validated['lat'] : null,
            isset($validated['lng']) ? (float) $validated['lng'] : null,
        ));
    }

    /**
     * AJAX: quote diantar ke rumah — jarak rumah customer ke gudang utama
     * (config .env SO_WAREHOUSE_LAT/LNG) + ongkir per km.
     */
    public function quoteDelivery(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
        ]);

        $km = $this->distance->distanceFromWarehouse((float) $validated['lat'], (float) $validated['lng']);

        $maxRadius = (float) config('so.shipping.max_radius_km');
        if ($maxRadius > 0 && $km > $maxRadius) {
            return response()->json([
                'status' => false,
                'message' => "Lokasi Anda di luar radius layanan pengiriman (maks {$maxRadius} km dari gudang utama).",
            ]);
        }

        return response()->json([
            'status' => true,
            'distance_km' => $km,
            'shipping_fee' => $this->distance->shippingFee($km),
        ]);
    }

    public function placeOrder(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:20'],
            'shipping_method' => ['required', 'in:'.ShippingMethodEnum::PICKUP.','.ShippingMethodEnum::COD.','.ShippingMethodEnum::DELIVERY],
            'so_cod_location' => ['nullable', 'required_if:shipping_method,'.ShippingMethodEnum::COD, 'string', 'max:255'],
            'so_lat' => ['nullable', 'required_if:shipping_method,'.ShippingMethodEnum::DELIVERY, 'numeric', 'between:-90,90'],
            'so_lng' => ['nullable', 'required_if:shipping_method,'.ShippingMethodEnum::DELIVERY, 'numeric', 'between:-180,180'],
            'so_address' => ['nullable', 'required_if:shipping_method,'.ShippingMethodEnum::DELIVERY, 'string', 'max:1000'],
            // Alamat opsional khusus COD (panel terpisah dari delivery)
            'so_address_cod' => ['nullable', 'string', 'max:1000'],
        ]);

        $customer = Auth::user();

        if ($customer && $customer->type !== UserTypeEnum::CUSTOMER) {
            abort(403, 'Hanya customer yang dapat melakukan pemesanan.');
        }

        /** @var Collection $cartItems */
        $cartItems = $this->cart->items();
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
                return back()->withErrors(['cart' => "Stok '{$product->product_nama}' tidak cukup (sisa {$product->product_stok})."]).withInput();
            }
        }

        // Ongkir dihitung ulang server-side — fee dari klien tidak dipercaya
        $method = $validated['shipping_method'];
        $shipping = [
            'method' => $method,
            'fee' => 0.0,
            'location' => null,
            'distance_km' => null,
            'lat' => null,
            'lng' => null,
            'address' => null,
        ];

        if ($method === ShippingMethodEnum::COD) {
            $location = CodLocation::where('location_name', $validated['so_cod_location'])
                ->where('is_active', true)
                ->first();

            if (! $location) {
                return back()->withErrors(['shipping' => 'Lokasi COD tidak valid.'])->withInput();
            }

            // Fee flat lokasi menang; jika kosong, wajib titik customer untuk hitung jarak
            $quote = $this->cod->quoteForLocation(
                $location,
                isset($validated['so_lat']) ? (float) $validated['so_lat'] : null,
                isset($validated['so_lng']) ? (float) $validated['so_lng'] : null,
            );

            if (! $quote['status']) {
                return back()->withErrors(['shipping' => $quote['message'] ?? 'Lokasi COD tidak valid.'])->withInput();
            }

            $shipping['fee'] = (float) $quote['shipping_fee'];
            $shipping['location'] = $quote['location_name'];
            $shipping['distance_km'] = $quote['distance_km'];
            $shipping['lat'] = isset($validated['so_lat']) ? (float) $validated['so_lat'] : null;
            $shipping['lng'] = isset($validated['so_lng']) ? (float) $validated['so_lng'] : null;
            $shipping['address'] = $validated['so_address_cod'] ?? ($validated['so_address'] ?? null);
        } elseif ($method === ShippingMethodEnum::DELIVERY) {
            // Ongkir dihitung dari jarak pin rumah ke gudang utama (.env)
            $km = $this->distance->distanceFromWarehouse((float) $validated['so_lat'], (float) $validated['so_lng']);

            $maxRadius = (float) config('so.shipping.max_radius_km');
            if ($maxRadius > 0 && $km > $maxRadius) {
                return back()->withErrors(['shipping' => "Lokasi Anda di luar radius layanan pengiriman (maks {$maxRadius} km dari gudang utama)."])->withInput();
            }

            $shipping['fee'] = $this->distance->shippingFee($km);
            $shipping['distance_km'] = $km;
            $shipping['lat'] = (float) $validated['so_lat'];
            $shipping['lng'] = (float) $validated['so_lng'];
            $shipping['address'] = $validated['so_address'] ?? null;
        }

        $subtotal = $this->cart->subtotal();
        $isGuest = $customer === null;

        $so = DB::transaction(function () use ($validated, $customer, $cartItems, $subtotal, $shipping) {
            /** @var So $so */
            $so = So::create([
                'so_tanggal' => now(),
                // Guest: tanpa reseller & customer; login: milik reseller terkait
                'so_id_reseller' => $customer ? ($customer->reference_id ?: $customer->id) : null,
                'so_id_customer' => $customer?->id,
                'so_customer_name' => $validated['customer_name'],
                'so_customer_phone' => $validated['customer_phone'],
                'so_status' => SoStatusEnum::PENDING,
                'so_shipping_method' => $shipping['method'],
                'so_cod_location' => $shipping['location'],
                'so_shipping_fee' => $shipping['fee'],
                'so_distance_km' => $shipping['distance_km'],
                'so_address' => $shipping['address'],
                'so_lat' => $shipping['lat'],
                'so_lng' => $shipping['lng'],
                'so_subtotal' => $subtotal,
                'so_discount' => 0,
                'so_discount_type' => 'nominal',
                'so_ppn_rate' => 0,
                'so_pph_rate' => 0,
                'so_grand_total' => $subtotal + $shipping['fee'],
            ]);

            $seq = 1;
            foreach ($cartItems as $item) {
                SoDetail::create([
                    'so_detail_code' => sprintf('%s-%03d', $so->so_code, $seq),
                    'so_detail_id_so' => $so->id,
                    'so_detail_id_product' => $item->has_product->id,
                    'so_detail_qty' => (int) $item->qty,
                    'so_detail_harga' => (float) ($item->has_product?->product_harga ?? 0),
                ]);
                $seq++;
            }

            $this->cart->clear();

            return $so;
        });

        // Guest menyimpan id SO di session agar bisa akses halaman pembayaran
        if ($isGuest) {
            session()->push('guest_orders', $so->id);
        }

        flash()->success("Pesanan {$so->so_code} dibuat. Silakan selesaikan pembayaran.");

        return redirect()->route('payment.show', ['id' => $so->id]);
    }
}
