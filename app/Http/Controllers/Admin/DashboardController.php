<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\Table;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // Hitung total terjual per menu dari semua order valid
        $allOrders = \App\Models\Order::whereNotIn('status', ['pending', 'cancelled'])->get();
        $salesCount = [];
        foreach ($allOrders as $ord) {
            if (is_array($ord->items)) {
                foreach ($ord->items as $item) {
                    $mid = $item['id'] ?? null;
                    if ($mid) {
                        $salesCount[$mid] = ($salesCount[$mid] ?? 0) + ($item['qty'] ?? 1);
                    }
                }
            }
        }

        $menus = Menu::with('category')->orderBy('sort_order')->get()->map(function ($menu) use ($salesCount) {
            $menu->sold = $salesCount[$menu->id] ?? 0;
            return $menu;
        });
        $tables = Table::orderBy('number')->get();
        $categories = \App\Models\Category::orderBy('sort_order')->get();
        
        $today = \Carbon\Carbon::today();

        // Orders that are paid or further along (exclude pending/cancelled)
        $validOrders = \App\Models\Order::whereNotIn('status', ['pending', 'cancelled'])->orderBy('created_at', 'desc')->get();
        
        // Formatted Orders for the frontend
        $orders = $validOrders->map(function($order) {
            return [
                'id'            => $order->order_id,
                'table'         => $order->table_number,
                'items_count'   => is_array($order->items) ? count($order->items) : 0,
                'items_detail'  => is_array($order->items) ? $order->items : [],
                'total'         => $order->total,
                'subtotal'      => $order->subtotal,
                'tax'           => $order->tax,
                'customer_name' => $order->customer_name,
                'payment_method'=> $order->payment_method,
                'status'        => $order->status,
                'time'          => $order->created_at->diffForHumans(),
                'date_formatted'=> $order->created_at->format('d M Y, H:i')
            ];
        });

        // 1. STATS
        $todayOrders = $validOrders->filter(function($o) use ($today) {
            return $o->created_at->isSameDay($today);
        });
        
        $totalOrderHariIni = $todayOrders->count();
        $pendapatanHariIni = $todayOrders->sum('total');
        
        $pesananAktif = $validOrders->filter(function($o) {
            return in_array($o->status, ['paid', 'making', 'ready']);
        })->count();

        $stats = [
            'total_order' => $totalOrderHariIni,
            'pendapatan'  => $pendapatanHariIni,
            'aktif'       => $pesananAktif,
        ];

        // 2. CHART: Penjualan 7 Hari Terakhir (Minggu)
        $salesData = [];
        $salesLabels = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = \Carbon\Carbon::today()->subDays($i);
            $salesLabels[] = $date->isoFormat('ddd');
            
            $dayTotal = $validOrders->filter(function($o) use ($date) {
                return $o->created_at->isSameDay($date);
            })->sum('total');
            
            $salesData[] = $dayTotal / 1000000; // in millions for the chart
        }

        // 2b. CHART: Penjualan 30 Hari Terakhir (Bulan)
        $salesDataMonth = [];
        $salesLabelsMonth = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = \Carbon\Carbon::today()->subDays($i);
            // Show label only every few days to keep chart readable
            $salesLabelsMonth[] = $date->format('d/m');
            
            $dayTotal = $validOrders->filter(function($o) use ($date) {
                return $o->created_at->isSameDay($date);
            })->sum('total');
            
            $salesDataMonth[] = $dayTotal / 1000000; // in millions
        }


        // 3. CHART: Kunjungan (Jam) Hari Ini
        $visitData = array_fill(0, 8, 0); // 08:00, 10:00, 12:00, 14:00, 16:00, 18:00, 20:00, 22:00
        $visitLabels = ['08:00', '10:00', '12:00', '14:00', '16:00', '18:00', '20:00', '22:00'];
        foreach ($todayOrders as $order) {
            $hour = $order->created_at->hour;
            if ($hour >= 8 && $hour < 10) $visitData[0]++;
            elseif ($hour >= 10 && $hour < 12) $visitData[1]++;
            elseif ($hour >= 12 && $hour < 14) $visitData[2]++;
            elseif ($hour >= 14 && $hour < 16) $visitData[3]++;
            elseif ($hour >= 16 && $hour < 18) $visitData[4]++;
            elseif ($hour >= 18 && $hour < 20) $visitData[5]++;
            elseif ($hour >= 20 && $hour < 22) $visitData[6]++;
            elseif ($hour >= 22) $visitData[7]++;
        }

        // 4. CHART: Kategori & Top Menus
        $categorySales = [];
        $menuSales = [];
        foreach ($validOrders as $order) {
            if (is_array($order->items)) {
                foreach ($order->items as $item) {
                    // Count by menu name
                    $name = $item['name'];
                    if (!isset($menuSales[$name])) $menuSales[$name] = 0;
                    $menuSales[$name] += $item['qty'];

                    // We don't have direct category_id in items, we match by name temporarily
                    // A better way is to attach category info in items JSON, but we can search it
                }
            }
        }
        
        // Sort top menus
        arsort($menuSales);
        $topMenus = collect($menuSales)->take(4)->map(function($qty, $name) use ($validOrders) {
            // Rough percentage of total items
            $totalItems = 0;
            foreach ($validOrders as $o) {
                if(is_array($o->items)) {
                    foreach($o->items as $i) $totalItems += $i['qty'];
                }
            }
            $pct = $totalItems > 0 ? round(($qty / $totalItems) * 100) : 0;
            return [
                'name' => $name,
                'pct' => $pct,
                'emoji' => '☕' // Default emoji
            ];
        })->values()->toArray();

        // Dummy Category data until we store category_id in order items
        $categoryChart = [
            'labels' => ['Kopi Panas', 'Kopi Dingin', 'Non Kopi', 'Makanan'],
            'data'   => [35, 45, 10, 10]
        ];

        return view('admin.dashboard', compact(
            'menus', 'categories', 'orders', 'stats', 'salesLabels', 'salesData',
            'salesLabelsMonth', 'salesDataMonth',
            'visitLabels', 'visitData', 'topMenus', 'categoryChart', 'tables'
        ));

    }

    public function updateOrderStatus(Request $request, $id)
    {
        $order = \App\Models\Order::where('order_id', $id)->firstOrFail();
        
        $request->validate([
            'status' => 'required|in:making,ready,done',
        ]);

        $order->status = $request->status;
        $order->save();

        return response()->json([
            'success' => true,
            'message' => 'Status pesanan diperbarui menjadi ' . $order->status,
            'status'  => $order->status
        ]);
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:6|confirmed',
        ]);

        $user = auth('admin')->user();

        if (!\Illuminate\Support\Facades\Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Kata sandi saat ini tidak cocok.'
            ], 400);
        }

        $user->password = \Illuminate\Support\Facades\Hash::make($request->new_password);
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Kata sandi berhasil diubah.'
        ]);
    }
}
