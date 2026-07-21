<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OwnerController extends Controller
{
    public function index()
    {
        $user  = Auth::guard('admin')->user();
        $today = Carbon::today();

        $validOrders = Order::whereNotIn('status', ['pending', 'cancelled'])
            ->orderBy('created_at', 'desc')
            ->get();

        // ── Hari ini ──
        $todayOrders = $validOrders->filter(fn($o) => $o->created_at->isSameDay($today));

        $pendapatanHariIni    = $todayOrders->sum('total');
        $totalOrderHariIni    = $todayOrders->count();
        $pendapatanKemarin    = $validOrders->filter(fn($o) => $o->created_at->isSameDay($today->copy()->subDay()))->sum('total');
        $growthPct            = $pendapatanKemarin > 0
            ? round((($pendapatanHariIni - $pendapatanKemarin) / $pendapatanKemarin) * 100, 1)
            : ($pendapatanHariIni > 0 ? 100 : 0);

        $avgOrderValue = $totalOrderHariIni > 0 ? round($pendapatanHariIni / $totalOrderHariIni) : 0;

        // Total items hari ini
        $totalItemsToday = 0;
        foreach ($todayOrders as $order) {
            if (is_array($order->items)) {
                foreach ($order->items as $item) $totalItemsToday += $item['qty'];
            }
        }

        // ── Stats 30 hari ──
        $month30 = $validOrders->filter(fn($o) => $o->created_at->gte($today->copy()->subDays(29)));
        $pendapatan30 = $month30->sum('total');
        $order30      = $month30->count();

        $stats = [
            'pendapatan_hari_ini' => $pendapatanHariIni,
            'total_order_hari_ini' => $totalOrderHariIni,
            'avg_order_value'     => $avgOrderValue,
            'total_items_today'   => $totalItemsToday,
            'growth_pct'          => $growthPct,
            'pendapatan_30'       => $pendapatan30,
            'order_30'            => $order30,
        ];

        // ── Grafik 7 hari ──
        $salesLabels = [];
        $salesData   = [];
        for ($i = 6; $i >= 0; $i--) {
            $date          = Carbon::today()->subDays($i);
            $salesLabels[] = $date->isoFormat('ddd, D MMM');
            $salesData[]   = $validOrders->filter(fn($o) => $o->created_at->isSameDay($date))->sum('total');
        }

        // ── Top menus (30 hari) ──
        $menuSales = [];
        foreach ($month30 as $order) {
            if (is_array($order->items)) {
                foreach ($order->items as $item) {
                    $name = $item['name'];
                    if (!isset($menuSales[$name])) $menuSales[$name] = ['qty' => 0, 'revenue' => 0];
                    $menuSales[$name]['qty']     += $item['qty'];
                    $menuSales[$name]['revenue'] += ($item['price'] ?? 0) * $item['qty'];
                }
            }
        }
        uasort($menuSales, fn($a, $b) => $b['qty'] - $a['qty']);
        $topMenus = collect($menuSales)->take(8)->map(fn($v, $k) => [
            'name'    => $k,
            'qty'     => $v['qty'],
            'revenue' => $v['revenue'],
        ])->values();

        // ── Jam sibuk (7 hari) ──
        $week7 = $validOrders->filter(fn($o) => $o->created_at->gte($today->copy()->subDays(6)));
        $hourMap        = array_fill(0, 24, 0);
        foreach ($week7 as $order) $hourMap[$order->created_at->hour]++;
        $peakHoursLabels = [];
        $peakHoursData   = [];
        for ($h = 7; $h <= 23; $h++) {
            $peakHoursLabels[] = sprintf('%02d:00', $h);
            $peakHoursData[]   = $hourMap[$h];
        }

        // ── Recent orders (view only) ──
        $recentOrders = $validOrders->take(50)->map(fn($order) => [
            'id'             => $order->order_id,
            'table'          => $order->table_number,
            'items_count'    => is_array($order->items) ? count($order->items) : 0,
            'total'          => $order->total,
            'status'         => $order->status,
            'time'           => $order->created_at->diffForHumans(),
            'date_formatted' => $order->created_at->format('d M Y, H:i'),
        ]);

        return view('owner.dashboard', compact(
            'user', 'stats',
            'salesLabels', 'salesData',
            'topMenus',
            'peakHoursLabels', 'peakHoursData',
            'recentOrders'
        ));
    }

    /**
     * Analytics data API (same as AnalyticsController but accessible to owner)
     */
    public function analyticsData(Request $request)
    {
        return app(\App\Http\Controllers\Admin\AnalyticsController::class)->data($request);
    }

    public function exportCsv(Request $request)
    {
        return app(\App\Http\Controllers\Admin\AnalyticsController::class)->exportCsv($request);
    }

    public function exportPdf(Request $request)
    {
        return app(\App\Http\Controllers\Admin\AnalyticsController::class)->exportPdf($request);
    }
}
