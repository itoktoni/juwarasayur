<?php

namespace Modules\So\Http\Controllers;

use App\Enums\UserTypeEnum;
use App\Http\Requests\GeneralRequest;
use App\Models\User;
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
    public function __construct(So $model)
    {
        $this->model = $model::getModel();
    }

    protected function share($data = [])
    {
        $products = Product::where('is_active', true)->orderBy('product_nama')->get(['id', 'product_nama', 'product_harga']);
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

        // Input kosong ("") → fallback ke user login
        if (empty($data['so_id_reseller'])) {
            $data['so_id_reseller'] = Auth::id();
        }

        // Reseller hanya boleh order untuk customer-nya sendiri
        if (! empty($data['so_id_customer'])) {
            $customer = User::findOrFail($data['so_id_customer']);
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

        if ($user && $user->type === UserTypeEnum::RESELLER) {
            return $user->hasCustomers()->orderBy('name')->pluck('name', 'id')->all();
        }

        // Admin/developer: semua customer
        return User::where('type', UserTypeEnum::CUSTOMER)->orderBy('name')->pluck('name', 'id')->all();
    }

    private function resellerOptions(): array
    {
        $user = Auth::user();

        if ($user && ($user->isAdmin() || $user->isDeveloper())) {
            return User::where('type', UserTypeEnum::RESELLER)->orderBy('name')->pluck('name', 'id')->all();
        }

        return [];
    }
}
