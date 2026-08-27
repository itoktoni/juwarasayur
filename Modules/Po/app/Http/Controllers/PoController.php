<?php

namespace Modules\Po\Http\Controllers;

use App\Http\Requests\GeneralRequest;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Catalog\Models\Product;
use Modules\Catalog\Models\ProductMaster;
use Modules\Inventory\Models\Lokasi;
use Modules\Po\Actions\PreparePoDetailAction;
use Modules\Po\Enums\PoStatusEnum;
use Modules\Po\Models\Po;
use Modules\Po\Models\PoDetail;
use Modules\Po\Models\Supplier;
use Modules\So\Enums\SoStatusEnum;
use Modules\So\Models\So;
use Modules\So\Models\SoDetail;

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
        $po = Po::with([
            'has_supplier',
            'has_details.has_product',
            'has_details.has_so_details.has_so.has_customer',
        ])->findOrFail($id);

        // Map per po_detail_id: daftar SO detail yang menjadi sumber + qty diminta.
        $soSources = $po->has_details->mapWithKeys(fn ($d) => [
            $d->id => [
                'rows' => $d->has_so_details,
                'total_diminta' => (float) $d->has_so_details->sum('pivot.qty'),
            ],
        ]);

        return $this->views('po::pages.po.prepare', [
            'model' => $po,
            'soSources' => $soSources,
        ]);
    }

    public function getPrepareProduct(GeneralRequest $request, $id)
    {
        $detail = PoDetail::with([
            'has_po.has_supplier',
            'has_product',
            'has_so_details.has_so.has_customer',
        ])->findOrFail($id);

        return $this->views('po::pages.po.prepare-product', [
            'model' => $detail,
            'lokasiOptions' => Lokasi::getOptions(),
            'soSources' => $detail->has_so_details,
            'totalDiminta' => (float) $detail->has_so_details->sum('pivot.qty'),
        ]);
    }

    public function postPrepareProduct(GeneralRequest $request, $id)
    {
        $detail = PoDetail::with(['has_product', 'has_so_details'])->findOrFail($id);

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

        // Validasi tambahan: prepared total tidak boleh melebihi qty diminta SO
        // (jika PO ini berasal dari SO). PO manual tanpa SO lewat tanpa cek.
        $soRequested = (float) $detail->has_so_details->sum('pivot.qty');
        if ($soRequested > 0 && ((int) $detail->po_detail_prepared + $total) > (int) $soRequested) {
            $selisih = ((int) $detail->po_detail_prepared + $total) - (int) $soRequested;

            return back()->withErrors(['locations' => "Qty prepared akan melebihi permintaan SO sebesar {$selisih}. Sesuaikan qty."])->withInput();
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

    /**
     * Print continues struk PO 80mm dengan garis potong antar struk.
     */
    public function getPrintContinues(GeneralRequest $request)
    {
        $ids = collect(explode(',', (string) $request->query('ids', '')))
            ->map(fn ($v) => (int) trim($v))
            ->filter()
            ->unique()
            ->values();

        abort_if($ids->isEmpty(), 404, 'Tidak ada PO untuk dicetak.');

        $list = $this->model->with(['has_details.has_product', 'has_supplier'])
            ->whereIn('id', $ids)
            ->orderBy('po_code')
            ->get();

        abort_if($list->isEmpty(), 404, 'Tidak ada PO untuk dicetak.');

        return response()->view('po::pages.po.print-continues', [
            'list' => $list,
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

    public function previewGenerateFromSo(GeneralRequest $request)
    {
        $tanggal = $request->input('tanggal');
        $groups = collect();
        $warnings = collect();

        if ($tanggal) {
            [$groups, $warnings] = $this->buildSoGroups($tanggal);
        }

        return $this->views('po::pages.po.generate-from-so', [
            'tanggal' => $tanggal,
            'groups' => $groups,
            'warnings' => $warnings,
        ]);
    }

    public function doGenerateFromSo(GeneralRequest $request)
    {
        $validated = $request->validate([
            'tanggal' => ['required', 'date'],
        ]);
        $tanggal = $validated['tanggal'];

        [$masterGroups, $warnings] = $this->buildSoGroups($tanggal);

        $valid = $masterGroups->filter(fn ($group) => $group['supplier'] !== null);

        if ($valid->isEmpty()) {
            return back()->withErrors(['tanggal' => 'Tidak ada item dengan supplier rekomendasi untuk tanggal '.$tanggal.'.'])->withInput();
        }

        try {
            $poCodes = DB::transaction(function () use ($valid, $tanggal) {
                $codes = [];
                $coveredDetailIds = [];

                // Gabungkan grup master per supplier — 1 PO = 1 supplier multi-baris produk
                $bySupplier = [];
                foreach ($valid as $group) {
                    $sid = $group['supplier']->id;
                    $bySupplier[$sid] ??= [
                        'supplier' => $group['supplier'],
                        'masters' => [],
                        'lines' => [],
                    ];
                    $bySupplier[$sid]['masters'][] = $group['nama'];

                    foreach ($group['items'] as $item) {
                        $pid = $item['product_id'];
                        $bySupplier[$sid]['lines'][$pid] ??= [
                            'product_id' => $pid,
                            'qty' => 0,
                            'harga' => (float) ($item['harga_modal'] ?? $item['harga']),
                            // Track asal-usul tiap baris PO: SO detail mana saja yang
                            // menyumbang qty, agar prepare bisa cross-check permintaan SO.
                            'sources' => [],
                        ];
                        $bySupplier[$sid]['lines'][$pid]['qty'] += (int) $item['qty'];
                        $bySupplier[$sid]['lines'][$pid]['sources'][] = [
                            'so_detail_id' => (int) $item['so_detail_id'],
                            'qty' => (int) $item['qty'],
                        ];
                        $coveredDetailIds[] = $item['so_detail_id'];
                    }
                }

                foreach ($bySupplier as $data) {
                    $po = Po::create([
                        'po_tanggal' => $tanggal,
                        'po_id_supplier' => $data['supplier']->id,
                        'po_keterangan' => 'Generate dari SO tanggal '.$tanggal.' — master: '.implode(', ', array_unique($data['masters'])),
                    ]);

                    $seq = 1;
                    foreach ($data['lines'] as $line) {
                        $poDetail = PoDetail::create([
                            'po_detail_id_po' => $po->id,
                            'po_detail_id_product' => $line['product_id'],
                            'po_detail_code' => sprintf('%s-%03d', $po->po_code, $seq++),
                            'po_detail_qty' => $line['qty'],
                            'po_detail_harga' => $line['harga'],
                            'po_detail_keterangan' => null,
                        ]);

                        // Simpan pivot per (po_detail, so_detail) dengan qty yang diminta.
                        // attach() akan deduplicate jika ada so_detail_id yang sama,
                        // tapi di sini tiap kombinasi sudah unik per product.
                        $syncData = [];
                        foreach ($line['sources'] as $src) {
                            $syncData[$src['so_detail_id']] = ['qty' => $src['qty']];
                        }
                        if (! empty($syncData)) {
                            $poDetail->has_so_details()->sync($syncData);
                        }
                    }

                    $po->recalculateTotals();
                    $codes[] = $po->po_code;
                }

                // Tandai tiap SO detail yang sudah dibuatkan PO (anti dobel-generate
                // di level detail — SO parsial tetap bisa digenerate sisanya)
                SoDetail::whereIn('id', array_unique($coveredDetailIds))
                    ->update(['po_generated_at' => now()]);

                // Tandai juga level SO bila seluruh detailnya ter-cover (indikator UI)
                $soIds = SoDetail::whereIn('id', array_unique($coveredDetailIds))->pluck('so_detail_id_so')->unique();
                foreach ($soIds as $soId) {
                    $totalDetails = SoDetail::where('so_detail_id_so', $soId)->count();
                    $coveredCount = SoDetail::whereIn('id', $coveredDetailIds)->where('so_detail_id_so', $soId)->count();
                    if ($coveredCount === $totalDetails) {
                        So::whereKey($soId)->update(['so_po_generated_at' => now()]);
                    }
                }

                return $codes;
            });

            flash()->success('PO berhasil dibuat: '.implode(', ', $poCodes));

            // Arahkan ke daftar PO — preview tanggal ini kini kosong karena SO sudah ditandai
            return redirect()->route('po-po.getTable');
        } catch (\Throwable $th) {
            return back()->withErrors(['tanggal' => $th->getMessage()])->withInput();
        }
    }

    /**
     * Kelompokkan detail SO pada satu tanggal berdasarkan product master.
     * Return [groups, warnings] — group tanpa supplier rekomendasi masuk warnings.
     *
     * @return array{0: Collection, 1: Collection}
     */
    private function buildSoGroups(string $tanggal): array
    {
        $details = SoDetail::query()
            ->whereHas('has_so', fn ($q) => $q
                ->whereDate('so_tanggal', $tanggal)
                ->where('so_status', '!=', SoStatusEnum::CANCELLED)
                ->whereNull('so_po_generated_at'))
            // Detail yang sudah pernah dibuatkan PO tidak boleh digenerate ulang
            ->whereNull('po_generated_at')
            ->with(['has_so', 'has_product.has_product_master.has_suppliers'])
            ->get();

        $groups = collect();
        $warnings = collect();

        foreach ($details->groupBy(fn ($d) => optional($d->has_product?->has_product_master)->id ?? 0) as $items) {
            $first = $items->first();
            $master = $first->has_product?->has_product_master;

            $rows = $items->map(fn ($d) => [
                'so_detail_id' => $d->id,
                'so_code' => $d->has_so->so_code,
                'product_id' => $d->so_detail_id_product,
                'product_nama' => $d->has_product?->product_nama,
                'berat' => (float) ($d->has_product?->product_berat ?? 0),
                'qty' => (int) $d->so_detail_qty,
                'total_berat' => (float) ($d->has_product?->product_berat ?? 0) * (int) $d->so_detail_qty,
                'harga' => (float) ($d->has_product?->product_harga ?? 0),
                'harga_modal' => $d->has_product?->product_harga_modal,
            ])->values();

            $group = [
                'master' => $master,
                'nama' => $master?->{ProductMaster::field_name()} ?? $first->has_product?->product_nama.' (tanpa master)',
                'items' => $rows,
                'total_berat' => $rows->sum('total_berat'),
                'supplier' => $master?->has_suppliers->firstWhere('pivot.is_recommended', true),
                // Produk tanpa master ATAU master tanpa supplier rekomendasi tidak boleh digenerate
                'reason' => $master === null ? 'Tanpa Product Master' : 'Tanpa Supplier Rekomendasi',
            ];

            if ($group['supplier'] === null) {
                $warnings->push($group);
            } else {
                $groups->push($group);
            }
        }

        return [$groups, $warnings];
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
