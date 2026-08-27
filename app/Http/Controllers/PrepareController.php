<?php

namespace App\Http\Controllers;

use App\Actions\PrepareSoProductAction;
use App\Http\Requests\PrepareRequest;
use App\Models\PrepareAllocation;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Modules\Catalog\Models\Product;
use Modules\Inventory\Models\Lokasi;
use Modules\So\Enums\SoStatusEnum;
use Modules\So\Models\So;
use Modules\So\Models\SoDetail;

/**
 * Prepare barang dari gudang untuk memenuhi SO.
 * Alur: pilih SO → group by product → siapkan per produk → print label.
 */
class PrepareController extends Controller
{
    /**
     * Halaman utama: filter SO (paid/confirmed) yang siap di-prepare.
     */
    public function index(Request $request): View
    {
        $tanggal = $request->query('tanggal', now()->toDateString());

        $sos = So::query()
            ->with(['has_customer', 'has_details.has_product', 'has_details.has_prepare_allocations'])
            ->whereDate('so_tanggal', $tanggal)
            ->whereIn('so_status', [SoStatusEnum::PAID, SoStatusEnum::CONFIRMED])
            ->orderBy('id')
            ->get()
            // Filter SO yang masih ada item belum full prepared
            ->filter(function (So $so) {
                return $so->has_details->contains(function (SoDetail $d) {
                    $sisa = (int) $d->so_detail_qty - (int) $d->has_prepare_allocations->sum('qty');

                    return $sisa > 0;
                });
            })
            ->values();

        return view('prepare.index', [
            'tanggal' => $tanggal,
            'sos' => $sos,
        ]);
    }

    /**
     * Submit SO yang dipilih → group by product.
     */
    public function group(Request $request): View|RedirectResponse
    {
        $soIds = (array) $request->input('so_ids', []);
        $soIds = array_filter(array_map('intval', $soIds));

        if (empty($soIds)) {
            return redirect()->route('prepare.index')->withErrors(['Pilih minimal satu SO.']);
        }

        $details = SoDetail::with(['has_so.has_customer', 'has_product', 'has_prepare_allocations'])
            ->whereIn('so_detail_id_so', $soIds)
            ->orderBy('so_detail_id_product')
            ->orderBy('id')
            ->get();

        // Group by product
        $groups = $details->groupBy('so_detail_id_product')->map(function ($items, $productId) {
            $product = $items->first()->has_product;
            $totalDiminta = (int) $items->sum('so_detail_qty');
            $totalDisiapkan = (int) $items->sum(fn ($d) => $d->has_prepare_allocations->sum('qty'));
            $sisa = max(0, $totalDiminta - $totalDisiapkan);

            // Daftar SO yang meminta
            $sos = $items->map(fn ($d) => [
                'so_detail_id' => $d->id,
                'so_code' => $d->has_so?->so_code,
                'customer' => $d->has_so?->so_customer_name ?? $d->has_so?->has_customer?->name ?? '-',
                'qty_diminta' => (int) $d->so_detail_qty,
                'qty_disiapkan' => (int) $d->has_prepare_allocations->sum('qty'),
            ])->values();

            return [
                'product' => $product,
                'total_diminta' => $totalDiminta,
                'total_disiapkan' => $totalDisiapkan,
                'sisa' => $sisa,
                'sos' => $sos,
                'so_detail_ids' => $items->pluck('id')->all(),
            ];
        })->values();

        // Lokasi gudang untuk dropdown
        $lokasiOptions = Lokasi::where('is_active', true)->orderBy('sort_order')->get(['id', 'lokasi_nama']);

        return view('prepare.group', [
            'soIds' => $soIds,
            'groups' => $groups,
            'lokasiOptions' => $lokasiOptions,
        ]);
    }

    /**
     * Form prepare 1 produk.
     */
    public function prepareForm(Request $request, int $product): View|RedirectResponse
    {
        $product = Product::findOrFail($product);
        $soDetailIds = (array) $request->query('so_detail_ids', []);
        $soDetailIds = array_filter(array_map('intval', $soDetailIds));

        if (empty($soDetailIds)) {
            return redirect()->route('prepare.index')->withErrors(['Pilih minimal satu SO.']);
        }

        $details = SoDetail::with(['has_so.has_customer', 'has_prepare_allocations'])
            ->whereIn('id', $soDetailIds)
            ->where('so_detail_id_product', $product->id)
            ->get();

        $totalDiminta = (int) $details->sum('so_detail_qty');
        $totalDisiapkan = (int) $details->sum(fn ($d) => $d->has_prepare_allocations->sum('qty'));
        $sisa = max(0, $totalDiminta - $totalDisiapkan);

        $lokasiOptions = Lokasi::where('is_active', true)->orderBy('sort_order')->get(['id', 'lokasi_nama']);

        return view('prepare.prepare', [
            'product' => $product,
            'details' => $details,
            'totalDiminta' => $totalDiminta,
            'totalDisiapkan' => $totalDisiapkan,
            'sisa' => $sisa,
            'soDetailIds' => $soDetailIds,
            'lokasiOptions' => $lokasiOptions,
        ]);
    }

