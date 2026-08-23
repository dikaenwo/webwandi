<!DOCTYPE html>
@php
    $host = request()->getHost();
    if (in_array($host, ['localhost', '127.0.0.1'])) {
        $fp = @stream_socket_client("udp://8.8.8.8:53", $errno, $errstr, 1);
        $serverIp = '127.0.0.1';
        if ($fp) {
            $socketName = stream_socket_get_name($fp, false);
            if ($socketName) $serverIp = trim(explode(':', $socketName)[0]);
            fclose($fp);
        }
        $port = request()->getPort();
        $portStr = ($port && $port != 80 && $port != 443) ? ':' . $port : '';
        $dynamicBaseUrl = 'http://' . $serverIp . $portStr;
    } else {
        $dynamicBaseUrl = request()->getSchemeAndHttpHost();
    }
@endphp
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Owner Dashboard — Skena Coffee</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans bg-[var(--c-bg)]/50 text-[var(--c-dk)] antialiased" data-theme="green"
      x-data="ownerApp()">

{{-- ===================== SIDEBAR ===================== --}}
<aside class="fixed inset-y-0 left-0 hidden md:flex flex-col w-64 bg-[var(--c-dk)] transition-transform duration-300 ease-in-out shadow-2xl z-40">
    <div class="px-6 py-6 border-b border-white/10">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-white/90 flex items-center justify-center p-1.5 shadow-sm">
                <img src="{{ asset('images/logo.png') }}" alt="Logo" class="w-full h-full object-contain">
            </div>
            <div>
                <p class="text-white font-bold text-sm leading-none">Skena Coffee</p>
                <p class="text-[var(--c-lt)]/60 text-xs mt-0.5">Owner Panel</p>
            </div>
        </div>
    </div>

    <nav class="flex-1 px-3 py-5 space-y-1 overflow-y-auto">
        <p class="text-[var(--c-lt)]/40 text-[10px] uppercase tracking-widest px-3 mb-3">Menu</p>
        <button class="sidebar-link sidebar-link-active w-full text-left">
            <i data-lucide="layout-dashboard" class="w-4 h-4 shrink-0"></i>
            <span>Dashboard & Pantau</span>
        </button>
    </nav>

    {{-- User Profile & Logout --}}
    <div class="p-4 border-t border-white/10">
        <div class="bg-white/5 rounded-2xl p-4 flex items-center gap-3 border border-white/10">
            <div class="w-9 h-9 rounded-full bg-[var(--c-lt)] flex items-center justify-center text-[var(--c-dk)] font-extrabold text-sm shrink-0">
                {{ substr($user->name, 0, 1) }}
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-white text-xs font-bold truncate">{{ $user->name }}</p>
                <p class="text-[var(--c-lt)]/50 text-[10px] truncate">Owner</p>
            </div>
            <form method="POST" action="{{ route('admin.logout') }}" class="shrink-0">
                @csrf
                <button type="submit" class="text-[var(--c-lt)]/60 hover:text-white transition-colors p-1" title="Keluar">
                    <i data-lucide="log-out" class="w-4 h-4"></i>
                </button>
            </form>
        </div>
    </div>
</aside>

<div class="md:pl-64 min-h-screen flex flex-col bg-[var(--c-bg)]/50">

{{-- ===================== HEADER ===================== --}}
<header class="sticky top-0 z-30 bg-white/90 backdrop-blur-md border-b border-[var(--c-lt)]/40 shadow-sm shrink-0">
    <div class="flex items-center justify-between px-4 sm:px-6 h-16">
        <div class="flex items-center gap-3">
            <p class="text-sm font-bold text-[var(--c-dk)] md:hidden">Owner Dashboard</p>
        </div>

        <div class="flex items-center gap-3">
            {{-- View Only Badge --}}
            <span class="hidden sm:flex items-center gap-1.5 text-[10px] font-bold px-3 py-1.5 rounded-full"
                  style="background:#fef3c7;color:#92400e;border:1px solid #fde68a;">
                <i data-lucide="eye" class="w-3 h-3"></i>
                Mode Pantau — Read Only
            </span>

            {{-- Date --}}
            <span class="text-xs text-[var(--c-md)]/60 font-medium">{{ now()->isoFormat('dddd, D MMMM Y') }}</span>
            
            {{-- Mobile Logout --}}
            <form method="POST" action="{{ route('admin.logout') }}" class="md:hidden ml-2">
                @csrf
                <button type="submit"
                        class="flex items-center gap-1.5 text-xs font-bold p-2 rounded-xl border border-[var(--c-lt)] text-[var(--c-md)] hover:bg-[var(--c-bg)] transition-colors">
                    <i data-lucide="log-out" class="w-3.5 h-3.5"></i>
                </button>
            </form>
        </div>
    </div>
