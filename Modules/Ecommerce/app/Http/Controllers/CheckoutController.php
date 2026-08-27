<?php

namespace Modules\Ecommerce\Http\Controllers;

use App\Enums\UserTypeEnum;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;
use Modules\Ecommerce\Models\CodLocation;
use Modules\Ecommerce\Services\CartService;
use Modules\Ecommerce\Services\CodShippingService;
use Modules\So\Enums\ShippingMethodEnum;
use Modules\So\Enums\SoStatusEnum;
use Modules\So\Models\So;
use Modules\So\Models\SoDetail;
use Modules\So\Models\SoDiscount;
use Modules\So\Services\DistanceService;

class CheckoutController extends Controller
{
    /** Session key kode diskon yang sedang dipakai di checkout */
    private const DISCOUNT_SESSION_KEY = 'checkout_discount';

    public function __construct(
        private CartService $cart,
        private CodShippingService $cod,
        private DistanceService $distance,
    ) {}

    /**
     * AJAX: redeem kode diskon — validasi matrix (aktif + min. transaksi),
     * simpan kode di session checkout.
     */
    public function redeemDiscount(Request $request): JsonResponse
    {
        $request->validate(['code' => ['required', 'string', 'max:50']]);

        /** @var SoDiscount|null $discount */
        $discount = SoDiscount::where('discount_code', strtoupper(trim($request->code)))->first();

        if (! $discount || ! $discount->is_active) {
            return response()->json(['status' => false, 'message' => 'Kode diskon tidak ditemukan atau tidak aktif.'], 422);
        }

        $subtotal = $this->cart->subtotal();
        if (! $discount->layakDigunakan($subtotal)) {
            return response()->json(['status' => false, 'message' => 'Minimal transaksi Rp '.number_format((float) $discount->discount_min_purchase, 0, ',', '.').' untuk memakai kode ini.'], 422);
        }

        Session::put(self::DISCOUNT_SESSION_KEY, ['code' => $discount->discount_code]);

        return response()->json([
            'status' => true,
            'code' => $discount->discount_code,
            'label' => $discount->discount_nama,
            'type' => $discount->discount_type,
            'value' => (float) $discount->discount_value,
            'amount' => $discount->hitungPotongan($subtotal),
        ]);
    }

    public function removeDiscount(): JsonResponse
    {
        Session::forget(self::DISCOUNT_SESSION_KEY);

        return response()->json(['status' => true]);
    }

    public function show(): View|RedirectResponse
    {
        $items = $this->cart->items();

        if ($items->isEmpty()) {
            flash()->warning('Keranjang masih kosong.');

            return redirect()->route('cart.index');
        }

        $user = Auth::user();
        $isReseller = $user && $user->isReseller();
        $isAffiliator = $user && $user->isAffiliator();

        $customers = collect();
        $customer = $user;

        if ($isAffiliator) {
            $customers = $user->hasCustomers()->where('type', UserTypeEnum::CUSTOMER)->orderBy('name')->get(['id', 'name', 'phone']);
            $selectedId = (int) Session::get('reseller_customer_id', 0);
            // Tanpa pilihan customer = belanja untuk diri sendiri → pakai data user login
            $customer = $selectedId
                ? (User::where('type', UserTypeEnum::CUSTOMER)->where('reference_id', $user->id)->find($selectedId) ?? $user)
                : $user;
        }

        // Hitung subtotal dengan diskon reseller
        $subtotal = $items->sum(function ($item) use ($isReseller) {
            $harga = (float) ($item->has_product?->product_harga ?? 0);
            $pct = $isReseller ? (float) ($item->has_product?->reseller_fee_percent ?? 0) : 0;
            $hargaEfektif = $pct > 0 ? $harga * (1 - $pct / 100) : $harga;

            return $item->qty * $hargaEfektif;
        });

        return view('ecommerce::pages.checkout.form', [
            'items' => $items,
            'subtotal' => $subtotal,
            'customer' => $customer,
            'isReseller' => $isReseller,
            'isAffiliator' => $isAffiliator,
            'customers' => $customers,
            // Kode diskon yang sedang diredeem (jika valid)
            'discount' => $this->activeDiscount($subtotal),
            // Titik awal peta delivery = gudang utama (.env: SO_WAREHOUSE_*)
            'warehouse' => config('so.shipping.warehouse'),
            // Daftar lokasi COD aktif untuk pilihan di checkout
            'codLocations' => CodLocation::active(),
        ]);
    }