    /**
     * Submit prepare 1 produk.
     */
    public function storePrepare(PrepareRequest $request, int $product): RedirectResponse
    {
        $product = Product::findOrFail($product);
        $lokasi = Lokasi::findOrFail($request->input('lokasi_id'));

        try {
            /** @var User|null $user */
            $user = Auth::user();
            $distributions = PrepareSoProductAction::run(
                $product,
                $lokasi,
                (int) $request->input('qty'),
                (array) $request->input('so_detail_ids'),
                $user,
                $request->input('expired_date')
            );

            $totalDisiapkan = array_sum(array_column($distributions, 'qty'));
            $jumlahSo = count($distributions);
            flash()->success("Berhasil menyiapkan {$totalDisiapkan} unit untuk {$jumlahSo} SO.");
        } catch (\Throwable $e) {
            flash()->error($e->getMessage());

            return back()->withInput();
        }

        // Redirect ke group dengan so_ids yang sama (via session/flash old)
        $soIds = SoDetail::whereIn('id', $request->input('so_detail_ids'))
            ->pluck('so_detail_id_so')
            ->unique()
            ->all();

        return redirect()->route('prepare.group', ['so_ids' => $soIds]);
    }

    /**
     * Halaman progress: list semua SO detail dengan status persiapan.
     * Tiap baris = 1 item (produk) dari 1 SO — admin bisa lihat sudah disiapkan
     * berapa, di lokasi mana, dan status badge (Siap / Sebagian / Belum).
     */
    public function progress(Request $request): View
    {
        $filterStatus = $request->query('status', 'all'); // all | ready | partial | pending
        $search = trim((string) $request->query('q', ''));

        // Ambil semua SO detail dari SO paid/confirmed/delivered (yang relevan untuk prepare)
        $query = SoDetail::with([
            'has_so.has_customer',
            'has_product',
            'has_prepare_allocations.has_lokasi',
        ])
            ->whereHas('has_so', function ($q) {
                $q->whereIn('so_status', [SoStatusEnum::PAID, SoStatusEnum::CONFIRMED, SoStatusEnum::DELIVERED]);
            })
            ->orderByDesc('id');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->whereHas('has_so', fn ($s) => $s->where('so_code', 'like', "%{$search}%")
                    ->orWhere('so_customer_name', 'like', "%{$search}%"))
                    ->orWhereHas('has_product', fn ($p) => $p->where('product_nama', 'like', "%{$search}%")
                        ->orWhere('product_kode', 'like', "%{$search}%"));
            });
        }

        $details = $query->get();

        // Hitung status per-baris & terapkan filter
        $rows = $details->map(function (SoDetail $d) {
            $diminta = (int) $d->so_detail_qty;
            $disiapkan = (int) $d->has_prepare_allocations->sum('qty');
            $sisa = max(0, $diminta - $disiapkan);
            $lokasi = $d->has_prepare_allocations->pluck('has_lokasi.lokasi_nama')->unique()->filter()->implode(', ');

            $status = $sisa === 0 ? 'ready' : ($disiapkan > 0 ? 'partial' : 'pending');

            return [
                'so' => $d->has_so,
                'product' => $d->has_product,
                'so_detail' => $d,
                'diminta' => $diminta,
                'disiapkan' => $disiapkan,
                'sisa' => $sisa,
                'lokasi' => $lokasi ?: '—',
                'status' => $status,
                'percent' => $diminta > 0 ? min(100, round($disiapkan / $diminta * 100)) : 0,
            ];
        });

        if ($filterStatus !== 'all') {
            $rows = $rows->where('status', $filterStatus)->values();
        }

        // Statistik ringkas
        $stats = [
            'total' => $rows->count(),
            'ready' => $rows->where('status', 'ready')->count(),
            'partial' => $rows->where('status', 'partial')->count(),
            'pending' => $rows->where('status', 'pending')->count(),
        ];

        return view('prepare.progress', [
            'rows' => $rows,
            'stats' => $stats,
            'filterStatus' => $filterStatus,
            'search' => $search,
        ]);
    }

    /**
     * Print label thermal per item yang sudah disiapkan.
     */
    public function printLabel(Request $request): View
    {
        $soId = (int) $request->query('so_id');
        $so = null;
        $allocations = collect();

        if ($soId) {
            $so = So::with(['has_customer'])->find($soId);
            if ($so) {
                $allocations = PrepareAllocation::with(['has_product', 'has_lokasi', 'has_so_detail.has_so'])
                    ->whereHas('has_so_detail', fn ($q) => $q->where('so_detail_id_so', $soId))
                    ->orderBy('id')
                    ->get();
            }
        }

        $sos = So::whereHas('has_details.has_prepare_allocations')
            ->orderByDesc('id')
            ->limit(100)
            ->get(['id', 'so_code', 'so_tanggal', 'so_customer_name']);

        return view('prepare.print-label', [
            'so' => $so,
            'allocations' => $allocations,
            'sos' => $sos,
        ]);
    }
}
