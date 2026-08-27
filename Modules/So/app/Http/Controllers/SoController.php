<?php

namespace Modules\So\Http\Controllers;

use App\Enums\UserTypeEnum;
use App\Http\Requests\GeneralRequest;
use App\Models\User;
use App\Services\Commission\FeeResolver;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Catalog\Models\Product;
use Modules\Ecommerce\Models\CodLocation;
use Modules\So\Enums\ShippingMethodEnum;
use Modules\So\Enums\SoStatusEnum;
use Modules\So\Models\So;
use Modules\So\Models\SoDetail;
use Modules\So\Services\DistanceService;

class SoController extends Controller
{
    public function __construct(So $model, protected FeeResolver $fees)
    {
        $this->model = $model::getModel();
    }

    protected function share($data = [])
    {
        $products = Product::where('is_active', true)
            ->orderBy('product_nama')
            ->get(['id', 'product_nama', 'product_harga', 'reseller_fee_percent']);
        $trim = fn ($v) => $v === null || $v === '' ? $v : rtrim(rtrim(number_format((float) $v, 2, '.', ''), '0'), '.');

        $codLocations = CodLocation::active()
            ->map(fn ($loc) => [
                'name' => $loc->location_name,
                'lat' => (float) $loc->lat,
                'lng' => (float) $loc->lng,
                'fee' => (float) ($loc->fee ?? 0),
            ]);

        return array_merge([
            'model' => $this->model,
            'customerOptions' => $this->customerOptions(),
            'resellerOptions' => $this->resellerOptions(),
            'statusOptions' => SoStatusEnum::getOptions(),
            'shippingMethodOptions' => ShippingMethodEnum::getOptions(),
            'discountTypeOptions' => ['nominal' => 'Nominal (Rp)', 'percent' => 'Persen (%)'],
            'codLocationOptions' => $codLocations->pluck('name', 'name')->all(),
            'codLocations' => $codLocations->all(),
            'productOptions' => $products->pluck('product_nama', 'id')->all(),
            'productPrices' => $products->mapWithKeys(fn ($p) => [$p->id => $trim($p->product_harga)])->all(),
            'productResellerFees' => $products->mapWithKeys(fn ($p) => [$p->id => $trim($p->reseller_fee_percent ?? 0)])->all(),
            'resellerTypes' => $this->resellerTypes(),
            'warehouse' => config('so.shipping.warehouse'),
            'shippingConfig' => [
                'price_per_km' => (float) config('so.shipping.price_per_km'),
                'min_fee' => (float) config('so.shipping.min_fee'),
                'max_radius_km' => (float) config('so.shipping.max_radius_km'),
            ],
        ], $data);
    }

    protected function getData()
    {
        return $this->model->with(['has_reseller', 'has_customer', 'has_details.has_product'])->filter()->sort();
    }

    public function getUpdate(GeneralRequest $request, $id)
    {
        $data = $this->model->with(['has_details.has_product'])->findOrFail($id);

        return $this->views($this->template(), [
            'model' => $data,
        ]);
    }

    /**
     * Shortcut dari tabel SO: masuk ke modul Prepare dengan SO ini sebagai target.
     * Hanya untuk SO berstatus paid/confirmed (yang siap di-prepare dari gudang).
     */
    public function getPrepare(GeneralRequest $request, $id)
    {
        $so = $this->model->findOrFail($id);

        // Validasi minimal: SO harus sudah punya detail & status relevan
        if (! in_array($so->so_status, [SoStatusEnum::PAID, SoStatusEnum::CONFIRMED], true)) {
            flash()->error('SO ini belum siap di-prepare (status: '.$so->so_status.').');

            return redirect()->route('so-so.getTable');
        }

        // Forward ke modul Prepare dengan so_ids sebagai query string
        return redirect()->route('prepare.group', ['so_ids' => [$so->id]]);
    }