</header>

{{-- ===================== MAIN ===================== --}}
<main class="flex-1 w-full max-w-7xl mx-auto px-4 sm:px-6 py-6 space-y-6 pb-12">

    {{-- Welcome Banner --}}
    <div class="rounded-2xl p-5 sm:p-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 bg-[var(--c-dk)]">
        <div>
            <h1 class="text-xl font-extrabold text-white flex items-center gap-2">Selamat datang, {{ $user->name }}! <i data-lucide="hand" class="w-5 h-5 text-white/80"></i></h1>
            <p class="text-sm text-white/70 mt-1">Berikut ringkasan perkembangan bisnis Skena Coffee hari ini.</p>
        </div>
        <div class="text-right">
            <p class="text-white/60 text-xs">Pendapatan Hari Ini</p>
            <p class="text-2xl font-extrabold text-white">Rp {{ number_format($stats['pendapatan_hari_ini'], 0, ',', '.') }}</p>
            @if($stats['growth_pct'] != 0)
            <p class="text-xs mt-0.5 font-bold {{ $stats['growth_pct'] >= 0 ? 'text-green-300' : 'text-red-300' }}">
                {{ $stats['growth_pct'] >= 0 ? '▲' : '▼' }} {{ abs($stats['growth_pct']) }}% vs kemarin
            </p>
            @endif
        </div>
    </div>

    {{-- ── Stat Cards ── --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        {{-- Order Hari Ini --}}
        <div class="stat-card bg-[var(--c-dk)] border-none">
            <div class="w-9 h-9 bg-white/15 rounded-xl flex items-center justify-center mb-3">
                <i data-lucide="receipt" class="w-4 h-4 text-white"></i>
            </div>
            <p class="text-2xl font-extrabold text-white">{{ $stats['total_order_hari_ini'] }}</p>
            <p class="text-[10px] text-white/70 mt-1">Order Hari Ini</p>
        </div>

        {{-- Rata-rata Transaksi --}}
        <div class="stat-card bg-[var(--c-md)] border-none">
            <div class="w-9 h-9 bg-white/15 rounded-xl flex items-center justify-center mb-3">
                <i data-lucide="trending-up" class="w-4 h-4 text-white"></i>
            </div>
            <p class="text-2xl font-extrabold text-white">Rp {{ number_format($stats['avg_order_value'], 0, ',', '.') }}</p>
            <p class="text-[10px] text-white/70 mt-1">Rata-rata / Transaksi</p>
        </div>

        {{-- Item Terjual Hari Ini --}}
        <div class="stat-card bg-[var(--c-bg)] border border-[var(--c-lt)]/30">
            <div class="w-9 h-9 bg-[var(--c-lt)]/20 rounded-xl flex items-center justify-center mb-3">
                <i data-lucide="coffee" class="w-4 h-4 text-[var(--c-dk)]"></i>
            </div>
            <p class="text-2xl font-extrabold text-[var(--c-dk)]">{{ $stats['total_items_today'] }}</p>
            <p class="text-[10px] text-[var(--c-dk)]/70 mt-1">Item Terjual Hari Ini</p>
        </div>

        {{-- Pendapatan 30 Hari --}}
        <div class="stat-card bg-[var(--c-md-lt)] border-none">
            <div class="w-9 h-9 bg-white/15 rounded-xl flex items-center justify-center mb-3">
                <i data-lucide="calendar" class="w-4 h-4 text-white"></i>
            </div>
            <p class="text-lg font-extrabold text-white">Rp {{ number_format($stats['pendapatan_30'], 0, ',', '.') }}</p>
            <p class="text-[10px] text-white/70 mt-1">Pendapatan 30 Hari</p>
        </div>
    </div>

    {{-- ── Analytics Section dengan Filter ── --}}
    <div x-data="ownerAnalytics()"
         x-init="init(); $watch('activeChart', (val) => { if (!loading && Object.keys(data).length) $nextTick(() => renderCharts()); });"
         class="space-y-5">

        {{-- Filter Bar --}}
        <div class="bg-white rounded-2xl border border-[var(--c-lt)]/30 p-4 shadow-sm">
            <div class="flex flex-wrap items-center gap-3">
                <p class="text-sm font-bold text-[var(--c-dk)] mr-2">Filter Periode:</p>
                <div class="flex gap-2 flex-wrap flex-1">
                    <template x-for="r in ranges" :key="r.value">
                        <button @click="setRange(r.value)"
                                :class="activeRange === r.value ? 'category-chip category-chip-active' : 'category-chip category-chip-inactive'"
                                class="category-chip text-xs py-1.5" x-text="r.label"></button>
                    </template>
                </div>
                <div x-show="activeRange === 'custom'" class="flex items-center gap-2">
                    <input type="date" x-model="customFrom" class="input-field text-xs py-1.5 px-3" style="min-width:130px;">
                    <span class="text-xs text-[var(--c-md)]">s/d</span>
                    <input type="date" x-model="customTo" class="input-field text-xs py-1.5 px-3" style="min-width:130px;">
                    <button @click="loadData()" class="btn-primary text-xs py-1.5 px-3">Terapkan</button>
                </div>
                <div class="flex gap-2 ml-auto">
                    <a :href="csvUrl" target="_blank"
                       class="flex items-center gap-1.5 text-xs font-bold px-3 py-2 rounded-xl border border-green-300 bg-green-50 text-green-700 hover:bg-green-100 transition-colors">
                        <i data-lucide="file-spreadsheet" class="w-3.5 h-3.5"></i> Excel
                    </a>
                    <a :href="pdfUrl" target="_blank"
                       class="flex items-center gap-1.5 text-xs font-bold px-3 py-2 rounded-xl border border-red-300 bg-red-50 text-red-700 hover:bg-red-100 transition-colors">
                        <i data-lucide="file-text" class="w-3.5 h-3.5"></i> PDF
                    </a>
                </div>
            </div>
        </div>

        {{-- Loading --}}
        <div x-show="loading" class="flex items-center justify-center py-12">
            <div class="flex flex-col items-center gap-3">
                <div class="w-10 h-10 border-4 border-[var(--c-lt)] border-t-[var(--c-dk)] rounded-full animate-spin"></div>
                <p class="text-sm text-[var(--c-md)]/60">Memuat data...</p>
            </div>
        </div>

        <div x-show="!loading" class="space-y-5">
            {{-- Summary dari filter --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white rounded-2xl border border-[var(--c-lt)]/30 p-4 shadow-sm">
                    <p class="text-[10px] text-[var(--c-md)]/60 uppercase font-bold tracking-wide mb-1">Total Pendapatan</p>
                    <p class="text-lg font-extrabold text-[var(--c-dk)]" x-text="'Rp ' + (data.summary?.total_revenue || 0).toLocaleString('id-ID')"></p>
                </div>
                <div class="bg-white rounded-2xl border border-[var(--c-lt)]/30 p-4 shadow-sm">
                    <p class="text-[10px] text-[var(--c-md)]/60 uppercase font-bold tracking-wide mb-1">Total Order</p>
                    <p class="text-lg font-extrabold text-[var(--c-dk)]" x-text="(data.summary?.total_orders || 0) + ' order'"></p>
                </div>
                <div class="bg-white rounded-2xl border border-[var(--c-lt)]/30 p-4 shadow-sm">
                    <p class="text-[10px] text-[var(--c-md)]/60 uppercase font-bold tracking-wide mb-1">Rata-rata / Order</p>
                    <p class="text-lg font-extrabold text-[var(--c-dk)]" x-text="'Rp ' + (data.summary?.avg_order_value || 0).toLocaleString('id-ID')"></p>
                </div>
                <div class="bg-white rounded-2xl border border-[var(--c-lt)]/30 p-4 shadow-sm">
                    <p class="text-[10px] text-[var(--c-md)]/60 uppercase font-bold tracking-wide mb-1">Item Terjual</p>
                    <p class="text-lg font-extrabold text-[var(--c-dk)]" x-text="(data.summary?.total_items || 0) + ' item'"></p>
                </div>
            </div>

            {{-- Grafik Utama --}}
            <div class="bg-white rounded-2xl border border-[var(--c-lt)]/30 p-5 shadow-sm">
                <div class="flex items-center justify-between mb-5">
                    <div>
                        <h3 class="font-bold text-[var(--c-dk)] text-sm">Grafik Penjualan</h3>
                        <p class="text-xs text-[var(--c-md)]/60 mt-0.5">Tren pendapatan berdasarkan periode</p>
                    </div>
                    <div class="flex gap-2">
                        <button @click="activeChart='revenue'" :class="activeChart==='revenue' ? 'category-chip-active' : 'category-chip-inactive'" class="category-chip text-[10px] px-3 py-1">Pendapatan</button>
                        <button @click="activeChart='orders'" :class="activeChart==='orders' ? 'category-chip-active' : 'category-chip-inactive'" class="category-chip text-[10px] px-3 py-1">Jumlah Order</button>
                    </div>
                </div>
                <div class="w-full h-56 relative">
                    <canvas id="ownerMainChart"></canvas>
                </div>
            </div>

            {{-- 2 Kolom: Top Menu + Jam Sibuk --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                {{-- Top Menu --}}
                <div class="bg-white rounded-2xl border border-[var(--c-lt)]/30 p-5 shadow-sm">
                    <h3 class="font-bold text-[var(--c-dk)] text-sm mb-4 flex items-center gap-2"><i data-lucide="trophy" class="w-4 h-4 text-[var(--c-md)]"></i> Menu Terlaris</h3>
                    <div class="space-y-3 max-h-72 overflow-y-auto pr-1">
                        <template x-for="(m, idx) in (data.top_menus || [])" :key="m.name">
                            <div class="flex items-center gap-3">
                                <div class="w-7 h-7 rounded-xl flex items-center justify-center text-[11px] font-extrabold text-white shrink-0"
                                     :style="idx === 0 ? 'background:var(--c-dk)' : idx === 1 ? 'background:var(--c-md)' : idx === 2 ? 'background:var(--c-md-lt)' : 'background:var(--c-lt)'"
                                     x-text="idx + 1"></div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex justify-between items-center mb-1">
                                        <span class="text-xs font-semibold text-[var(--c-dk)] truncate" x-text="m.name"></span>
                                        <span class="text-[10px] text-[var(--c-md)]/70 shrink-0 ml-2" x-text="m.qty + 'x'"></span>
                                    </div>
                                    <div class="h-1.5 bg-[var(--c-lt)]/20 rounded-full overflow-hidden">
                                        <div class="h-full bg-[var(--c-dk)] rounded-full transition-all duration-700"
                                             :style="`width:${data.top_menus?.length ? Math.round((m.qty / data.top_menus[0].qty) * 100) : 0}%`"></div>
                                    </div>
                                    <p class="text-[10px] text-[var(--c-md)]/60 mt-0.5" x-text="'Rp ' + m.revenue.toLocaleString('id-ID')"></p>
                                </div>
                            </div>
                        </template>
                        <div x-show="!data.top_menus?.length" class="text-center text-xs text-[var(--c-md)]/60 py-8">
                            Belum ada data penjualan
                        </div>
                    </div>
                </div>

                {{-- Jam Sibuk --}}
                <div class="bg-white rounded-2xl border border-[var(--c-lt)]/30 p-5 shadow-sm">
                    <h3 class="font-bold text-[var(--c-dk)] text-sm mb-4 flex items-center gap-2"><i data-lucide="clock" class="w-4 h-4 text-[var(--c-md)]"></i> Jam Tersibuk</h3>
                    <div class="w-full h-64 relative">
                        <canvas id="ownerPeakChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Riwayat Order (View Only) ── --}}
    <div class="bg-white rounded-2xl border border-[var(--c-lt)]/30 shadow-sm overflow-hidden"
         x-data="{ filterStatus: 'all', search: '' }">
        <div class="px-5 py-4 border-b border-[var(--c-lt)]/20">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h3 class="font-bold text-[var(--c-dk)] text-sm flex items-center gap-2"><i data-lucide="clipboard-list" class="w-4 h-4 text-[var(--c-md)]"></i> Riwayat Order Terbaru</h3>
                    <p class="text-xs text-[var(--c-md)]/60 mt-0.5">50 order terakhir — hanya pantau</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <input type="text" x-model="search" placeholder="Cari order ID..." class="input-field text-xs py-1.5 px-3 max-w-[160px]">
                    <template x-for="f in [{id:'all',label:'Semua'},{id:'paid',label:'Diproses'},{id:'making',label:'Dibuat'},{id:'ready',label:'Siap'},{id:'done',label:'Selesai'}]">
                        <button @click="filterStatus = f.id"
                                :class="filterStatus === f.id ? 'category-chip-active' : 'category-chip-inactive'"
                                class="category-chip text-[10px] py-1 px-2.5" x-text="f.label"></button>
                    </template>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[580px]">
                <thead class="bg-[var(--c-bg)]/50">
                    <tr>
                        <th class="table-header text-left">Order ID</th>
                        <th class="table-header text-left">Meja</th>
                        <th class="table-header text-left">Item</th>
                        <th class="table-header text-left">Total</th>
                        <th class="table-header text-left">Waktu</th>
                        <th class="table-header text-left">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[var(--c-lt)]/10">
                    @php
                    $statusClass = [
                        'paid'      => 'bg-blue-100 text-blue-700',
                        'making'    => 'bg-purple-100 text-purple-700',
                        'ready'     => 'bg-green-100 text-green-700',
                        'done'      => 'bg-gray-100 text-gray-600',
                        'cancelled' => 'bg-red-100 text-red-700',
                    ];
                    $statusLabel = [
                        'paid'      => 'Diproses',
                        'making'    => 'Dibuat',
                        'ready'     => 'Siap',
                        'done'      => 'Selesai',
                        'cancelled' => 'Batal',
                    ];
                    @endphp
                    @forelse($recentOrders as $order)
                    <tr class="hover:bg-[var(--c-bg)]/30 transition-colors duration-150">
                        <td class="table-cell font-mono font-bold text-[var(--c-dk)] text-xs">{{ $order['id'] }}</td>
                        <td class="table-cell">
                            <span class="bg-[var(--c-lt)]/30 text-[var(--c-dk)] font-bold text-xs px-2 py-1 rounded-lg">Meja {{ $order['table'] }}</span>
                        </td>
                        <td class="table-cell text-xs text-[var(--c-md)]/70">{{ $order['items_count'] }} item</td>
                        <td class="table-cell font-bold text-[var(--c-dk)] text-xs">Rp {{ number_format($order['total'], 0, ',', '.') }}</td>
                        <td class="table-cell text-xs text-[var(--c-md)]/50">{{ $order['date_formatted'] }}</td>
                        <td class="table-cell">
                            <span class="badge text-[10px] px-2.5 py-1 {{ $statusClass[$order['status']] ?? 'bg-gray-100 text-gray-600' }}">
                                {{ $statusLabel[$order['status']] ?? ucfirst($order['status']) }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="table-cell text-center text-xs text-[var(--c-md)]/60 py-10">
                            Belum ada data order.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</main>

{{-- Footer --}}
<footer class="shrink-0 border-t border-[var(--c-lt)]/30 px-6 py-3 bg-white">
    <p class="text-xs text-[var(--c-md)]/50 text-center">© {{ date('Y') }} Skena Coffee · Owner Dashboard · View Only Mode</p>
</footer>

</div> {{-- End Main wrapper --}}

<script>
document.addEventListener('DOMContentLoaded', () => { lucide.createIcons(); });
const obs = new MutationObserver(() => { obs.disconnect(); lucide.createIcons(); obs.observe(document.body, { childList: true, subtree: true }); });
obs.observe(document.body, { childList: true, subtree: true });

// ── Server-side chart for today's sales ──
document.addEventListener('DOMContentLoaded', () => {
    const style = getComputedStyle(document.body);
    const cDk = style.getPropertyValue('--c-dk').trim();
    const cMd = style.getPropertyValue('--c-md').trim();
    const cLt = style.getPropertyValue('--c-lt').trim();

    const todayCtx = document.getElementById('todaySalesChart');
    if (todayCtx) {
        new Chart(todayCtx, {
            type: 'bar',
            data: {
                labels: {{ Illuminate\Support\Js::from($salesLabels) }},
                datasets: [{ label: 'Pendapatan', data: {{ Illuminate\Support\Js::from($salesData) }}, backgroundColor: cMd, borderRadius: 6, hoverBackgroundColor: cDk }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, grid: { color: cLt }, ticks: { color: cMd, font: { size: 10 }, callback: v => v >= 1000000 ? 'Rp ' + (v/1000000).toFixed(1) + 'jt' : v } }, x: { grid: { display: false }, ticks: { color: cMd, font: { size: 10 } } } }
            }
        });
    }
});

// ── Owner App Alpine (for filter date badge) ──
function ownerApp() { return {}; }

// ── Owner Analytics Alpine Component ──
function ownerAnalytics() {
    return {
        loading: false,
        activeRange: '7',
        activeChart: 'revenue',
        customFrom: '',
        customTo: '',
        data: {},
        mainChart: null,
        peakChart: null,

        ranges: [
            { value: '7',      label: '7 Hari' },
            { value: '30',     label: '30 Hari' },
            { value: '90',     label: '3 Bulan' },
            { value: '365',    label: '1 Tahun' },
            { value: 'custom', label: 'Custom' },
        ],

        get csvUrl() { return this._url('/owner/api/analytics/export-csv'); },
        get pdfUrl() { return this._url('/owner/api/analytics/export-pdf'); },

        _url(base) {
            let u = base + '?range=' + this.activeRange;
            if (this.activeRange === 'custom') u += '&date_from=' + this.customFrom + '&date_to=' + this.customTo;
            return u;
        },

        init() { this.loadData(); },

        setRange(val) {
            this.activeRange = val;
            if (val !== 'custom') this.loadData();
        },

        async loadData() {
            this.loading = true;
            try {
                const res = await fetch(this._url('/owner/api/analytics/data'), { headers: { Accept: 'application/json' } });
                this.data = await res.json();
                this.loading = false;
                // $nextTick: tunggu Alpine update DOM (tampilkan !loading div) baru render chart
                this.$nextTick(() => this.renderCharts());
            } catch(e) { 
                console.error('Owner analytics error:', e); 
                this.loading = false;
            }
        },

        renderCharts() {
            const style = getComputedStyle(document.body);
            const cDk = style.getPropertyValue('--c-dk').trim();
            const cMd = style.getPropertyValue('--c-md').trim();
            const cLt = style.getPropertyValue('--c-lt').trim();

            const mainCtx = document.getElementById('ownerMainChart');
            if (mainCtx) {
                if (this.mainChart) this.mainChart.destroy();
                const isRev = this.activeChart === 'revenue';
                const cd = this.data.chart || { labels: [], revenue: [], orders: [] };
                this.mainChart = new Chart(mainCtx, {
                    type: 'line',
                    data: { 
                        labels: cd.labels, 
                        datasets: [{ 
                            label: isRev ? 'Pendapatan' : 'Order', 
                            data: isRev ? cd.revenue : cd.orders, 
                            borderColor: isRev ? cMd : cDk, 
                            backgroundColor: 'transparent',
                            fill: false,
                            tension: 0.4,
                            borderWidth: 3,
                            pointBackgroundColor: '#fff',
                            pointBorderColor: isRev ? cMd : cDk,
                            pointBorderWidth: 2,
                            pointRadius: 4,
                            pointHoverRadius: 6
                        }] 
                    },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false }, tooltip: { callbacks: { label: ctx => isRev ? 'Rp ' + ctx.raw.toLocaleString('id-ID') : ctx.raw + ' order' } } },
                        scales: { 
                            y: { 
                                beginAtZero: true, 
                                border: { display: false },
                                grid: { color: cLt, drawBorder: false }, 
                                ticks: { 
                                    color: cMd, 
                                    font: { size: 10 }, 
                                    callback: v => isRev ? (v >= 1000000 ? (v/1000000).toFixed(1)+'jt' : (v >= 1000 ? (v/1000).toFixed(0)+'k' : v)) : v 
                                } 
                            }, 
                            x: { 
                                border: { display: false },
                                grid: { display: false }, 
                                ticks: { color: cMd, font: { size: 10 }, maxRotation: 45 } 
                            } 
                        } 
                    }
                });
            }

            // Peak hours chart
            const peakCtx = document.getElementById('ownerPeakChart');
            if (peakCtx) {
                if (this.peakChart) this.peakChart.destroy();
                const pk = this.data.peak_hours || { labels: [], data: [] };
                const maxVal = Math.max(...pk.data);
                this.peakChart = new Chart(peakCtx, {
                    type: 'bar',
                    data: { labels: pk.labels, datasets: [{ data: pk.data, backgroundColor: pk.data.map(v => v === maxVal ? cDk : cLt), borderRadius: 6, maxBarThickness: 40 }] },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } },
                        scales: { y: { beginAtZero: true, border: {display: false}, grid: { color: cLt, drawBorder: false }, ticks: { color: cMd, font: { size: 10 } } }, x: { border: {display: false}, grid: { display: false }, ticks: { color: cMd, font: { size: 9 } } } }
                    }
                });
            }
        }
    };
}
</script>

</body>
</html>