    /**
     * Kode diskon aktif dari session — null jika tidak ada / tidak layak.
     */
    private function activeDiscount(float $subtotal): ?SoDiscount
    {
        $code = Session::get(self::DISCOUNT_SESSION_KEY)['code'] ?? null;
        if (! $code) {
            return null;
        }

        /** @var SoDiscount|null $discount */
        $discount = SoDiscount::where('discount_code', $code)->first();

        return ($discount && $discount->layakDigunakan($subtotal)) ? $discount : null;
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

        $user = Auth::user();
        $isReseller = $user && $user->isReseller();
        $isAffiliator = $user && $user->isAffiliator();

        if ($user && ! $isReseller && ! $isAffiliator && $user->type !== UserTypeEnum::CUSTOMER) {
            abort(403, 'Hanya customer/reseller/affiliator yang dapat melakukan pemesanan.');
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

        // Penentuan pemesan:
        // - Affiliator → memesan untuk customer pilihannya (session reseller_customer_id)
        // - Reseller/Customer → memesan untuk dirinya sendiri
        if ($isAffiliator) {
            $selectedId = (int) Session::get('reseller_customer_id', 0);
            $buyer = $selectedId
                ? User::where('type', UserTypeEnum::CUSTOMER)->where('reference_id', $user->id)->find($selectedId)
                : null;
            $buyerName = $validated['customer_name'] ?: ($buyer?->name ?? 'Pelanggan');
            $buyerPhone = $validated['customer_phone'] ?: ($buyer?->phone ?? null);
            $soIdReseller = $user->id;
            $soIdCustomer = $buyer?->id;
        } else {
            $buyerName = $validated['customer_name'];
            $buyerPhone = $validated['customer_phone'];
            $soIdReseller = $user ? ($user->reference_id ?: $user->id) : null;
            $soIdCustomer = $user?->id;
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

        // Subtotal wajib sama dengan tampilan checkout: pakai harga efektif
        // per item (termasuk diskon reseller) supaya total bayar tidak berubah
        // saat masuk ke halaman pembayaran/QRIS.
        $subtotal = $cartItems->sum(function ($item) use ($isReseller) {
            $harga = (float) ($item->has_product?->product_harga ?? 0);
            $pct = $isReseller ? (float) ($item->has_product?->reseller_fee_percent ?? 0) : 0;
            $hargaEfektif = $pct > 0 ? $harga * (1 - $pct / 100) : $harga;

            return $item->qty * $hargaEfektif;
        });
        $isGuest = $user === null;

        // Validasi ulang kode diskon di server — session tidak dipercaya mentah
        $discount = $this->activeDiscount($subtotal);
        $discountAmount = $discount?->hitungPotongan($subtotal) ?? 0.0;

        $so = DB::transaction(function () use ($cartItems, $subtotal, $shipping, $buyerName, $buyerPhone, $soIdReseller, $soIdCustomer, $discount, $discountAmount, $isReseller, $isAffiliator, $user) {
            /** @var So $so */
            $so = So::create([
                'so_tanggal' => now(),
                // Guest: tanpa reseller & customer; login: milik reseller terkait
                'so_id_reseller' => $soIdReseller,
                'so_id_customer' => $soIdCustomer,
                'so_customer_name' => $buyerName,
                'so_customer_phone' => $buyerPhone,
                'so_status' => SoStatusEnum::PENDING,
                'so_shipping_method' => $shipping['method'],
                'so_cod_location' => $shipping['location'],
                'so_shipping_fee' => $shipping['fee'],
                'so_distance_km' => $shipping['distance_km'],
                'so_address' => $shipping['address'],
                'so_lat' => $shipping['lat'],
                'so_lng' => $shipping['lng'],
                'so_subtotal' => $subtotal,
                'so_discount' => $discountAmount,
                'so_discount_type' => $discount?->discount_type ?? 'nominal',
                'so_discount_note' => $discount?->discount_code,
                'so_ppn_rate' => 0,
                'so_pph_rate' => 0,
                'so_grand_total' => max(0, $subtotal - $discountAmount + $shipping['fee']),
            ]);

            $seq = 1;
            $feeResolver = app(\App\Services\Commission\FeeResolver::class);
            foreach ($cartItems as $item) {
                $harga = (float) ($item->has_product?->product_harga ?? 0);
                $pct = $isReseller ? (float) ($item->has_product?->reseller_fee_percent ?? 0) : 0;
                $hargaEfektif = $pct > 0 ? $harga * (1 - $pct / 100) : $harga;
                $qty = (int) $item->qty;

                // Hitung fee snapshot per baris: reseller = diskon harga (fee=0),
                // affiliator = komisi % dari harga produk, customer = tanpa fee.
                $fee = $feeResolver->resolve($item->has_product, $user, $qty, $harga);

                SoDetail::create([
                    'so_detail_code' => sprintf('%s-%03d', $so->so_code, $seq),
                    'so_detail_id_so' => $so->id,
                    'so_detail_id_product' => $item->has_product->id,
                    'so_detail_qty' => $qty,
                    'so_detail_harga' => $hargaEfektif,
                    'fee_percent' => $isAffiliator ? $fee->percent : null,
                    'fee_amount' => $isAffiliator ? $fee->amount : 0,
                    'fee_source' => $fee->source,
                    'applied_role' => $fee->role,
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

        // Kode diskon selesai dipakai
        Session::forget(self::DISCOUNT_SESSION_KEY);

        if ($isReseller) {
            Session::forget('reseller_customer_id');
            flash()->success("Pesanan {$so->so_code} dibuat. Bagikan link pembayaran ke customer.");

            return redirect()->route('checkout.share', ['token' => $so->so_payment_token]);
        }

        flash()->success("Pesanan {$so->so_code} dibuat. Silakan selesaikan pembayaran.");

        return redirect()->route('payment.show', ['token' => $so->so_payment_token]);
    }

    /**
     * Halaman berbagi link pembayaran untuk reseller.
     */
    public function share(string $token)
    {
        $so = So::with('has_details.has_product')
            ->where('so_payment_token', $token)
            ->firstOrFail();

        if (Auth::check() && Auth::user()->isReseller() && $so->so_id_reseller !== Auth::id()) {
            abort(403);
        }

        $link = url('/payment/'.$so->so_payment_token);
        $qrisPayload = ! empty(config('ecommerce.qris_payload'))
            ? nominalQRIS(config('ecommerce.qris_payload'), (float) $so->so_grand_total)
            : null;
        $qrDownload = $qrisPayload
            ? $this->buildQrWithInfo($qrisPayload, $so->so_code, (float) $so->so_grand_total, 400)
            : '';
        // Sisa waktu pembayaran, sama dengan aturan di PaymentController
        $secondsLeft = max(0, PaymentController::EXPIRY_MINUTES * 60
            - (int) $so->created_at?->diffInSeconds(now()));

        return view('ecommerce::pages.checkout.share', [
            'so' => $so,
            'link' => $link,
            'qrisPayload' => $qrisPayload,
            'qrDownload' => $qrDownload,
            'secondsLeft' => $secondsLeft,
            'methodLabel' => ShippingMethodEnum::getDescription($so->so_shipping_method),
        ]);
    }

    /**
     * Build a downloadable QR PNG that also embeds the SO number and price.
     */
    private function buildQrWithInfo(string $qrText, string $soCode, float $amount, int $qrSize = 400): string
    {
        $renderer = new \BaconQrCode\Renderer\GDLibRenderer($qrSize, 10);
        $writer = new \BaconQrCode\Writer($renderer);
        $qrPng = $writer->writeString(
            $qrText,
            \BaconQrCode\Encoder\Encoder::DEFAULT_BYTE_MODE_ENCODING,
            \BaconQrCode\Common\ErrorCorrectionLevel::H()
        );

        $qrImg = @imagecreatefromstring($qrPng);
        if ($qrImg === false) {
            return 'data:image/png;base64,'.base64_encode($qrPng);
        }

        $qW = imagesx($qrImg);
        $qH = imagesy($qrImg);
        $pad = 20;
        $gap = 12;
        $textArea = 60;
        $W = $qW + $pad * 2;
        $H = $qH + $pad + $gap + $textArea;

        $canvas = imagecreatetruecolor($W, $H);
        $white = imagecolorallocate($canvas, 255, 255, 255);
        $black = imagecolorallocate($canvas, 0, 0, 0);
        $gray = imagecolorallocate($canvas, 90, 90, 90);
        imagefill($canvas, 0, 0, $white);
        imagecopy($canvas, $qrImg, $pad, $pad, 0, 0, $qW, $qH);

        $soText = 'SO: '.$soCode;
        $priceText = 'Rp '.number_format($amount, 0, ',', '.');
        $font = 5;
        $sx = (int) (($W - imagefontwidth($font) * strlen($soText)) / 2);
        imagestring($canvas, $font, max(0, $sx), $pad + $qH + $gap + 8, $soText, $black);
        $px = (int) (($W - imagefontwidth($font) * strlen($priceText)) / 2);
        imagestring($canvas, $font, max(0, $px), $pad + $qH + $gap + 34, $priceText, $gray);

        ob_start();
        imagepng($canvas);
        $out = ob_get_clean();
        imagedestroy($canvas);
        imagedestroy($qrImg);

        return 'data:image/png;base64,'.base64_encode($out);
    }
}