    /**
     * AJAX: hitung ongkir delivery berdasarkan jarak gudang → titik tujuan.
     */
    public function getShippingCost(GeneralRequest $request)
    {
        $validated = $request->validate([
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
        ]);

        /** @var DistanceService $distance */
        $distance = app(DistanceService::class);
        $km = $distance->distanceFromWarehouse((float) $validated['lat'], (float) $validated['lng']);

        $maxRadius = (float) config('so.shipping.max_radius_km');
        if ($maxRadius > 0 && $km > $maxRadius) {
            return response()->json([
                'status' => false,
                'message' => "Lokasi di luar radius layanan kirim (maks {$maxRadius} km).",
            ], 422);
        }

        return response()->json([
            'status' => true,
            'distance_km' => $km,
            'shipping_fee' => $distance->shippingFee($km),
        ]);
    }

    /**
     * AJAX: ongkir COD per lokasi terdaftar.
     */
    public function getCodFee(GeneralRequest $request)
    {
        $validated = $request->validate(['location' => ['required', 'string']]);

        $location = CodLocation::where('location_name', $validated['location'])
            ->where('is_active', true)
            ->first();

        if (! $location) {
            return response()->json(['status' => false, 'message' => 'Lokasi COD tidak ditemukan.'], 422);
        }

        return response()->json([
            'status' => true,
            'location' => $location->location_name,
            'shipping_fee' => (float) ($location->fee ?? 0),
        ]);
    }

    /**
     * Print continues: banyak struk 80mm sekaligus dengan garis potong di antara struk.
     * ids kosong → pakai data tabel saat ini (filter/halaman aktif).
     */
    public function getPrintContinues(GeneralRequest $request)
    {
        $ids = collect(explode(',', (string) $request->query('ids', '')))
            ->map(fn ($v) => (int) trim($v))
            ->filter()
            ->unique()
            ->values();

        $query = $this->model->with(['has_details.has_product']);

        $list = $ids->isNotEmpty()
            ? $query->whereIn('id', $ids)->orderBy('so_code')->get()
            : $this->getData()->get();

        abort_if($list->isEmpty(), 404, 'Tidak ada SO untuk dicetak.');

        return response()->view('so::pages.so.print-continues', [
            'list' => $list,
        ]);
    }

    public function postCreate(GeneralRequest $request)
    {
        $data = $this->validatedWithShipping($request);

        try {
            $so = DB::transaction(function () use ($data) {
                $so = So::create(collect($data)->except('details')->toArray());
                $this->syncDetails($so, $data['details']);
                $so->recalculateTotals();

                return $so->load('has_details.has_product');
            });

            return $this->response($this->payload(TOAST_SUCCESS, $so));
        } catch (\Throwable $th) {
            return $this->response($this->payload(TOAST_FAILED, $th->getMessage()));
        }
    }

    public function postUpdate(GeneralRequest $request, $id)
    {
        $so = So::findOrFail($id);
        $data = $this->validatedWithShipping($request, $so);

        try {
            $so = DB::transaction(function () use ($data, $so) {
                $so->update(collect($data)->except('details')->toArray());
                $this->syncDetails($so, $data['details']);
                $so->recalculateTotals();

                return $so->load('has_details.has_product');
            });

            return $this->response($this->payload(TOAST_SUCCESS, $so));
        } catch (\Throwable $th) {
            return $this->response($this->payload(TOAST_FAILED, $th->getMessage()));
        }
    }

