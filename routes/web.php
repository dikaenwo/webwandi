<?php

use Illuminate\Support\Facades\Route;
use App\Models\Menu;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Admin\AnalyticsController;
use App\Http\Controllers\Admin\TableController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\Kasir\KasirController;

/*
|--------------------------------------------------------------------------
| Skena Coffee — Web Routes
|--------------------------------------------------------------------------
*/

// =================== PUBLIC ROUTES ===================

// Home / Landing Page
Route::get('/', function () {
    // Hitung menu paling banyak terjual dari data order
    $validOrders = \App\Models\Order::whereNotIn('status', ['pending', 'cancelled'])->get();

    // Agregasi qty per menu_id
    // Catatan: item ID bisa berformat "19-ice" atau "3-hot" (dengan variant suffix)
    // Ekstrak angka saja: "19-ice" → 19
    $salesCount = [];
    foreach ($validOrders as $order) {
        if (is_array($order->items)) {
            foreach ($order->items as $item) {
                $rawId = $item['id'] ?? null;
                if ($rawId !== null) {
                    // Ambil bagian sebelum "-" (atau langsung angka jika tidak ada "-")
                    $menuId = (int) explode('-', (string) $rawId)[0];
                    if ($menuId > 0) {
                        $salesCount[$menuId] = ($salesCount[$menuId] ?? 0) + ($item['qty'] ?? 1);
                    }
                }
            }
        }
    }

    // Urutkan dari terbanyak, ambil SEMUA sold IDs dulu (belum dibatasi 4)
    arsort($salesCount);
    $allSoldIds = array_keys($salesCount);

    // Filter available dulu, baru ambil top 4
    // (mencegah item unavailable buang slot dan diganti filler dengan total_sold=0)
    $bestSellers = collect();
    if (!empty($allSoldIds)) {
        $soldMenusRaw = \App\Models\Menu::available()
            ->whereIn('id', $allSoldIds)
            ->get()
            ->keyBy('id');

        $bestSellers = collect($allSoldIds)
            ->map(fn($id) => $soldMenusRaw->get($id))
            ->filter()                        // buang yang tidak available
            ->map(function ($menu) use ($salesCount) {
                $menu->total_sold = $salesCount[$menu->id] ?? 0;
                return $menu;
            })
            ->take(4)                        // baru ambil top 4
            ->values();
    }

    // Isi sisa jika kurang dari 4 item yang available + terjual
    $alreadyIds = $bestSellers->pluck('id')->toArray();
    $needed     = max(0, 4 - $bestSellers->count());

    if ($needed > 0) {
        $fillers = \App\Models\Menu::available()
            ->when(!empty($alreadyIds), fn($q) => $q->whereNotIn('id', $alreadyIds))
            ->orderByDesc('rating')
            ->limit($needed)
            ->get()
            ->map(function ($menu) {
                $menu->total_sold = 0;
                return $menu;
            });

        $bestSellers = $bestSellers->concat($fillers)->values();
    }

    // ── Kategori dari DB (dengan jumlah menu aktual) ──
    $dbCategories = \App\Models\Category::withCount(['menus' => function ($q) {
        $q->where('is_available', true);
    }])->orderBy('sort_order')->get();

    // Peta ikon dan warna berdasarkan kata kunci nama kategori
    $iconMap = [
        'espresso'  => ['icon' => 'coffee',               'bg' => 'bg-[var(--c-md)]'],
        'black'     => ['icon' => 'zap',                  'bg' => 'bg-[#1a1a2e]'],
        'signature' => ['icon' => 'star',                 'bg' => 'bg-[var(--c-md-lt)]'],
        'blend'     => ['icon' => 'blend',                'bg' => 'bg-[#2d6a5e]'],
        'tea'       => ['icon' => 'leaf',                 'bg' => 'bg-[#276749]'],
        'coffee'    => ['icon' => 'coffee',               'bg' => 'bg-[var(--c-md)]'],
        'hot'       => ['icon' => 'flame',                'bg' => 'bg-orange-500'],
        'cold'      => ['icon' => 'thermometer-snowflake','bg' => 'bg-[#2B6CB0]'],
        'matcha'    => ['icon' => 'leaf',                 'bg' => 'bg-green-600'],
        'food'      => ['icon' => 'utensils',             'bg' => 'bg-[var(--c-dk)]'],
        'pizza'     => ['icon' => 'utensils',             'bg' => 'bg-red-600'],
        'snack'     => ['icon' => 'cookie',               'bg' => 'bg-amber-500'],
        'appetizer' => ['icon' => 'cookie',               'bg' => 'bg-[var(--c-md-lt)]'],
        'milk'      => ['icon' => 'glass-water',          'bg' => 'bg-sky-400'],
        'juice'     => ['icon' => 'citrus',               'bg' => 'bg-orange-400'],
        'dessert'   => ['icon' => 'cake-slice',           'bg' => 'bg-pink-400'],
    ];
    $bgPalette = [
        'bg-[var(--c-md)]','bg-[#1a1a2e]','bg-[var(--c-md-lt)]',
        'bg-[#2d6a5e]','bg-[#276749]','bg-[#2B6CB0]','bg-amber-600',
        'bg-rose-700','bg-teal-600','bg-violet-700',
    ];

    $categories = $dbCategories->map(function ($cat, $idx) use ($iconMap, $bgPalette) {
        $nameLower = strtolower($cat->name);
        $found     = ['icon' => 'tag', 'bg' => $bgPalette[$idx % count($bgPalette)]];
        foreach ($iconMap as $keyword => $style) {
            if (str_contains($nameLower, $keyword)) { $found = $style; break; }
        }
        return [
            'id'    => $cat->id,
            'name'  => $cat->name,
            'icon'  => $found['icon'],
            'bg'    => $found['bg'],
            'count' => $cat->menus_count,
        ];
    });

    return view('home', compact('bestSellers', 'categories'));
})->name('home');


