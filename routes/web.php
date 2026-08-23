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
    $salesCount = [];
    foreach ($validOrders as $order) {
        if (is_array($order->items)) {
            foreach ($order->items as $item) {
                $menuId = $item['id'] ?? null;
                if ($menuId) {
                    $salesCount[$menuId] = ($salesCount[$menuId] ?? 0) + ($item['qty'] ?? 1);
                }
            }
        }
    }

    // Ambil top 4 menu berdasarkan total qty terjual
    arsort($salesCount);
    $topIds = array_slice(array_keys($salesCount), 0, 4);

    // Ambil menu yang sudah terjual (urut dari terbanyak)
    $bestSellers = collect();

    if (!empty($topIds)) {
        $bestSellersRaw = \App\Models\Menu::available()
            ->whereIn('id', $topIds)
            ->get()
            ->keyBy('id');

        $bestSellers = collect($topIds)
            ->map(fn($id) => $bestSellersRaw->get($id))
            ->filter()
            ->map(function ($menu) use ($salesCount) {
                $menu->total_sold = $salesCount[$menu->id] ?? 0;
                return $menu;
            })
            ->values();
    }

    // Jika kurang dari 4, isi sisa slot dengan menu rating tertinggi (yang belum masuk)
    $alreadyIds = $bestSellers->pluck('id')->toArray();
    $needed = 4 - $bestSellers->count();

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

    return view('home', compact('bestSellers'));
})->name('home');


// QR Scan Route
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
Route::prefix('owner')->name('owner.')->middleware('admin.auth')->group(function () {
    Route::get('/', [\App\Http\Controllers\Owner\OwnerController::class, 'index'])->name('dashboard');
    Route::get('/api/analytics/data', [\App\Http\Controllers\Owner\OwnerController::class, 'analyticsData'])->name('api.analytics.data');
    Route::get('/api/analytics/export-csv', [\App\Http\Controllers\Owner\OwnerController::class, 'exportCsv'])->name('api.analytics.csv');
    Route::get('/api/analytics/export-pdf', [\App\Http\Controllers\Owner\OwnerController::class, 'exportPdf'])->name('api.analytics.pdf');
});

// =================== KASIR ROUTES ===================
Route::prefix('kasir')->name('kasir.')->middleware(['admin.auth', 'kasir.auth'])->group(function () {
    Route::get('/', [KasirController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [KasirController::class, 'index'])->name('dashboard.index');

    // Order Management API
    Route::put('/api/orders/{id}/status', [KasirController::class, 'updateOrderStatus'])->name('api.orders.status');

    // Analytics / Laporan API
    Route::get('/api/analytics/data', [KasirController::class, 'analyticsData'])->name('api.analytics.data');
    Route::get('/api/analytics/export-csv', [KasirController::class, 'exportCsv'])->name('api.analytics.csv');
    Route::get('/api/analytics/export-pdf', [KasirController::class, 'exportPdf'])->name('api.analytics.pdf');
});