    /**
     * Validasi + set reseller dari user login & hitung ongkir server-side
     * agar fee tidak bisa dimanipulasi dari client.
     */
    private function validatedWithShipping(GeneralRequest $request, ?So $so = null): array
    {
        $data = $request->validate((new So)->rules());

        $customer = ! empty($data['so_id_customer']) ? User::findOrFail($data['so_id_customer']) : null;

        // Input kosong ("") → pertahankan reseller lama saat update,
        // turunkan dari customer terpilih, atau fallback ke user login
        if (empty($data['so_id_reseller'])) {
            $data['so_id_reseller'] = $so?->so_id_reseller
                ?? ($customer?->reference_id ?: Auth::id());
        }

        // Reseller hanya boleh order untuk customer-nya sendiri.
        // Admin/developer/editor (role internal) bebas membuat SO tanpa batasan
        // ownership customer — karena di area admin semua user adalah milik sistem.
        $user = Auth::user();
        $isInternal = $user && in_array($user->role, ['admin', 'developer', 'editor'], true);

        if ($customer !== null && ! $isInternal) {
            abort_if((int) $customer->reference_id !== (int) $data['so_id_reseller'], 422, 'Customer bukan milik reseller ini.');
        }

        unset($data['so_code'], $data['so_shipping_fee'], $data['so_distance_km']);

        $data['so_shipping_fee'] = $so?->so_shipping_fee ?? 0;
        $data['so_distance_km'] = $so?->so_distance_km;

        // Diskon & pajak opsional — default 0 / pertahankan nilai lama saat update
        $data['so_discount'] ??= 0;
        $data['so_discount_type'] ??= $so?->so_discount_type ?? 'nominal';
        if (! array_key_exists('so_discount_note', $data)) {
            $data['so_discount_note'] = $so?->so_discount_note;
        }
        $data['so_ppn_rate'] ??= $so?->so_ppn_rate ?? 0;
        $data['so_pph_rate'] ??= $so?->so_pph_rate ?? 0;

        $method = $data['so_shipping_method'] ?? ShippingMethodEnum::PICKUP;
        /** @var DistanceService $service */
        $service = app(DistanceService::class);

        if ($method === ShippingMethodEnum::COD) {
            $location = CodLocation::where('location_name', trim((string) ($data['so_cod_location'] ?? '')))
                ->where('is_active', true)
                ->first();

            abort_if(! $location, 422, 'Lokasi COD tidak valid.');
            $data['so_cod_location'] = $location->location_name;
            $data['so_lat'] = $location->lat;
            $data['so_lng'] = $location->lng;
            $data['so_shipping_fee'] = (float) ($location->fee ?? 0);
        } elseif ($method === ShippingMethodEnum::DELIVERY) {
            abort_if(empty($data['so_lat']) || empty($data['so_lng']), 422, 'Titik lokasi pengiriman wajib diisi.');

            $km = $service->distanceFromWarehouse((float) $data['so_lat'], (float) $data['so_lng']);
            $maxRadius = (float) config('so.shipping.max_radius_km');
            abort_if($maxRadius > 0 && $km > $maxRadius, 422, "Lokasi di luar radius layanan kirim (maks {$maxRadius} km).");

            $data['so_distance_km'] = $km;
            $data['so_shipping_fee'] = $service->shippingFee($km);
        } else {
            // pickup: tanpa lokasi & ongkir
            $data['so_cod_location'] = null;
            $data['so_shipping_fee'] = 0;
        }

        // Terapkan FeeResolver per baris berdasarkan USER YANG DIPILIH (so_id_reseller),
        // bukan user login — sehingga admin bisa membuat order utk reseller/affiliator:
        //   - customer/user biasa : harga = product_harga
        //   - reseller            : harga = product_harga - (product_harga * reseller_fee_percent)
        //   - affiliator          : harga = product_harga + snapshot fee_percent/fee_amount
        // Basis harga SELALU product_harga dari DB agar tidak bisa dimanipulasi/double-diskon.
        if (! empty($data['details']) && is_array($data['details'])) {
            $ownerId = (int) ($data['so_id_reseller'] ?? 0);
            $owner = $ownerId ? User::find($ownerId) : Auth::user();

            foreach ($data['details'] as $idx => $row) {
                $product = Product::find((int) ($row['so_detail_id_product'] ?? 0));
                if (! $product) {
                    continue;
                }
                $qty = (int) ($row['so_detail_qty'] ?? 1);
                $res = $this->fees->resolve($product, $owner, $qty, (float) $product->product_harga);
                // Reseller: harga sudah didiskon, affiliator/customer: harga tetap
                $data['details'][$idx]['so_detail_harga'] = $res->hargaEfektif;
                if ($owner?->isAffiliator()) {
                    $data['details'][$idx]['fee_percent'] = $res->percent;
                    $data['details'][$idx]['fee_amount'] = $res->amount;
                    $data['details'][$idx]['fee_source'] = $res->source;
                    $data['details'][$idx]['applied_role'] = $res->role;
                } else {
                    // Reseller/user biasa: tidak ada komisi snapshot, tapi simpan role untuk audit
                    $data['details'][$idx]['fee_percent'] = null;
                    $data['details'][$idx]['fee_amount'] = 0;
                    $data['details'][$idx]['fee_source'] = $res->source;
                    $data['details'][$idx]['applied_role'] = $res->role;
                }
            }
        }

        return $data;
    }

