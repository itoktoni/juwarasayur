<?php

namespace App\Http\Controllers;

use App\Charts\DashboardChart;
use App\Enums\UserTypeEnum;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Modules\Catalog\Models\Product;
use Modules\Po\Enums\PoStatusEnum;
use Modules\Po\Models\Po;
use Modules\So\Enums\SoStatusEnum;
use Modules\So\Models\So;

class DashboardController extends Controller
{
    public function __invoke(Request $request, DashboardChart $chart)
    {
        $user = $request->user();

        if ($user && $user->isReseller()) {
            return $this->resellerDashboard($user, $chart);
        }

        return $this->adminDashboard($chart);
    }

    /**
     * Dashboard untuk admin / editor / developer:
     * ringkasan penjualan, barang yang harus di-prepare, + PO sebagai pengeluaran & laba rugi.
     */
    private function adminDashboard(DashboardChart $chart)
    {
        $preparedStatuses = [SoStatusEnum::PAID, SoStatusEnum::CONFIRMED];
        // Realisasi: SO dianggap pemasukan jika sudah dibayar (bukan pending/cancel)
        $realizedSo = [SoStatusEnum::PAID, SoStatusEnum::CONFIRMED, SoStatusEnum::SHIPPED, SoStatusEnum::DELIVERED];
        // PO dianggap pengeluaran jika ordered/partial/closed (pending = belum keluar uang)
        $realizedPo = [PoStatusEnum::ORDERED, PoStatusEnum::PARTIAL, PoStatusEnum::CLOSED];

        // Penjualan (revenue) — hanya SO terealisasi
        $revenue = (float) So::whereIn('so_status', $realizedSo)->sum('so_grand_total');
        // Pengeluaran — hanya PO terealisasi
        $pengeluaran = (float) Po::whereIn('po_status', $realizedPo)->sum('po_grand_total');
        $laba = $revenue - $pengeluaran;
        $labaMargin = $revenue > 0 ? round($laba / $revenue * 100, 1) : 0;

        // Cash flow — 30 hari & bulan ini (pakai status realisasi yang sama)
        $today = Carbon::today();
        $monthStart = Carbon::now()->startOfMonth();
        $cashIn30 = (float) So::whereDate('so_tanggal', '>=', $today->copy()->subDays(30))
            ->whereIn('so_status', $realizedSo)->sum('so_grand_total');
        $cashOut30 = (float) Po::whereDate('po_tanggal', '>=', $today->copy()->subDays(30))
            ->whereIn('po_status', $realizedPo)->sum('po_grand_total');
        $cashInMonth = (float) So::whereDate('so_tanggal', '>=', $monthStart)
            ->whereIn('so_status', $realizedSo)->sum('so_grand_total');
        $cashOutMonth = (float) Po::whereDate('po_tanggal', '>=', $monthStart)
            ->whereIn('po_status', $realizedPo)->sum('po_grand_total');

        $stats = [
            'total_orders' => So::count(),
            'revenue' => $revenue,
            'pengeluaran' => $pengeluaran,
            'laba' => $laba,
            'laba_margin' => $labaMargin,
            'to_prepare' => So::whereIn('so_status', $preparedStatuses)->count(),
            'unpaid' => So::where('so_status', SoStatusEnum::PENDING)->count(),
            'total_customers' => User::where('type', UserTypeEnum::CUSTOMER)->count(),
            'total_resellers' => User::where('type', UserTypeEnum::RESELLER)->count(),
            'total_products' => Product::count(),
            // PO
            'total_po' => Po::count(),
            'po_pending' => Po::where('po_status', PoStatusEnum::PENDING)->count(),
            'po_ordered' => Po::where('po_status', PoStatusEnum::ORDERED)->count(),
            'po_closed' => Po::where('po_status', PoStatusEnum::CLOSED)->count(),
            // Cash flow
            'cash_in_30' => $cashIn30,
            'cash_out_30' => $cashOut30,
            'net_30' => $cashIn30 - $cashOut30,
            'cash_in_month' => $cashInMonth,
            'cash_out_month' => $cashOutMonth,
            'net_month' => $cashInMonth - $cashOutMonth,
        ];

        // Pesanan yang harus di-prepare (sudah bayar / dikonfirmasi, belum dikirim)
        $toPrepare = So::with(['has_customer', 'has_reseller'])
            ->whereIn('so_status', $preparedStatuses)
            ->orderByDesc('so_tanggal')
            ->limit(8)
            ->get();

        $recentOrders = So::with(['has_customer', 'has_reseller'])
            ->orderByDesc('so_tanggal')
            ->limit(8)
            ->get();

        $recentPos = Po::with(['has_supplier'])
            ->orderByDesc('po_tanggal')
            ->limit(5)
            ->get();

        return view('dashboard.admin', compact('stats', 'toPrepare', 'recentOrders', 'recentPos'))
            ->with('salesChart', $chart->salesRevenue())
            ->with('statusChart', $chart->orderStatusBreakdown())
            ->with('profitChart', $chart->profitLast7Days())
            ->with('poStatusChart', $chart->poStatusBreakdown());
    }