// ── Public API: Best Sellers (dipakai oleh home page realtime polling) ──────
Route::get('/api/best-sellers', function () {
    $validOrders = \App\Models\Order::whereNotIn('status', ['pending', 'cancelled'])->get();

    $salesCount = [];
    foreach ($validOrders as $order) {
        if (is_array($order->items)) {
            foreach ($order->items as $item) {
                $rawId = $item['id'] ?? null;
                if ($rawId !== null) {
                    $menuId = (int) explode('-', (string) $rawId)[0];
                    if ($menuId > 0) {
                        $salesCount[$menuId] = ($salesCount[$menuId] ?? 0) + ($item['qty'] ?? 1);
                    }
                }
            }
        }
    }

    arsort($salesCount);
    $allSoldIds = array_keys($salesCount);

    $bestSellers = collect();
    if (!empty($allSoldIds)) {
        $raw = \App\Models\Menu::available()->whereIn('id', $allSoldIds)->get()->keyBy('id');
        $bestSellers = collect($allSoldIds)
            ->map(fn($id) => $raw->get($id))
            ->filter()
            ->map(function ($menu) use ($salesCount) {
                $menu->total_sold = $salesCount[$menu->id] ?? 0;
                return $menu;
            })
            ->take(4)
            ->values();
    }

    $needed     = max(0, 4 - $bestSellers->count());
    $alreadyIds = $bestSellers->pluck('id')->toArray();
    if ($needed > 0) {
        $fillers = \App\Models\Menu::available()
            ->when(!empty($alreadyIds), fn($q) => $q->whereNotIn('id', $alreadyIds))
            ->orderByDesc('rating')
            ->limit($needed)
            ->get()
            ->map(function ($menu) {
                $menu->total_sold = 0;
                return $menu;
            });
        $bestSellers = $bestSellers->concat($fillers)->values();
    }

    return response()->json($bestSellers->map(fn($m) => [
        'id'          => $m->id,
        'name'        => $m->name,
        'description' => $m->description,
        'price'       => $m->price,
        'image_url'   => $m->image_url,
        'rating'      => $m->rating ?? 0,
        'total_sold'  => $m->total_sold ?? 0,
    ]));
})->name('api.best-sellers');


Route::get('/scan/{table}', function ($table) {
    // A simple redirect to menu page with the table number as query parameter
    // The front-end Alpine.js will capture this and store it in localStorage
    return redirect()->route('menu', ['table' => $table]);
})->name('scan.qr');

// Menu Listing
Route::get('/menu', function () {
    $menus = Menu::available()->with('category')->orderBy('sort_order')->get();
    $categories = \App\Models\Category::orderBy('sort_order')->get();
    return view('menu', compact('menus', 'categories'));
})->name('menu');

// Menu Detail
Route::get('/menu/{id}', function ($id) {
    $menu = Menu::with('category')->findOrFail($id);
    $relatedMenus = Menu::available()
        ->where('category_id', $menu->category_id)
        ->where('id', '!=', $menu->id)
        ->inRandomOrder()
        ->limit(3)
        ->get();
    return view('detail-menu', compact('menu', 'relatedMenus'));
})->name('menu.detail');

// Cart
Route::get('/cart', function () {
    return view('cart');
})->name('cart');

// Checkout
Route::get('/checkout', function () {
    return view('checkout');
})->name('checkout');

// Checkout - create QRIS payment via Core API (AJAX POST)
Route::post('/order/create-qris', [OrderController::class, 'createQris'])->name('order.create_qris');

// Checkout - create Midtrans Snap token (AJAX POST, fallback)
Route::post('/order/create-token', [OrderController::class, 'createToken'])->name('order.create_token');

