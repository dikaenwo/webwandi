<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    /**
     * Return JSON analytics data for the given period.
     */
    public function data(Request $request)
    {
        $range   = $request->get('range', '7');   // 7, 30, 90, 365, custom
        $dateFrom = $request->get('date_from');
        $dateTo   = $request->get('date_to');

        [$from, $to, $labels, $groupBy] = $this->resolveRange($range, $dateFrom, $dateTo);

        $orders = Order::whereNotIn('status', ['pending', 'cancelled'])
            ->whereBetween('created_at', [$from->startOfDay()->copy(), $to->endOfDay()->copy()])
            ->get();

        // ── Sales chart ──
        $salesMap  = [];
        $ordersMap = [];
        foreach ($labels as $label) {
            $salesMap[$label]  = 0;
            $ordersMap[$label] = 0;
        }

        foreach ($orders as $order) {
            $key = $this->labelKey($order->created_at, $groupBy);
            if (array_key_exists($key, $salesMap)) {
                $salesMap[$key]  += $order->total;
                $ordersMap[$key] += 1;
            }
        }

        // ── Summary stats ──
        $totalRevenue   = $orders->sum('total');
        $totalOrders    = $orders->count();
        $avgOrderValue  = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;
        $totalItems     = 0;
        $menuSales      = [];
        $hourMap        = array_fill(0, 24, 0);

        foreach ($orders as $order) {
            $hourMap[$order->created_at->hour]++;
            if (is_array($order->items)) {
                foreach ($order->items as $item) {
                    $totalItems += $item['qty'];
                    $name = $item['name'];
                    if (!isset($menuSales[$name])) $menuSales[$name] = ['qty' => 0, 'revenue' => 0];
                    $menuSales[$name]['qty']     += $item['qty'];
                    $menuSales[$name]['revenue'] += ($item['price'] ?? 0) * $item['qty'];
                }
            }
        }

        // Top menus
        uasort($menuSales, fn($a, $b) => $b['qty'] - $a['qty']);
        $topMenus = collect($menuSales)->take(10)->map(fn($v, $k) => [
            'name'    => $k,
            'qty'     => $v['qty'],
            'revenue' => $v['revenue'],
        ])->values();

        // Peak hours
        $peakHoursData   = [];
        $peakHoursLabels = [];
        for ($h = 7; $h <= 23; $h++) {
            $peakHoursLabels[] = sprintf('%02d:00', $h);
            $peakHoursData[]   = $hourMap[$h];
        }

        // Daily breakdown for table
        $dailyBreakdown = [];
        $keys = array_keys($salesMap);
        foreach ($keys as $key) {
            $dailyBreakdown[] = [
                'period'   => $key,
                'revenue'  => $salesMap[$key],
                'orders'   => $ordersMap[$key],
            ];
        }

        return response()->json([
            'summary' => [
                'total_revenue'   => $totalRevenue,
                'total_orders'    => $totalOrders,
                'avg_order_value' => round($avgOrderValue),
                'total_items'     => $totalItems,
            ],
            'chart' => [
                'labels'     => array_values($labels),
                'revenue'    => array_values($salesMap),
                'orders'     => array_values($ordersMap),
            ],
            'top_menus'          => $topMenus,
            'peak_hours'         => [
                'labels' => $peakHoursLabels,
                'data'   => $peakHoursData,
            ],
            'daily_breakdown'    => $dailyBreakdown,
        ]);
    }

    /**
     * Export as CSV (Excel-compatible)
     */
    public function exportCsv(Request $request)
    {
        $range   = $request->get('range', '7');
        $dateFrom = $request->get('date_from');
        $dateTo   = $request->get('date_to');

        [$from, $to] = $this->resolveRange($range, $dateFrom, $dateTo);

        $orders = Order::whereNotIn('status', ['pending', 'cancelled'])
            ->whereBetween('created_at', [$from->startOfDay()->copy(), $to->endOfDay()->copy()])
            ->orderBy('created_at', 'desc')
            ->get();

        $filename = 'laporan-skena-coffee-' . $from->format('Ymd') . '-' . $to->format('Ymd') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($orders) {
            $file = fopen('php://output', 'w');
            // BOM for Excel UTF-8
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Header row
            fputcsv($file, [
                'Order ID', 'Tanggal', 'Nama Customer', 'Meja',
                'Item', 'Subtotal', 'Pajak (10%)', 'Total',
                'Metode Bayar', 'Status'
            ]);

            foreach ($orders as $order) {
                $itemNames = '';
                if (is_array($order->items)) {
                    $itemNames = collect($order->items)->map(fn($i) => $i['qty'] . 'x ' . $i['name'])->implode(', ');
                }
                fputcsv($file, [
                    $order->order_id,
                    $order->created_at->format('d/m/Y H:i'),
                    $order->customer_name ?? '-',
                    'Meja ' . $order->table_number,
                    $itemNames,
                    $order->subtotal,
                    $order->tax,
                    $order->total,
                    strtoupper($order->payment_method ?? 'QRIS'),
                    ucfirst($order->status),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export as PDF (pure HTML → browser print)
     */
    public function exportPdf(Request $request)
    {
        $range   = $request->get('range', '7');
        $dateFrom = $request->get('date_from');
        $dateTo   = $request->get('date_to');

        [$from, $to] = $this->resolveRange($range, $dateFrom, $dateTo);

        $orders = Order::whereNotIn('status', ['pending', 'cancelled'])
            ->whereBetween('created_at', [$from->startOfDay()->copy(), $to->endOfDay()->copy()])
            ->orderBy('created_at', 'desc')
            ->get();

        $totalRevenue  = $orders->sum('total');
        $totalOrders   = $orders->count();
        $avgOrderValue = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;

        return view('admin.analytics-pdf', compact(
            'orders', 'totalRevenue', 'totalOrders', 'avgOrderValue', 'from', 'to', 'range'
        ));
    }

    // ─── Helpers ──────────────────────────────────────────────────────────

    private function resolveRange($range, $dateFrom, $dateTo): array
    {
        $to   = Carbon::today();
        $from = match ($range) {
            '7'   => Carbon::today()->subDays(6),
            '30'  => Carbon::today()->subDays(29),
            '90'  => Carbon::today()->subDays(89),
            '365' => Carbon::today()->subDays(364),
            'custom' => Carbon::parse($dateFrom ?? today()),
            default  => Carbon::today()->subDays(6),
        };
        if ($range === 'custom' && $dateTo) {
            $to = Carbon::parse($dateTo);
        }

        $diffDays = $from->diffInDays($to) + 1;

        if ($diffDays <= 31) {
            $groupBy = 'day';
            $labels  = [];
            for ($i = 0; $i < $diffDays; $i++) {
                $labels[] = $from->copy()->addDays($i)->format('d/m');
            }
        } elseif ($diffDays <= 92) {
            $groupBy = 'week';
            $labels  = [];
            $cursor  = $from->copy()->startOfWeek();
            while ($cursor <= $to) {
                $labels[] = 'Mg ' . $cursor->format('d/m');
                $cursor->addWeek();
            }
        } else {
            $groupBy = 'month';
            $labels  = [];
            $cursor  = $from->copy()->startOfMonth();
            while ($cursor <= $to) {
                $labels[] = $cursor->format('M Y');
                $cursor->addMonth();
            }
        }

        return [$from, $to, $labels, $groupBy];
    }

    private function labelKey(Carbon $date, string $groupBy): string
    {
        return match ($groupBy) {
            'day'   => $date->format('d/m'),
            'week'  => 'Mg ' . $date->copy()->startOfWeek()->format('d/m'),
            'month' => $date->format('M Y'),
        };
    }
}
