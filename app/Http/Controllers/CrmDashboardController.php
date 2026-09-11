<?php

namespace App\Http\Controllers;

use App\Charts\DashboardChart;
use App\Enums\UserTypeEnum;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Modules\So\Enums\SoStatusEnum;
use Modules\So\Models\So;

class CrmDashboardController extends Controller
{
    public function __invoke(Request $request, DashboardChart $chart)
    {
        $today = Carbon::today();
        $since7 = $today->copy()->subDays(7);
        $since14 = $today->copy()->subDays(14);
        $since30 = $today->copy()->subDays(30);
        $since90 = $today->copy()->subDays(90);

        // === UNPAID (order tapi gak bayar) ===
        $pendingQ = So::where('so_status', SoStatusEnum::PENDING);
        $unpaidCount = (clone $pendingQ)->count();
        $unpaidSum = (float) (clone $pendingQ)->sum('so_grand_total');
        // distinct orang (pakai so_id_customer jika ada, fallback phone+name)
        $pendingRows = (clone $pendingQ)->get(['so_id_customer', 'so_customer_phone', 'so_customer_name']);
        $unpaidPeople = $pendingRows->map(fn ($r) => $r->so_id_customer ? 'id:'.$r->so_id_customer : 'phone:'.($r->so_customer_phone ?: $r->so_customer_name))->unique()->count();

        // Aging buckets
        $aging = [
            '0-1d' => (clone $pendingQ)->whereDate('created_at', '>=', $today->copy()->subDay())->count(),
            '1-3d' => (clone $pendingQ)->whereDate('created_at', '>=', $today->copy()->subDays(3))->whereDate('created_at', '<', $today->copy()->subDay())->count(),
            '3-7d' => (clone $pendingQ)->whereDate('created_at', '>=', $since7)->whereDate('created_at', '<', $today->copy()->subDays(3))->count(),
            '>7d' => (clone $pendingQ)->whereDate('created_at', '<', $since7)->count(),
        ];

        // List unpaid terlama
        $unpaidList = So::with(['has_customer', 'has_reseller'])
            ->where('so_status', SoStatusEnum::PENDING)
            ->orderBy('created_at')
            ->limit(15)
            ->get();

        // === FREKUENSI BELANJA (dalam 7 hari terakhir, exclude cancel) ===
        $freqRows = So::whereDate('so_tanggal', '>=', $since7)
            ->whereNotIn('so_status', [SoStatusEnum::CANCELLED])
            ->selectRaw('COALESCE(CAST(so_id_customer AS CHAR), so_customer_phone, so_customer_name) as cust_key, so_customer_name, so_id_customer, COUNT(*) as cnt, SUM(so_grand_total) as total')
            ->groupBy('cust_key', 'so_customer_name', 'so_id_customer')
            ->orderByDesc('cnt')
            ->get();

        $freqOnce = $freqRows->where('cnt', 1)->count();
        $freqTwice = $freqRows->where('cnt', 2)->count();
        $freqMulti = $freqRows->where('cnt', '>', 2)->count();
        $active7 = $freqRows->count(); // customer aktif 7 hari

        // Customer yang hanya 1x seminggu (cnt==1)
        $onceList = $freqRows->where('cnt', 1)->take(15);

        // === CHURN / jarang beli ===
        // Customer yang pernah order 7-30 hari lalu tapi tidak di 7 hari terakhir
        $customersLast30 = So::whereDate('so_tanggal', '>=', $since30)->whereNotIn('so_status', [SoStatusEnum::CANCELLED])
            ->selectRaw('COALESCE(CAST(so_id_customer AS CHAR), so_customer_phone) as k')->distinct()->pluck('k');
        $customersLast7 = So::whereDate('so_tanggal', '>=', $since7)->whereNotIn('so_status', [SoStatusEnum::CANCELLED])
            ->selectRaw('COALESCE(CAST(so_id_customer AS CHAR), so_customer_phone) as k')->distinct()->pluck('k');
        $churnRiskKeys = $customersLast30->diff($customersLast7);
        $churnCount = $churnRiskKeys->count();

        // Dormant 30 hari+: punya order 30-90 hari lalu tapi tidak 30 hari terakhir
        $customersLast90 = So::whereDate('so_tanggal', '>=', $since90)->whereDate('so_tanggal', '<', $since30)
            ->whereNotIn('so_status', [SoStatusEnum::CANCELLED])
            ->selectRaw('COALESCE(CAST(so_id_customer AS CHAR), so_customer_phone) as k')->distinct()->pluck('k');
        $customersActive30 = So::whereDate('so_tanggal', '>=', $since30)->whereNotIn('so_status', [SoStatusEnum::CANCELLED])
            ->selectRaw('COALESCE(CAST(so_id_customer AS CHAR), so_customer_phone) as k')->distinct()->pluck('k');
        $dormant30 = $customersLast90->diff($customersActive30)->count();

        // Total customer terdaftar vs pernah order
        $totalCustomers = User::where('type', UserTypeEnum::CUSTOMER)->count();
        $everOrderedKeys = So::whereNotIn('so_status', [SoStatusEnum::CANCELLED])
            ->selectRaw('COALESCE(CAST(so_id_customer AS CHAR), so_customer_phone) as k')->distinct()->pluck('k')->count();

        // Top customers by order count 30 hari
        $top30 = So::whereDate('so_tanggal', '>=', $since30)->whereNotIn('so_status', [SoStatusEnum::CANCELLED])
            ->selectRaw('COALESCE(CAST(so_id_customer AS CHAR), so_customer_phone) as k, so_customer_name, so_id_customer, COUNT(*) as cnt, SUM(so_grand_total) as total')
            ->groupBy('k', 'so_customer_name', 'so_id_customer')
            ->orderByDesc('cnt')
            ->limit(10)
            ->get();

        // Repeat rate: pelanggan dengan >=2 order dalam 30 hari
        $repeat30 = So::whereDate('so_tanggal', '>=', $since30)->whereNotIn('so_status', [SoStatusEnum::CANCELLED])
            ->selectRaw('COALESCE(CAST(so_id_customer AS CHAR), so_customer_phone) as k')->groupBy('k')->havingRaw('COUNT(*) >= 2')->get()->count();
        $repeatRate = $customersLast30->count() > 0 ? round($repeat30 / $customersLast30->count() * 100, 1) : 0;

        $stats = compact('unpaidCount', 'unpaidSum', 'unpaidPeople', 'aging', 'active7', 'freqOnce', 'freqTwice', 'freqMulti', 'churnCount', 'dormant30', 'totalCustomers', 'everOrderedKeys', 'repeatRate');

        return view('dashboard.crm', compact('stats', 'unpaidList', 'onceList', 'top30', 'freqRows', 'since7', 'since30'))
            ->with('unpaidChart', $chart->crmUnpaidAging())
            ->with('freqChart', $chart->crmFrequency());
    }
}
