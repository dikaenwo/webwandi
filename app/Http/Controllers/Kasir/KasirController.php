<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Menu;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KasirController extends Controller
{
    public function index()
    {
        $today = Carbon::today();

        // Orders that are paid or further along (exclude pending/cancelled)
        $validOrders = Order::whereNotIn('status', ['pending', 'cancelled'])->orderBy('created_at', 'desc')->get();

        // Formatted Orders for the frontend
        $orders = $validOrders->map(function ($order) {
            return [
                'id'             => $order->order_id,
                'table'          => $order->table_number,
                'items_count'    => is_array($order->items) ? count($order->items) : 0,
                'items_detail'   => is_array($order->items) ? $order->items : [],
                'total'          => $order->total,
                'subtotal'       => $order->subtotal,
                'tax'            => $order->tax,
                'customer_name'  => $order->customer_name,
                'payment_method' => $order->payment_method,
                'status'         => $order->status,
                'time'           => $order->created_at->diffForHumans(),
                'date_formatted' => $order->created_at->format('d M Y, H:i'),
            ];
        });

        // STATS
        $todayOrders = $validOrders->filter(function ($o) use ($today) {
            return $o->created_at->isSameDay($today);
        });

        $totalOrderHariIni = $todayOrders->count();
        $pendapatanHariIni = $todayOrders->sum('total');

        $pesananAktif = $validOrders->filter(function ($o) {
            return in_array($o->status, ['paid', 'making', 'ready']);
        })->count();

        $avgOrderValue = $totalOrderHariIni > 0 ? round($pendapatanHariIni / $totalOrderHariIni) : 0;

        $stats = [
            'total_order' => $totalOrderHariIni,
            'pendapatan'  => $pendapatanHariIni,
            'aktif'       => $pesananAktif,
            'avg_order'   => $avgOrderValue,
        ];

        // CHART: Penjualan 7 Hari Terakhir
        $salesData   = [];
        $salesLabels = [];
        for ($i = 6; $i >= 0; $i--) {
            $date          = Carbon::today()->subDays($i);
            $salesLabels[] = $date->isoFormat('ddd');
            $dayTotal = $validOrders->filter(function ($o) use ($date) {
                return $o->created_at->isSameDay($date);
            })->sum('total');
            $salesData[] = $dayTotal / 1000000; // in millions for the chart
        }

        // Top Menu (all time)
        $menuSales = [];
        foreach ($validOrders as $order) {
            if (is_array($order->items)) {
                foreach ($order->items as $item) {
                    $name = $item['name'];
                    if (!isset($menuSales[$name])) $menuSales[$name] = 0;
                    $menuSales[$name] += $item['qty'];
                }
            }
        }
        arsort($menuSales);
        $topMenus = collect($menuSales)->take(4)->map(function ($qty, $name) use ($validOrders) {
            $totalItems = 0;
            foreach ($validOrders as $o) {
                if (is_array($o->items)) {
                    foreach ($o->items as $i) $totalItems += $i['qty'];
                }
            }
            $pct = $totalItems > 0 ? round(($qty / $totalItems) * 100) : 0;
            return [
                'name' => $name,
                'pct'  => $pct,
            ];
        })->values()->toArray();

        $user = Auth::guard('admin')->user();

        return view('kasir.dashboard', compact(
            'user', 'orders', 'stats', 'salesLabels', 'salesData', 'topMenus'
        ));
    }

    public function updateOrderStatus(Request $request, $id)
    {
        $order = Order::where('order_id', $id)->firstOrFail();

        $request->validate([
            'status' => 'required|in:making,ready,done',
        ]);

        $order->status = $request->status;
        $order->save();

        return response()->json([
            'success' => true,
            'message' => 'Status pesanan diperbarui menjadi ' . $order->status,
            'status'  => $order->status,
        ]);
    }

    /**
     * Analytics data API for kasir reports
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
