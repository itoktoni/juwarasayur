<?php

namespace App\Http\Controllers;

use App\Charts\DashboardChart;
use App\Enums\UserTypeEnum;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Modules\Catalog\Models\Product;
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
     * ringkasan penjualan, barang yang harus di-prepare, dll.
     */
    private function adminDashboard(DashboardChart $chart)
    {
        $preparedStatuses = [SoStatusEnum::PAID, SoStatusEnum::CONFIRMED];
        $paidStatuses = [
            SoStatusEnum::PAID,
            SoStatusEnum::CONFIRMED,
            SoStatusEnum::SHIPPED,
            SoStatusEnum::DELIVERED,
        ];

        $stats = [
            'total_orders' => So::count(),
            'revenue' => (float) So::whereNotIn('so_status', [SoStatusEnum::CANCELLED])->sum('so_grand_total'),
            'to_prepare' => So::whereIn('so_status', $preparedStatuses)->count(),
            'unpaid' => So::where('so_status', SoStatusEnum::PENDING)->count(),
            'total_customers' => User::where('type', UserTypeEnum::CUSTOMER)->count(),
            'total_resellers' => User::where('type', UserTypeEnum::RESELLER)->count(),
            'total_products' => Product::count(),
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

        return view('dashboard.admin', compact('stats', 'toPrepare', 'recentOrders'))
            ->with('salesChart', $chart->salesRevenue())
            ->with('statusChart', $chart->orderStatusBreakdown());
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
}
