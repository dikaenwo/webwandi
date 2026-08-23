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
        $tables     = Table::orderBy('number')->get();
        $categories = \App\Models\Category::orderBy('sort_order')->get();
        $today      = \Carbon\Carbon::today();

        // ── Satu query untuk semua kalkulasi order ──────────────────────────
        $validOrders = \App\Models\Order::whereNotIn('status', ['pending', 'cancelled'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Agregasi sales per menu_id (untuk kolom Terjual) & per nama (untuk Top Menu chart)
        // Catatan: item ID bisa berformat "19-ice" atau "3-hot" — ekstrak angka saja
        $salesCount  = [];
        $menuSales   = [];
        $totalItems  = 0;
        foreach ($validOrders as $ord) {
            if (is_array($ord->items)) {
                foreach ($ord->items as $item) {
                    $qty   = $item['qty'] ?? 1;
                    $rawId = $item['id'] ?? null;
                    // Ekstrak numeric menu_id: "19-ice" → 19
                    if ($rawId !== null) {
                        $menuId = (int) explode('-', (string) $rawId)[0];
                        if ($menuId > 0) {
                            $salesCount[$menuId] = ($salesCount[$menuId] ?? 0) + $qty;
                        }
                    }
                    // by name → Top Menu chart
                    $menuSales[$item['name']] = ($menuSales[$item['name']] ?? 0) + $qty;
                    $totalItems += $qty;
                }
            }
        }

        // Inject field `sold` ke tiap menu
        $menus = Menu::with('category')->orderBy('sort_order')->get()->map(function ($menu) use ($salesCount) {
            $menu->sold = $salesCount[$menu->id] ?? 0;
            return $menu;
        });

        // Formatted Orders untuk frontend (hanya 50 terbaru agar JSON tidak besar)
        $orders = $validOrders->take(50)->map(function($order) {
            return [
                'id'            => $order->order_id,
                'table'         => $order->table_number,
                'items_count'   => is_array($order->items) ? count($order->items) : 0,
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
        $todayOrders      = $validOrders->filter(fn($o) => $o->created_at->isSameDay($today));
        $totalOrderHariIni = $todayOrders->count();
        $pendapatanHariIni = $todayOrders->sum('total');
        $pesananAktif      = $validOrders->filter(fn($o) => in_array($o->status, ['paid', 'making', 'ready']))->count();

        $stats = [
            'total_order' => $totalOrderHariIni,
            'pendapatan'  => $pendapatanHariIni,
            'aktif'       => $pesananAktif,
        ];

        // 2. CHART: Penjualan 7 Hari Terakhir
        $salesData = []; $salesLabels = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = \Carbon\Carbon::today()->subDays($i);
            $salesLabels[] = $date->isoFormat('ddd');
            $salesData[]   = $validOrders->filter(fn($o) => $o->created_at->isSameDay($date))->sum('total') / 1000000;
        }

        // 2b. CHART: Penjualan 30 Hari Terakhir
        $salesDataMonth = []; $salesLabelsMonth = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = \Carbon\Carbon::today()->subDays($i);
            $salesLabelsMonth[] = $date->format('d/m');
            $salesDataMonth[]   = $validOrders->filter(fn($o) => $o->created_at->isSameDay($date))->sum('total') / 1000000;
        }

        // 3. CHART: Kunjungan per Jam (7 hari terakhir agar selalu ada data)
        $visitData   = array_fill(0, 8, 0);
        $visitLabels = ['08:00', '10:00', '12:00', '14:00', '16:00', '18:00', '20:00', '22:00'];
        $last7Orders = $validOrders->filter(fn($o) => $o->created_at->gte(\Carbon\Carbon::today()->subDays(6)->startOfDay()));
        foreach ($last7Orders as $order) {
            $h = $order->created_at->hour;
            if ($h >= 8 && $h < 10)       $visitData[0]++;
            elseif ($h >= 10 && $h < 12)  $visitData[1]++;
            elseif ($h >= 12 && $h < 14)  $visitData[2]++;
            elseif ($h >= 14 && $h < 16)  $visitData[3]++;
            elseif ($h >= 16 && $h < 18)  $visitData[4]++;
            elseif ($h >= 18 && $h < 20)  $visitData[5]++;
            elseif ($h >= 20 && $h < 22)  $visitData[6]++;
            elseif ($h >= 22)             $visitData[7]++;
        }

        // 4. Top Menu Chart
        arsort($menuSales);
        $topMenus = collect($menuSales)->take(4)->map(function($qty, $name) use ($totalItems) {
            return ['name' => $name, 'pct' => $totalItems > 0 ? round(($qty / $totalItems) * 100) : 0, 'emoji' => '☕'];
        })->values()->toArray();

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

    /**
     * Realtime polling endpoint — dipanggil setiap ~20 detik oleh frontend.
     * Ringan: hanya hitung stats hari ini + 10 order terbaru.
     */
    public function live()
    {
        $today       = \Carbon\Carbon::today();
        $validOrders = \App\Models\Order::whereNotIn('status', ['pending', 'cancelled'])
            ->orderBy('created_at', 'desc')
            ->get();

        $todayOrders = $validOrders->filter(fn($o) => $o->created_at->isSameDay($today));

        $stats = [
            'total_order' => $todayOrders->count(),
            'pendapatan'  => $todayOrders->sum('total'),
            'aktif'       => $validOrders->filter(fn($o) => in_array($o->status, ['paid', 'making', 'ready']))->count(),
        ];

        $orders = $validOrders->take(10)->map(fn($order) => [
            'id'          => $order->order_id,
            'table'       => $order->table_number,
            'items_count' => is_array($order->items) ? count($order->items) : 0,
            'total'       => $order->total,
            'status'      => $order->status,
            'time'        => $order->created_at->diffForHumans(),
        ]);

        return response()->json([
            'stats'       => $stats,
            'orders'      => $orders,
            'server_time' => now()->format('H:i:s'),
        ]);
    }
}
