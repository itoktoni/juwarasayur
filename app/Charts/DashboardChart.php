<?php

namespace App\Charts;

use App\Models\Notification;
use App\Models\User;
use ArielMejiaDev\LarapexCharts\LarapexChart;
use Carbon\Carbon;
use Modules\So\Models\So;

class DashboardChart
{
    /**
     * User registrations over the last 7 days.
     */
    public function userRegistrations(): LarapexChart
    {
        $days = collect(range(6, 0))->map(function ($i) {
            $date = Carbon::today()->subDays($i);

            return [
                'label' => $date->format('d M'),
                'count' => User::whereDate('created_at', $date)->count(),
            ];
        });

        return (new LarapexChart)->areaChart()
            ->setTitle('User Registrations')
            ->setSubtitle('New users — last 7 days')
            ->addData($days->pluck('count')->toArray())
            ->setXAxis($days->pluck('label')->toArray())
            ->setColors(['#3755c3'])
            ->setGrid()
            ->setMarkers(['#3755c3'], 4, 6);
    }

    /**
     * Revenue per day over the last 7 days (all sales orders).
     */
    public function salesRevenue(): LarapexChart
    {
        $days = collect(range(6, 0))->map(function ($i) {
            $date = Carbon::today()->subDays($i);

            $total = (float) So::whereDate('so_tanggal', $date)
                ->whereNotIn('so_status', ['cancelled'])
                ->sum('so_grand_total');

            return [
                'label' => $date->format('d M'),
                'total' => $total,
            ];
        });

        return (new LarapexChart)->areaChart()
            ->setTitle('Pendapatan Penjualan')
            ->setSubtitle('7 hari terakhir')
            ->addData($days->pluck('total')->toArray())
            ->setXAxis($days->pluck('label')->toArray())
            ->setColors(['#3755c3'])
            ->setGrid()
            ->setMarkers(['#3755c3'], 4, 6);
    }

    /**
     * Order count grouped by status (donut).
     */
    public function orderStatusBreakdown(): LarapexChart
    {
        $statuses = ['pending', 'paid', 'confirmed', 'shipped', 'delivered', 'cancelled'];
        $labels = ['Pending', 'Dibayar', 'Confirmed', 'Dikirim', 'Diterima', 'Cancelled'];
        $colors = ['#d97706', '#2563eb', '#7c3aed', '#0891b2', '#16a34a', '#dc2626'];

        $data = collect($statuses)->map(fn ($s) => So::where('so_status', $s)->count())->toArray();

        return (new LarapexChart)->donutChart()
            ->setTitle('Status Pesanan')
            ->setSubtitle('Total per status')
            ->addData($data)
            ->setLabels($labels)
            ->setColors($colors);
    }

    /**
     * Revenue per day over the last 7 days for a specific reseller.
     */
    public function resellerSales(int $resellerId): LarapexChart
    {
        $days = collect(range(6, 0))->map(function ($i) use ($resellerId) {
            $date = Carbon::today()->subDays($i);

            $total = (float) So::where('so_id_reseller', $resellerId)
                ->whereDate('so_tanggal', $date)
                ->whereNotIn('so_status', ['cancelled'])
                ->sum('so_grand_total');

            return [
                'label' => $date->format('d M'),
                'total' => $total,
            ];
        });

        return (new LarapexChart)->areaChart()
            ->setTitle('Penjualan Saya')
            ->setSubtitle('7 hari terakhir')
            ->addData($days->pluck('total')->toArray())
            ->setXAxis($days->pluck('label')->toArray())
            ->setColors(['#16a34a'])
            ->setGrid()
            ->setMarkers(['#16a34a'], 4, 6);
    }

    /**
     * Notifications: read vs unread.
     */
    public function notificationStats(): LarapexChart
    {
        $read = Notification::where('read', true)->count();
        $unread = Notification::where('read', false)->count();

        return (new LarapexChart)->donutChart()
            ->setTitle('Notifications')
            ->setSubtitle('Read / Unread')
            ->addData([$read, $unread])
            ->setLabels(['Read', 'Unread'])
            ->setColors(['#16a34a', '#d97706']);
    }
}