// Midtrans payment notification webhook (POST, no CSRF)
Route::post('/payment/notification', [OrderController::class, 'notification'])
    ->name('payment.notification')
    ->withoutMiddleware(['web', \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);

// Update payment status from client
Route::post('/order/update-status', [OrderController::class, 'updateStatus'])->name('order.update_status');

// Order Status page
Route::get('/order/status', [OrderController::class, 'status'])->name('order.status');

// Order History & Live Status API (public, no auth needed)
Route::get('/api/orders/history', [OrderController::class, 'history'])->name('api.orders.history');
Route::get('/api/orders/{id}/status', [OrderController::class, 'liveStatus'])->name('api.orders.live_status');

// =================== ADMIN ROUTES ===================
Route::prefix('admin')->name('admin.')->group(function () {
    
    // Auth Routes
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Protected Routes
    Route::middleware('admin.auth')->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');

        // Categories Management API
        Route::get('/api/categories', [\App\Http\Controllers\Admin\CategoryController::class, 'index'])->name('api.categories.index');
        Route::post('/api/categories', [\App\Http\Controllers\Admin\CategoryController::class, 'store'])->name('api.categories.store');
        Route::put('/api/categories/{category}', [\App\Http\Controllers\Admin\CategoryController::class, 'update'])->name('api.categories.update');
        Route::delete('/api/categories/{category}', [\App\Http\Controllers\Admin\CategoryController::class, 'destroy'])->name('api.categories.destroy');

        // Menu Management API for frontend
        Route::get('/api/menus', [MenuController::class, 'index'])->name('api.menus.index');
        Route::post('/api/menus', [MenuController::class, 'store'])->name('api.menus.store');
        Route::post('/api/menus/{menu}', [MenuController::class, 'update'])->name('api.menus.update'); // using POST for file upload (with _method=PUT)
        Route::delete('/api/menus/{menu}', [MenuController::class, 'destroy'])->name('api.menus.destroy');

        // Order Management API for frontend (kept for backward compatibility)
        Route::put('/api/orders/{id}/status', [DashboardController::class, 'updateOrderStatus'])->name('api.orders.status');

        // ── Live polling API (realtime dashboard refresh) ──
        Route::get('/api/live', [DashboardController::class, 'live'])->name('api.live');

        // Analytics API
        Route::get('/api/analytics/data', [AnalyticsController::class, 'data'])->name('api.analytics.data');
        Route::get('/api/analytics/export-csv', [AnalyticsController::class, 'exportCsv'])->name('api.analytics.csv');
        Route::get('/api/analytics/export-pdf', [AnalyticsController::class, 'exportPdf'])->name('api.analytics.pdf');

        // Settings API
        Route::put('/api/settings/password', [DashboardController::class, 'updatePassword'])->name('api.settings.password');

        // Table Management API
        Route::get('/api/tables', [TableController::class, 'index'])->name('api.tables.index');
        Route::post('/api/tables', [TableController::class, 'store'])->name('api.tables.store');
        Route::put('/api/tables/{table}', [TableController::class, 'update'])->name('api.tables.update');
        Route::delete('/api/tables/{table}', [TableController::class, 'destroy'])->name('api.tables.destroy');
    });
});

// =================== OWNER ROUTES ===================
// 'owner.auth' sudah handle: cek auth + cek role owner
Route::prefix('owner')->name('owner.')->middleware(['owner.auth'])->group(function () {
    Route::get('/', [\App\Http\Controllers\Owner\OwnerController::class, 'index'])->name('dashboard');
    Route::get('/api/analytics/data', [\App\Http\Controllers\Owner\OwnerController::class, 'analyticsData'])->name('api.analytics.data');
    Route::get('/api/analytics/export-csv', [\App\Http\Controllers\Owner\OwnerController::class, 'exportCsv'])->name('api.analytics.csv');
    Route::get('/api/analytics/export-pdf', [\App\Http\Controllers\Owner\OwnerController::class, 'exportPdf'])->name('api.analytics.pdf');
});

// =================== KASIR ROUTES ===================
// 'kasir.auth' sudah handle: cek auth + cek role kasir
Route::prefix('kasir')->name('kasir.')->middleware(['kasir.auth'])->group(function () {
    Route::get('/', [KasirController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [KasirController::class, 'index'])->name('dashboard.index');

    // Order Management API
    Route::put('/api/orders/{id}/status', [KasirController::class, 'updateOrderStatus'])->name('api.orders.status');

    // Analytics / Laporan API
    Route::get('/api/analytics/data', [KasirController::class, 'analyticsData'])->name('api.analytics.data');
    Route::get('/api/analytics/export-csv', [KasirController::class, 'exportCsv'])->name('api.analytics.csv');
    Route::get('/api/analytics/export-pdf', [KasirController::class, 'exportPdf'])->name('api.analytics.pdf');
});
