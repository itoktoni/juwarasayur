<?php

namespace Modules\So\Http\Controllers;

use App\Enums\UserTypeEnum;
use App\Http\Requests\GeneralRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Modules\Catalog\Models\Product;
use Modules\So\Enums\ConsignmentStatusEnum;
use Modules\So\Models\Consignment;
use Modules\So\Models\ConsignmentDetail;

/**
 * Admin: titip jual (konsinyasi). Pagi = titip barang ke reseller,
 * malam = tarik uang berdasarkan jumlah terjualan (settle).
 */
class ConsignmentController extends Controller
{
    public function __construct(Consignment $model)
    {
        $this->model = $model::getModel();
    }

    protected function share($data = [])
    {
        $products = Product::where('is_active', true)->orderBy('product_nama')->get(['id', 'product_nama', 'product_harga']);

        return array_merge([
            'model' => $this->model,
            'resellerOptions' => User::where('type', UserTypeEnum::RESELLER)->orderBy('name')->pluck('name', 'id')->all(),
            'statusOptions' => ConsignmentStatusEnum::getOptions(),
            'productOptions' => $products->pluck('product_nama', 'id')->all(),
            'productPrices' => $products->mapWithKeys(fn ($p) => [$p->id => (float) $p->product_harga])->all(),
        ], $data);
    }

    protected function getData()
    {
        return $this->model->with(['has_reseller', 'has_details'])->filter()->sort();
    }

    public function getUpdate(GeneralRequest $request, $id)
    {
        $data = $this->model->with(['has_details.has_product'])->findOrFail($id);

        if ($data->status === ConsignmentStatusEnum::SETTLED) {
            flash()->warning('Titipan sudah ditarik (settled) dan tidak bisa diubah.');

            return redirect()->route('so-consignment.getTable');
        }

        return $this->views($this->template(), ['model' => $data]);
    }

    public function postCreate(GeneralRequest $request)
    {
        $data = $this->validated($request);

        try {
            DB::transaction(function () use ($data) {
                $consignment = Consignment::create(collect($data)->except('details')->toArray());
                $this->syncDetails($consignment, $data['details']);
                $consignment->recalculateTotals();
            });

            return $this->response($this->payload(TOAST_SUCCESS));
        } catch (\Throwable $th) {
            return $this->response($this->payload(TOAST_FAILED, $th->getMessage()));
        }
    }

    public function postUpdate(GeneralRequest $request, $id)
    {
        $consignment = Consignment::findOrFail($id);

        abort_if($consignment->status === ConsignmentStatusEnum::SETTLED, 422, 'Titipan sudah settled.');

        $data = $this->validated($request);

        try {
            DB::transaction(function () use ($data, $consignment) {
                $consignment->update(collect($data)->except('details')->toArray());
                $this->syncDetails($consignment, $data['details']);
                $consignment->recalculateTotals();
            });

            return $this->response($this->payload(TOAST_SUCCESS));
        } catch (\Throwable $th) {
            return $this->response($this->payload(TOAST_FAILED, $th->getMessage()));
        }
    }

    /**
     * Halaman penarikan uang: isi qty terjual & sisa per produk.
     */
    public function getSettle(GeneralRequest $request, $id)
    {
        $data = $this->model->with(['has_details.has_product', 'has_reseller'])->findOrFail($id);

        abort_if($data->status === ConsignmentStatusEnum::SETTLED, 404, 'Titipan ini sudah ditarik.');

        return response()->view('so::pages.consignment.settle', [
            'model' => $data,
        ]);
    }

    public function postSettle(GeneralRequest $request, $id)
    {
        $consignment = Consignment::with(['has_details'])->findOrFail($id);

        abort_if($consignment->status === ConsignmentStatusEnum::SETTLED, 422, 'Titipan ini sudah ditarik.');

        $validated = $request->validate([
            'rows' => ['required', 'array'],
            'rows.*.qty_sold' => ['nullable', 'numeric', 'min:0'],
            'rows.*.qty_returned' => ['nullable', 'numeric', 'min:0'],
        ]);

        try {
            DB::transaction(function () use ($validated, $consignment) {
                $consignment->settle($validated['rows']);
            });

            // Refresh untuk invoice
            $consignment->refresh()->load(['has_details.has_product', 'has_reseller']);

            return response()->view('so::pages.consignment.invoice', [
                'model' => $consignment,
            ]);
        } catch (\Throwable $th) {
            flash()->error($th->getMessage());

            return redirect()->route('so-consignment.getSettle', ['id' => $id]);
        }
    }

    /**
     * Dashboard konsinyasi hari ini: reseller dengan consignasi=true,
     * menampilkan barang yang dititipkan hari ini + ringkasan.
     */
    public function getToday(GeneralRequest $request)
    {
        $today = today();

        $resellers = User::where('type', UserTypeEnum::RESELLER)
            ->where('consignasi', true)
            ->with(['has_consignments' => fn ($q) => $q->whereDate('consignment_date', $today)->with('has_details')])
            ->get()
            ->map(function (User $reseller) use ($today) {
                $consignments = $reseller->has_consignments;

                return [
                    'reseller' => $reseller,
                    'open_count' => $consignments->where('status', ConsignmentStatusEnum::OPEN)->count(),
                    'settled_count' => $consignments->where('status', ConsignmentStatusEnum::SETTLED)->count(),
                    'qty_consigned' => (float) $consignments->sum('total_qty'),
                    'qty_sold' => (float) $consignments->sum('total_sold'),
                    'amount_collected' => (float) $consignments->sum('total_amount'),
                    'details' => $consignments->flatMap->has_details,
                    'date' => $today,
                ];
            });

        return response()->view('so::pages.consignment.today', [
            'resellers' => $resellers,
            'today' => $today,
        ]);
    }

    private function validated(GeneralRequest $request): array
    {
        $data = $request->validate((new Consignment)->rules());

        foreach ($data['details'] as &$row) {
            $product = Product::find($row['product_id']);
            $row['price'] = $row['price'] ?? (float) ($product?->product_harga ?? 0);
        }

        $data['status'] = ConsignmentStatusEnum::OPEN;

        return $data;
    }

    private function syncDetails(Consignment $consignment, array $details): void
    {
        $existing = $consignment->has_details()->get()->keyBy('id');
        $keepIds = [];

        foreach ($details as $row) {
            $attrs = [
                'consignment_id' => $consignment->id,
                'product_id' => (int) $row['product_id'],
                'qty' => (float) $row['qty'],
                'price' => (float) ($row['price'] ?? 0),
            ];

            $prev = isset($row['id']) ? $existing->get((int) $row['id']) : null;
            if ($prev) {
                $prev->update($attrs);
                $keepIds[] = (int) $prev->id;
            } else {
                $keepIds[] = (int) ConsignmentDetail::create($attrs)->id;
            }
        }

        foreach ($existing as $detail) {
            if (! in_array((int) $detail->id, $keepIds, true)) {
                $detail->delete();
            }
        }
    }
}
