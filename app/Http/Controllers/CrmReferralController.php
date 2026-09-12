<?php

namespace App\Http\Controllers;

use App\Charts\DashboardChart;
use App\Enums\UserTypeEnum;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\So\Enums\SoStatusEnum;
use Modules\So\Models\So;

class CrmReferralController extends Controller
{
    public function __invoke(Request $request, DashboardChart $chart)
    {
        $hasHits = Schema::hasTable('referral_hits');

        $range = $request->input('range', '30'); // 7|30|90|all
        $since = match ($range) {
            '7' => Carbon::today()->subDays(7),
            '90' => Carbon::today()->subDays(90),
            'all' => null,
            default => Carbon::today()->subDays(30),
        };

        // Base query hits
        $hitsQ = $hasHits ? DB::table('referral_hits')->when($since, fn ($q) => $q->where('created_at', '>=', $since)) : null;

        $totalHits = $hasHits ? (clone $hitsQ)->count() : 0;
        $hitsToday = $hasHits ? DB::table('referral_hits')->whereDate('created_at', Carbon::today())->count() : 0;
        $hits7 = $hasHits ? DB::table('referral_hits')->where('created_at', '>=', Carbon::today()->subDays(7))->count() : 0;

        // Unique affiliators yang link-nya pernah dibuka
        $uniqueAffiliators = $hasHits ? (clone $hitsQ)->distinct()->count('affiliator_id') : 0;
        $totalAffiliators = User::where('type', UserTypeEnum::AFFILIATOR)->count();
        $withCode = User::where('type', UserTypeEnum::AFFILIATOR)->whereNotNull('referral_code')->count();

        // Konversi: registrasi via referral (users.reference_id not null) dalam range
        $regQ = User::whereNotNull('reference_id')->when($since, fn ($q) => $q->where('created_at', '>=', $since));
        $totalRegistrations = (clone $regQ)->count();
        // Order via referral (So.so_id_reseller yang merupakan affiliator)
        $orderQ = So::whereNotNull('so_id_reseller')->when($since, fn ($q) => $q->where('so_tanggal', '>=', $since));
        $totalOrders = (clone $orderQ)->count();
        // Komisi real = fee_amount, bukan omzet
        $orderRevenue = (float) \Modules\So\Models\SoDetail::whereHas('has_so', function ($q) use ($since) {
            $q->whereNotNull('so_id_reseller')->whereNotIn('so_status', [SoStatusEnum::CANCELLED])->when($since, fn ($qq) => $qq->where('so_tanggal', '>=', $since));
        })->sum('fee_amount');

        $conversionReg = $totalHits > 0 ? round($totalRegistrations / $totalHits * 100, 2) : 0;
        $conversionOrder = $totalHits > 0 ? round($totalOrders / $totalHits * 100, 2) : 0;

        // Tren 14 hari terakhir untuk chart
        $trend = collect(range(13, 0))->map(function (int $i) use ($hasHits) {
            $day = Carbon::today()->subDays($i);
            $cnt = $hasHits ? DB::table('referral_hits')->whereDate('created_at', $day)->count() : 0;
            return ['label' => $day->format('d/m'), 'hits' => $cnt];
        });

        // Top affiliators by hits / customers / orders
        $topAffiliators = User::where('type', UserTypeEnum::AFFILIATOR)
            ->withCount([
                'hasCustomers as customers_count' => fn ($q) => $q->when($since, fn ($qq) => $qq->where('created_at', '>=', $since)),
            ])
            ->get()
            ->map(function (User $u) use ($hasHits, $since) {
                $hits = $hasHits ? DB::table('referral_hits')->where('affiliator_id', $u->id)->when($since, fn ($q) => $q->where('created_at', '>=', $since))->count() : 0;
                $orders = So::where('so_id_reseller', $u->id)->when($since, fn ($q) => $q->where('so_tanggal', '>=', $since))->count();
                $revenue = (float) \Modules\So\Models\SoDetail::whereHas('has_so', function ($q) use ($u, $since) {
                    $q->where('so_id_reseller', $u->id)->whereNotIn('so_status', [SoStatusEnum::CANCELLED])->when($since, fn ($qq) => $qq->where('so_tanggal', '>=', $since));
                })->sum('fee_amount');
                return [
                    'user' => $u,
                    'hits' => $hits,
                    'customers' => $u->customers_count,
                    'orders' => $orders,
                    'revenue' => $revenue,
                ];
            })
            ->sortByDesc('hits')
            ->take(15)
            ->values();

        // Recent hits
        $recentHits = $hasHits ? DB::table('referral_hits')
            ->leftJoin('users', 'users.id', '=', 'referral_hits.affiliator_id')
            ->select('referral_hits.*', 'users.name as affiliator_name')
            ->orderByDesc('referral_hits.created_at')
            ->limit(20)
            ->get() : collect();

        // Per-customer funnel: yang klik tapi belum order
        $affiliatorFilter = $request->input('affiliator_id');
        $filteredCustomers = null;
        if ($affiliatorFilter) {
            $filteredCustomers = User::where('type', UserTypeEnum::CUSTOMER)->where('reference_id', $affiliatorFilter)->orderByDesc('id')->limit(10)->get();
        }

        $stats = compact('totalHits', 'hitsToday', 'hits7', 'uniqueAffiliators', 'totalAffiliators', 'withCode', 'totalRegistrations', 'totalOrders', 'orderRevenue', 'conversionReg', 'conversionOrder');

        return view('dashboard.crm-referral', compact('stats', 'trend', 'topAffiliators', 'recentHits', 'range', 'since', 'affiliatorFilter', 'filteredCustomers'))
            ->with('hitsChart', $this->hitsChart($trend))
            ->with('funnelChart', $chart->crmFrequency()); // fallback kecil, atau pakai chart custom
    }

    private function hitsChart($trend)
    {
        $chart = new \ArielMejiaDev\LarapexCharts\LarapexChart;
        return $chart->lineChart()
            ->addData($trend->pluck('hits')->toArray(), 'Klik link')
            ->setXAxis($trend->pluck('label')->toArray())
            ->setColors(['#1976d2'])
            ->setGrid()
            ->setHeight(300)
            ->setOptions([
                'chart' => ['background' => '#ffffff', 'fontFamily' => 'inherit'],
                'grid' => ['borderColor' => '#e5e7eb', 'opacity' => 0.6],
            ]);
    }
}
