<?php

namespace Modules\Po\Http\Controllers;

use App\Http\Requests\GeneralRequest;
use Illuminate\Support\Facades\DB;
use Modules\Catalog\Models\Product;
use Modules\Inventory\Models\Lokasi;
use Modules\Po\Actions\PreparePoDetailAction;
use Modules\Po\Enums\PoStatusEnum;
use Modules\Po\Models\Po;
use Modules\Po\Models\PoDetail;
use Modules\Po\Models\Supplier;

class PoController extends Controller
{
    public function __construct(Po $model)
    {
        $this->model = $model::getModel();
    }

    protected function share($data = [])
    {
        $products = Product::where('is_active', true)->orderBy('product_nama')->get(['id', 'product_nama', 'product_harga', 'product_harga_modal']);
        $trim = fn ($v) => $v === null || $v === '' ? $v : rtrim(rtrim(number_format((float) $v, 2, '.', ''), '0'), '.');

        return array_merge([
            'model' => $this->model,
            'supplierOptions' => Supplier::getOptions(),
            'statusOptions' => PoStatusEnum::getOptions(),
            'productOptions' => $products->pluck('product_nama', 'id')->all(),
            'productPrices' => $products->mapWithKeys(fn ($p) => [$p->id => $trim($p->product_harga_modal ?? $p->product_harga)])->all(),
            'discountTypeOptions' => ['nominal' => 'Nominal (Rp)', 'percent' => 'Persen (%)'],
            'ppnRateDefault' => (float) config('po.ppn_rate', 11),
            'pphRateDefault' => (float) config('po.pph_rate', 2),
        ], $data);
    }

    protected function getData()
    {
        return $this->model->with(['has_supplier', 'has_details.has_product'])->filter()->sort();
    }

    public function getPrepare(GeneralRequest $request, $id)
    {
        $po = Po::with(['has_supplier', 'has_details.has_product'])->findOrFail($id);

        return $this->views('po::pages.po.prepare', [
            'model' => $po,
        ]);
    }

    public function getPrepareProduct(GeneralRequest $request, $id)
    {
        $detail = PoDetail::with(['has_po.has_supplier', 'has_product'])->findOrFail($id);

        return $this->views('po::pages.po.prepare-product', [
            'model' => $detail,
            'lokasiOptions' => Lokasi::getOptions(),
        ]);
    }

    public function postPrepareProduct(GeneralRequest $request, $id)
    {
        $detail = PoDetail::with('has_product')->findOrFail($id);

        $validated = $request->validate([
            'locations' => ['required', 'array', 'min:1'],
            'locations.*.lokasi_id' => ['required', 'exists:inv_lokasis,id'],
            'locations.*.qty' => ['required', 'integer', 'min:1'],
            'locations.*.expired_date' => ['nullable', 'date'],
        ]);

        $total = (int) collect($validated['locations'])->sum('qty');
        $sisa = $detail->po_detail_sisa;

        if ($total <= 0 || $total > $sisa) {
            return back()->withErrors(['locations' => 'Total qty melebihi sisa qty product.'])->withInput();
        }

        try {
            PreparePoDetailAction::run($detail, $validated['locations']);
            flash()->success('Stock product berhasil di-prepare.');
        } catch (\Throwable $th) {
            flash()->error($th->getMessage());
        }

        return redirect()->route('po-po.getPrepare', ['id' => $detail->po_detail_id_po]);
    }

    public function getUpdate(GeneralRequest $request, $id)
    {
        $data = $this->model->with(['has_details.has_product'])->findOrFail($id);

        return $this->views($this->template(), [
            'model' => $data,
        ]);
    }

    public function postCreate(GeneralRequest $request)
    {
        $data = $request->validate((new Po)->rules());
        $data['po_discount_type'] ??= 'nominal';
        $data['po_ppn_rate'] ??= (float) config('po.ppn_rate', 11);
        $data['po_pph_rate'] ??= (float) config('po.pph_rate', 2);

        try {
            $po = DB::transaction(function () use ($data) {
                $po = Po::create(collect($data)->except('details')->toArray());
                $this->syncDetails($po, $data['details']);
                $po->recalculateTotals();

                return $po->load('has_details.has_product');
            });

            return $this->response($this->payload(TOAST_SUCCESS, $po));
        } catch (\Throwable $th) {
            return $this->response($this->payload(TOAST_FAILED, $th->getMessage()));
        }
    }

    public function postUpdate(GeneralRequest $request, $id)
    {
        $po = Po::findOrFail($id);
        $data = $request->validate((new Po)->rules());
        unset($data['po_code']);
        $data['po_discount_type'] ??= $po->po_discount_type ?? 'nominal';
        $data['po_ppn_rate'] ??= $po->po_ppn_rate ?? (float) config('po.ppn_rate', 11);
        $data['po_pph_rate'] ??= $po->po_pph_rate ?? (float) config('po.pph_rate', 2);

        try {
            $po = DB::transaction(function () use ($data, $po) {
                $po->update(collect($data)->except('details')->toArray());
                $this->syncDetails($po, $data['details']);
                $po->recalculateTotals();

                return $po->load('has_details.has_product');
            });

            return $this->response($this->payload(TOAST_SUCCESS, $po));
        } catch (\Throwable $th) {
            return $this->response($this->payload(TOAST_FAILED, $th->getMessage()));
        }
    }

    private function syncDetails(Po $po, array $details): void
    {
        $existing = $po->has_details()->get()->keyBy('id');
        $keepIds = [];
        $seq = 1;

        $productPrices = Product::whereIn('id', collect($details)->pluck('po_detail_id_product'))->get(['id', 'product_harga', 'product_harga_modal'])->mapWithKeys(fn ($p) => [$p->id => $p->product_harga_modal ?? $p->product_harga]);

        foreach ($details as $row) {
            $productId = (int) $row['po_detail_id_product'];
            $qty = (int) $row['po_detail_qty'];
            $hargaRaw = $row['po_detail_harga'] ?? null;
            $harga = $hargaRaw === '' || $hargaRaw === null ? (float) ($productPrices[$productId] ?? 0) : (float) $hargaRaw;

            $attrs = [
                'po_detail_id_po' => $po->id,
                'po_detail_id_product' => $productId,
                'po_detail_qty' => $qty,
                'po_detail_harga' => $harga,
                'po_detail_keterangan' => $row['po_detail_keterangan'] ?? null,
            ];

            $id = $row['po_detail_id'] ?? null;
            $prev = $id ? $existing->get((int) $id) : null;

            if ($prev) {
                $prev->update($attrs);
                $keepIds[] = (int) $prev->id;
            } else {
                $attrs['po_detail_code'] = $this->nextDetailCode($po->po_code, $seq);
                $keepIds[] = (int) PoDetail::create($attrs)->id;
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

    private function nextDetailCode(string $poCode, int $seq): string
    {
        $code = sprintf('%s-%03d', $poCode, $seq);
        while (PoDetail::where('po_detail_code', $code)->exists()) {
            $seq++;
            $code = sprintf('%s-%03d', $poCode, $seq);
        }

        return $code;
    }
}