    private function syncDetails(So $so, array $details): void
    {
        $existing = $so->has_details()->get()->keyBy('id');
        $keepIds = [];
        $seq = 1;

        foreach ($details as $row) {
            $attrs = [
                'so_detail_id_so' => $so->id,
                'so_detail_id_product' => (int) $row['so_detail_id_product'],
                'so_detail_qty' => (int) $row['so_detail_qty'],
                'so_detail_harga' => (float) ($row['so_detail_harga'] ?? 0),
                'fee_percent' => isset($row['fee_percent']) ? (float) $row['fee_percent'] : null,
                'fee_amount' => (float) ($row['fee_amount'] ?? 0),
                'fee_source' => $row['fee_source'] ?? null,
                'applied_role' => $row['applied_role'] ?? null,
                'so_detail_keterangan' => $row['so_detail_keterangan'] ?? null,
            ];

            $id = $row['so_detail_id'] ?? null;
            $prev = $id ? $existing->get((int) $id) : null;

            if ($prev) {
                $prev->update($attrs);
                $keepIds[] = (int) $prev->id;
            } else {
                $attrs['so_detail_code'] = $this->nextDetailCode($so->so_code, $seq);
                $keepIds[] = (int) SoDetail::create($attrs)->id;
            }

            $seq++;
        }

        foreach ($existing as $detail) {
            if (in_array((int) $detail->id, $keepIds, true)) {
                continue;
            }
            $detail->delete();
        }
    }

    private function nextDetailCode(string $soCode, int $seq): string
    {
        $code = sprintf('%s-%03d', $soCode, $seq);
        while (SoDetail::where('so_detail_code', $code)->exists()) {
            $seq++;
            $code = sprintf('%s-%03d', $soCode, $seq);
        }

        return $code;
    }

    private function customerOptions(): array
    {
        $user = Auth::user();

        if ($user && in_array($user->type, [UserTypeEnum::RESELLER, UserTypeEnum::AFFILIATOR], true)) {
            return $user->hasCustomers()->orderBy('name')->pluck('name', 'id')->all();
        }

        // Admin/developer: semua customer
        return User::where('type', UserTypeEnum::CUSTOMER)->orderBy('name')->pluck('name', 'id')->all();
    }

    /**
     * Pilihan pemilik order untuk admin: reseller maupun affiliator.
     * Harga & perlakuan mengikuti tipe user yang dipilih.
     */
    private function resellerOptions(): array
    {
        return $this->pickableOwners()
            ?->mapWithKeys(fn ($u) => [
                $u->id => $u->type === UserTypeEnum::AFFILIATOR ? "{$u->name} (Affiliator)" : $u->name,
            ])->all() ?? [];
    }

    /** Map [id user => type] untuk preview harga di sisi JS form. */
    private function resellerTypes(): array
    {
        return $this->pickableOwners()?->pluck('type', 'id')->all() ?? [];
    }

    private function pickableOwners(): ?Collection
    {
        $user = Auth::user();

        if ($user && ($user->isAdmin() || $user->isDeveloper())) {
            return User::whereIn('type', [UserTypeEnum::RESELLER, UserTypeEnum::AFFILIATOR])
                ->orderBy('name')
                ->get(['id', 'name', 'type']);
        }

        return null;
    }
}
