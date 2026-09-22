<?php

namespace Modules\Po\Http\Controllers;

use App\Http\Requests\GeneralRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
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
            'lokasiOptions' => Lokasi::getOptions(),
        ]);
    }

    /**
     * Prepare All: simpan qty prepare per produk langsung ke stock di 1 lokasi
     * tanpa form prepare per produk. Qty default = sisa, bisa diedit di tabel.
     * 1 transaksi per detail (partial commit: detail valid tetap masuk,
     * yang gagal dilaporkan).
     */
    public function postPrepareAll(GeneralRequest $request, $id)
    {
        $po = Po::with(['has_details.has_so_details'])->findOrFail($id);

        $validated = $request->validate([
            'lokasi_id' => ['required', 'exists:inv_lokasis,id'],
            'qty' => ['nullable', 'array'],
            'qty.*' => ['nullable', 'integer', 'min:0'],
        ]);
        $lokasiId = (int) $validated['lokasi_id'];
        $qtyMap = $validated['qty'] ?? [];

        $done = 0;
        $totalPcs = 0;
        $skipped = 0;
        $errors = [];

        foreach ($po->has_details as $detail) {
            $sisa = (int) $detail->po_detail_sisa;
            if ($sisa <= 0) {
                continue;
            }

            // Qty dari input tabel (default sisa), clamp ke sisa
            $wanted = array_key_exists($detail->id, $qtyMap) && $qtyMap[$detail->id] !== null
                ? (int) $qtyMap[$detail->id]
                : $sisa;
            $qty = max(0, min($wanted, $sisa));
            if ($qty <= 0) {
                $skipped++;

                continue;
            }

            // Cap mengikuti postPrepareProduct: prepared tidak boleh melebihi permintaan SO
            $soRequested = (float) $detail->has_so_details->sum('pivot.qty');
            if ($soRequested > 0) {
                $allowed = (int) $soRequested - (int) $detail->po_detail_prepared;
                if ($allowed <= 0) {
                    $skipped++;

                    continue;
                }
                $qty = min($qty, $allowed);
            }

            try {
                PreparePoDetailAction::run($detail, [
                    ['lokasi_id' => $lokasiId, 'qty' => $qty, 'expired_date' => null],
                ]);
                $done++;
                $totalPcs += $qty;
            } catch (\Throwable $th) {
                $errors[] = ($detail->po_detail_code ?? '#'.$detail->id).': '.$th->getMessage();
            }
        }

        if ($done > 0) {
            flash()->success("Prepare All selesai: {$done} produk ({$totalPcs} pcs) masuk stock.");
        }
        if ($skipped > 0 || $errors !== []) {
            $msg = trim($skipped > 0 ? "{$skipped} produk dilewati (qty 0 / melebihi permintaan SO). " : ''.implode(' ', $errors));

            return redirect()->route('po-po.getPrepare', ['id' => $po->id])->withErrors(['lokasi_id' => $msg])->withInput();
        }
        if ($done === 0) {
            return back()->withErrors(['lokasi_id' => 'Tidak ada sisa qty untuk di-prepare.'])->withInput();
        }

        return redirect()->route('po-po.getPrepare', ['id' => $po->id]);
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

            // Preview dikelompokkan per SUPPLIER (bukan per master): 1 card = 1 calon PO
            // dengan multiple product — sama seperti hasil generate (1 PO per supplier).
            $groups = $groups
                ->groupBy(fn ($g) => $g['supplier']->id)
                ->map(fn ($masterGroups) => [
                    'supplier' => $masterGroups->first()['supplier'],
                    'items' => $masterGroups->flatMap(fn ($g) => $g['items'])->values(),
                    'total_berat' => $masterGroups->sum('total_berat'),
                ])
                ->values();
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
            // Master terpilih (opsional, kosong = semua master)
            'masters' => ['nullable', 'array'],
            'masters.*' => ['integer'],
            // Barang terpilih dari tombol Generate per baris (kosong = semua barang)
            'products' => ['nullable', 'array'],
            'products.*' => ['integer'],
            // Supplier terpilih dari tombol Generate PO per card (kosong = semua supplier)
            'suppliers' => ['nullable', 'array'],
            'suppliers.*' => ['integer'],
        ]);
        $tanggal = $validated['tanggal'];

        [$masterGroups, $warnings] = $this->buildSoGroups($tanggal);

        $valid = $masterGroups->filter(fn ($group) => $group['supplier'] !== null);

        // Generate per card: hanya grup master yang dipilih lewat tombol di card-nya
        if (! empty($validated['masters'])) {
            $selected = array_map('intval', $validated['masters']);
            $valid = $valid->filter(fn ($group) => in_array((int) ($group['master']?->id ?? 0), $selected, true));
        }

        // Generate PO per supplier: hanya supplier yang dipilih lewat tombol di card-nya —
        // 1 PO dengan multiple product milik supplier tersebut.
        if (! empty($validated['suppliers'])) {
            $selectedSuppliers = array_map('intval', $validated['suppliers']);
            $valid = $valid->filter(fn ($group) => in_array((int) $group['supplier']->id, $selectedSuppliers, true));
        }

        // Generate satu per satu berdasarkan list barang: hanya product_id yang dipilih
        // yang masuk PO; grup yang barangnya habis terfilter dibuang.
        if (! empty($validated['products'])) {
            $selectedProducts = array_map('intval', $validated['products']);
            $valid = $valid
                ->map(function ($group) use ($selectedProducts) {
                    $group['items'] = $group['items']
                        ->filter(fn ($item) => in_array((int) $item['product_id'], $selectedProducts, true))
                        ->values();

                    return $group;
                })
                ->filter(fn ($group) => $group['items']->isNotEmpty());
        }

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
                        // Barang gabungan multi-SO: tiap so_detail penyumbang dicatat dengan qty-nya
                        foreach ($item['so_details'] as $src) {
                            $bySupplier[$sid]['lines'][$pid]['sources'][] = [
                                'so_detail_id' => (int) $src['id'],
                                'qty' => (int) $src['qty'],
                            ];
                            $coveredDetailIds[] = (int) $src['id'];
                        }
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

            // Gabungkan barang yang sama dari beberapa SO jadi satu baris:
            // qty & total berat dijumlah, sumber so_detail dicatat untuk pivot PO ↔ SO.
            // Contoh: Pakcoy dari 3 SO @ 3kg+4kg+3kg → 1 baris 10kg.
            $rows = $rows
                ->groupBy('product_id')
                ->map(function ($productRows) {
                    $first = $productRows->first();

                    return [
                        'product_id' => $first['product_id'],
                        'product_nama' => $first['product_nama'],
                        'berat' => $first['berat'],
                        'qty' => $productRows->sum('qty'),
                        'total_berat' => round($productRows->sum('total_berat'), 3),
                        'harga' => $first['harga'],
                        'harga_modal' => $first['harga_modal'],
                        'so_codes' => $productRows->pluck('so_code')->unique()->values()->all(),
                        'so_details' => $productRows
                            ->map(fn ($r) => ['id' => (int) $r['so_detail_id'], 'qty' => (int) $r['qty']])
                            ->values()
                            ->all(),
                    ];
                })
                ->values();

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

    /**
     * Download template CSV untuk import PO via Excel.
     * Satu baris = satu detail produk. Baris dengan PO Ref + Supplier + Tanggal
     * yang sama digabung jadi 1 PO. PO Ref kosong = tiap baris jadi PO sendiri.
     */
    public function getTemplate()
    {
        $delimiter = config('website.csv_delimiter', ';');
        $filename = 'po_template_'.date('Y-m-d_His').'.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($delimiter) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM agar Excel tampil benar

            fputcsv($handle, ['PO Ref', 'Tanggal', 'Supplier Kode', 'Produk Kode', 'Qty', 'Harga', 'Keterangan PO'], $delimiter);

            // Contoh: 2 baris ref sama = 1 PO dengan 2 detail, 1 baris ref kosong = 1 PO sendiri
            fputcsv($handle, ['PO-1', date('Y-m-d'), 'SUP-001', 'PRD-001', '10', '', 'Contoh PO gabungan 2 produk'], $delimiter);
            fputcsv($handle, ['PO-1', date('Y-m-d'), 'SUP-001', 'PRD-002', '5', '25000', ''], $delimiter);
            fputcsv($handle, ['', date('Y-m-d'), 'SUP-001', 'PRD-003', '3', '', 'Ref kosong = PO sendiri'], $delimiter);

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Halaman upload CSV PO.
     */
    public function getImport()
    {
        return $this->views('po::pages.po.import');
    }

    /**
     * Import PO dari CSV (diisi via Excel): parse → validasi per baris →
     * grouping per PO Ref → 1 transaksi per PO (partial commit: grup valid
     * tetap tersimpan, grup gagal dilaporkan).
     */
    public function postImport(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
        ]);

        $file = $request->file('file');
        $handle = fopen($file->getPathname(), 'r');

        if ($handle === false) {
            return redirect()->back()->with('error', 'Gagal membuka file CSV.');
        }

        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        $delimiter = config('website.csv_delimiter', ';');
        $header = fgetcsv($handle, 0, $delimiter);

        if ($header === false) {
            fclose($handle);

            return redirect()->back()->with('error', 'File CSV kosong atau format tidak valid.');
        }

        $headerMap = $this->mapImportHeader(array_map(fn ($v) => Str::lower(trim((string) $v)), $header));

        if (! in_array('po_tanggal', $headerMap, true) || ! in_array('supplier_kode', $headerMap, true) || ! in_array('product_kode', $headerMap, true)) {
            fclose($handle);

            return redirect()->back()->with('error', 'Header wajib: Tanggal, Supplier Kode, Produk Kode. Download template dulu.');
        }

        $rows = [];
        $rowNum = 1;

        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            $rowNum++;
            if (count(array_filter($row, fn ($v) => trim((string) $v) !== '')) === 0) {
                continue;
            }
            $data = [];
            foreach ($headerMap as $idx => $field) {
                $value = trim((string) ($row[$idx] ?? ''));
                $data[$field] = $value === '' ? null : $value;
            }
            $data['_row'] = $rowNum;
            $rows[] = $data;
        }
        fclose($handle);

        if ($rows === []) {
            return redirect()->back()->with('error', 'Tidak ada data di file CSV.');
        }

        // Validasi per baris: tanggal, supplier, produk, qty, harga
        $errors = [];
        $supplierCache = [];
        $productCache = [];

        foreach ($rows as &$r) {
            $rn = $r['_row'];

            // Tanggal: dukung Y-m-d, d/m/Y, d-m-Y
            $tanggal = null;
            if (! empty($r['po_tanggal'])) {
                foreach (['Y-m-d', 'd/m/Y', 'd-m-Y', 'Y/m/d'] as $fmt) {
                    try {
                        $tanggal = Carbon::createFromFormat($fmt, trim((string) $r['po_tanggal']))->format('Y-m-d');
                        break;
                    } catch (\Throwable) {
                        continue;
                    }
                }
                if ($tanggal === null) {
                    try {
                        $tanggal = Carbon::parse($r['po_tanggal'])->format('Y-m-d');
                    } catch (\Throwable) {
                        $errors[] = "Baris {$rn}: tanggal '{$r['po_tanggal']}' tidak valid (pakai YYYY-MM-DD).";
                    }
                }
            } else {
                $errors[] = "Baris {$rn}: tanggal kosong.";
            }
            $r['_tanggal'] = $tanggal;

            // Supplier: match kode dulu, fallback nama
            $supplier = null;
            if (! empty($r['supplier_kode'])) {
                $key = Str::lower(trim((string) $r['supplier_kode']));
                if (! array_key_exists($key, $supplierCache)) {
                    $supplierCache[$key] = Supplier::where('supplier_kode', $r['supplier_kode'])->first()
                        ?? Supplier::where('supplier_nama', $r['supplier_kode'])->first();
                }
                $supplier = $supplierCache[$key];
                if (! $supplier) {
                    $errors[] = "Baris {$rn}: supplier '{$r['supplier_kode']}' tidak ditemukan.";
                }
            } else {
                $errors[] = "Baris {$rn}: supplier kode kosong.";
            }
            $r['_supplier'] = $supplier;

            // Produk: match kode dulu, fallback nama
            $product = null;
            if (! empty($r['product_kode'])) {
                $key = Str::lower(trim((string) $r['product_kode']));
                if (! array_key_exists($key, $productCache)) {
                    $productCache[$key] = Product::where('product_kode', $r['product_kode'])->first()
                        ?? Product::where('product_nama', $r['product_kode'])->first();
                }
                $product = $productCache[$key];
                if (! $product) {
                    $errors[] = "Baris {$rn}: produk '{$r['product_kode']}' tidak ditemukan.";
                }
            } else {
                $errors[] = "Baris {$rn}: produk kode kosong.";
            }
            $r['_product'] = $product;

            // Qty wajib >= 1, dukung format ribuan Indonesia
            $qty = $this->parseAngka($r['qty'] ?? null);
            if ($qty === null || $qty < 1) {
                $errors[] = "Baris {$rn}: qty harus angka >= 1.";
                $r['_qty'] = null;
            } else {
                $r['_qty'] = (int) $qty;
            }

            // Harga opsional: kosong = harga modal produk; explicit 0 = gratis
            $hargaRaw = $r['harga'] ?? null;
            if ($hargaRaw === null) {
                $r['_harga'] = $product ? (float) ($product->product_harga_modal ?? $product->product_harga ?? 0) : 0;
            } else {
                $harga = $this->parseAngka($hargaRaw);
                if ($harga === null || $harga < 0) {
                    $errors[] = "Baris {$rn}: harga '{$hargaRaw}' tidak valid.";
                    $r['_harga'] = null;
                } else {
                    $r['_harga'] = (float) $harga;
                }
            }

            $r['_valid'] = $r['_tanggal'] && $r['_supplier'] && $r['_product'] && $r['_qty'] !== null && $r['_harga'] !== null;
        }
        unset($r);

        // Grouping: ref sama = 1 PO. Ref kosong = tiap baris PO sendiri.
        $groups = [];
        foreach ($rows as $r) {
            $ref = trim((string) ($r['po_ref'] ?? ''));
            $key = $ref !== '' ? 'ref:'.$ref : 'row:'.$r['_row'];
            $groups[$key] ??= ['ref' => $ref, 'rows' => []];
            $groups[$key]['rows'][] = $r;
        }

        $created = [];
        $failedGroups = 0;

        foreach ($groups as $key => $group) {
            $grows = $group['rows'];
            $firstNums = implode(', ', array_column($grows, '_row'));

            // Baris invalid → grup gagal tanpa insert
            if (collect($grows)->contains(fn ($x) => ! $x['_valid'])) {
                $failedGroups++;

                continue;
            }

            // Konsistensi dalam grup: tanggal & supplier harus sama
            $tanggalSet = collect($grows)->pluck('_tanggal')->unique()->values();
            $supplierSet = collect($grows)->pluck('_supplier.id')->unique()->values();
            if ($tanggalSet->count() > 1 || $supplierSet->count() > 1) {
                $errors[] = "Baris {$firstNums}: PO Ref '{$group['ref']}' harus 1 tanggal + 1 supplier yang sama.";
                $failedGroups++;

                continue;
            }

            $first = $grows[0];

            try {
                $po = DB::transaction(function () use ($grows, $first) {
                    $po = Po::create([
                        'po_tanggal' => $first['_tanggal'],
                        'po_id_supplier' => $first['_supplier']->id,
                        'po_keterangan' => $first['keterangan'] ?? ('Import Excel ref '.($first['po_ref'] ?: 'baris '.$first['_row'])),
                    ]);

                    $seq = 1;
                    foreach ($grows as $line) {
                        PoDetail::create([
                            'po_detail_id_po' => $po->id,
                            'po_detail_id_product' => $line['_product']->id,
                            'po_detail_code' => $this->nextDetailCode($po->po_code, $seq++),
                            'po_detail_qty' => $line['_qty'],
                            'po_detail_harga' => $line['_harga'],
                            'po_detail_keterangan' => null,
                        ]);
                    }

                    $po->recalculateTotals();

                    return $po;
                });
                $created[] = $po->po_code;
            } catch (\Throwable $th) {
                $errors[] = "Baris {$firstNums}: gagal simpan — ".$th->getMessage();
                $failedGroups++;
            }
        }

        $parts = [];
        if (count($created)) {
            $parts[] = count($created).' PO dibuat ('.implode(', ', array_slice($created, 0, 5)).(count($created) > 5 ? ', ...' : '').')';
        }
        if ($failedGroups) {
            $parts[] = $failedGroups.' grup gagal';
        }
        $summary = 'Import selesai: '.(empty($parts) ? 'tidak ada perubahan' : implode(', ', $parts)).'.';
        if ($errors !== []) {
            $summary .= ' '.count($errors).' error (lihat detail).';
        }

        return redirect()->route('po-po.getTable')
            ->with('success', $summary)
            ->with('import_errors', $errors);
    }

    private function mapImportHeader(array $header): array
    {
        $map = [
            'po ref' => 'po_ref',
            'ref' => 'po_ref',
            'kode po' => 'po_ref',
            'grup' => 'po_ref',
            'group' => 'po_ref',
            'tanggal' => 'po_tanggal',
            'tgl' => 'po_tanggal',
            'po tanggal' => 'po_tanggal',
            'supplier kode' => 'supplier_kode',
            'kode supplier' => 'supplier_kode',
            'supplier' => 'supplier_kode',
            'produk kode' => 'product_kode',
            'kode produk' => 'product_kode',
            'product kode' => 'product_kode',
            'produk' => 'product_kode',
            'product' => 'product_kode',
            'qty' => 'qty',
            'jumlah' => 'qty',
            'harga' => 'harga',
            'keterangan po' => 'keterangan',
            'keterangan' => 'keterangan',
        ];

        $result = [];
        foreach ($header as $idx => $col) {
            $result[$idx] = $map[trim($col)] ?? trim($col);
        }

        return $result;
    }

    /**
     * Parse angka Indonesia: "24.000", "Rp 24.000", "10,5" → int/float.
     */
    private function parseAngka(mixed $raw): float|int|null
    {
        if ($raw === null) {
            return null;
        }
        $s = trim((string) $raw);
        if ($s === '') {
            return null;
        }
        $negative = str_starts_with(ltrim($s), '-');
        $noRp = trim(str_ireplace('rp', '', $s), " \t\n\r\0\x0B-");
        if (preg_match('/^\d+[.,]\d{1,2}$/', $noRp)) {
            $value = (float) str_replace(',', '.', $noRp);
        } else {
            $digits = preg_replace('/[^0-9]/', '', $noRp);
            if ($digits === '' || $digits === null) {
                return null;
            }
            $value = (int) $digits;
        }
        if ($negative) {
            $value = -$value;
        }

        return $value;
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