    /**
     * Dashboard untuk reseller:
     * penjualan hari ini, yang belum bayar, jumlah customer, dll.
     */
    private function resellerDashboard(User $user, DashboardChart $chart)
    {
        $today = Carbon::today();

        $stats = [
            'sales_today' => So::where('so_id_reseller', $user->id)
                ->whereDate('so_tanggal', $today)
                ->count(),
            'revenue_today' => (float) So::where('so_id_reseller', $user->id)
                ->whereDate('so_tanggal', $today)
                ->whereNotIn('so_status', [SoStatusEnum::CANCELLED])
                ->sum('so_grand_total'),
            'unpaid' => So::where('so_id_reseller', $user->id)
                ->where('so_status', SoStatusEnum::PENDING)
                ->count(),
            'customers' => User::where('type', UserTypeEnum::CUSTOMER)
                ->where('reference_id', $user->id)
                ->count(),
            'total_orders' => So::where('so_id_reseller', $user->id)->count(),
            'to_prepare' => So::where('so_id_reseller', $user->id)
                ->whereIn('so_status', [SoStatusEnum::PAID, SoStatusEnum::CONFIRMED])
                ->count(),
            'revenue_total' => (float) So::where('so_id_reseller', $user->id)
                ->whereNotIn('so_status', [SoStatusEnum::CANCELLED])
                ->sum('so_grand_total'),
        ];

        $recentOrders = So::with(['has_customer'])
            ->where('so_id_reseller', $user->id)
            ->orderByDesc('so_tanggal')
            ->limit(8)
            ->get();

        return view('dashboard.reseller', compact('stats', 'recentOrders'))
            ->with('salesChart', $chart->resellerSales($user->id));
    }

    /**
     * Download PDF daftar harga produk.
     * - Admin/Editor/Developer: 2 kolom (harga normal + harga reseller)
     * - Reseller: 1 kolom (harga reseller setelah diskon)
     * - Customer/Affiliator: 1 kolom (harga normal)
     */
    public function downloadPrices(Request $request)
    {
        $user = $request->user();
        $products = Product::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('product_nama')
            ->get();

        $isAdmin = in_array($user->role, ['admin', 'editor', 'developer']);
        $isReseller = $user->isReseller();

        $items = $products->map(function ($product) {
            $hargaNormal = (float) $product->product_harga;
            $resellerFee = $product->reseller_fee_percent ? (float) $product->reseller_fee_percent : 0;
            $hargaReseller = $hargaNormal * (1 - $resellerFee / 100);

            return [
                'nama' => $product->product_nama,
                'harga_normal' => $hargaNormal,
                'harga_reseller' => $hargaReseller,
                'reseller_fee' => $resellerFee,
            ];
        });

        $pdf = Pdf::loadView('pdf.product-prices', [
            'user' => $user,
            'items' => $items,
            'isAdmin' => $isAdmin,
            'isReseller' => $isReseller,
            'date' => Carbon::now()->format('d M Y'),
        ]);

        $filename = 'daftar-harga-'.Carbon::now()->format('Y-m-d').'.pdf';

        return $pdf->download($filename);
    }
}
