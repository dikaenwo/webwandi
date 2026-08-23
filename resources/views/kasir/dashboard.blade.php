<!DOCTYPE html>
@php
    $host = request()->getHost();
    if (in_array($host, ['localhost', '127.0.0.1'])) {
        $serverIp = gethostbyname(gethostname());
        $fp = @stream_socket_client("udp://8.8.8.8:53", $errno, $errstr, 1);
        if ($fp) {
            $socketName = stream_socket_get_name($fp, false);
            if ($socketName) {
                $serverIp = trim(explode(':', $socketName)[0]);
            }
            fclose($fp);
        }
        $port = request()->getPort();
        $portStr = ($port && $port != 80 && $port != 443) ? ':' . $port : '';
        $dynamicBaseUrl = 'http://' . $serverIp . $portStr;
    } else {
        $dynamicBaseUrl = request()->getSchemeAndHttpHost();
    }
@endphp
<html lang="id" x-data="{ sidebarOpen: false }" class="scroll-smooth">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Kasir Dashboard — Skena Coffee</title>
    <meta name="description" content="Dashboard kasir Skena Coffee untuk mengelola pesanan dan mencetak laporan">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans bg-[var(--c-bg)]/50 text-[var(--c-dk)] antialiased" :data-theme="theme" x-data="{
    activePage: 'dashboard',
    sidebarOpen: false,
    theme: localStorage.getItem('theme') || 'green',
    orders: {{ Illuminate\Support\Js::from($orders) }},
    searchQuery: '',
    filterStatus: 'all',
    get filteredOrders() {
        return this.orders.filter(order => {
            const search = this.searchQuery.toLowerCase();
            const matchesSearch = search === '' || 
                (order.id && order.id.toLowerCase().includes(search)) || 
                (order.table && String(order.table).includes(search)) ||
                (order.customer_name && order.customer_name.toLowerCase().includes(search));
                
            const matchesStatus = this.filterStatus === 'all' || order.status === this.filterStatus;
            
            return matchesSearch && matchesStatus;
        });
    },
    stats: {{ Illuminate\Support\Js::from($stats) }},
    salesLabels: {{ Illuminate\Support\Js::from($salesLabels) }},
    salesData: {{ Illuminate\Support\Js::from($salesData) }},
    topMenus: {{ Illuminate\Support\Js::from($topMenus) }},

    showReceiptModal: false,
    selectedOrder: null,
    
    openReceipt(order) {
        this.selectedOrder = order;
        this.showReceiptModal = true;
    },
    
    printReceipt() {
        window.print();
    },

    async advanceOrderStatus(order) {
        let nextStatus = '';
        if (order.status === 'paid') nextStatus = 'making';
        else if (order.status === 'making') nextStatus = 'ready';
        else if (order.status === 'ready') nextStatus = 'done';
        else return;

        try {
            const response = await fetch('/kasir/api/orders/' + order.id + '/status', {
                method: 'PUT',
                headers: { 
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ status: nextStatus })
            });
            const data = await response.json();
            if (response.ok) {
                order.status = nextStatus;
                // Update active count
                if (nextStatus === 'done') {
                    this.stats.aktif = Math.max(0, this.stats.aktif - 1);
                }
            } else {
                alert('Error: ' + (data.message || 'Gagal mengubah status'));
            }
        } catch (e) {
            console.error(e);
            alert('Kesalahan jaringan saat mengubah status pesanan.');
        }
    },

    getStatusClass(status) {
        const map = {
            'pending':   'bg-amber-100 text-amber-700',
            'paid':      'bg-blue-100 text-blue-700',
            'making':    'bg-purple-100 text-purple-700',
            'ready':     'bg-green-100 text-green-700',
            'done':      'bg-gray-100 text-gray-600',
            'cancelled': 'bg-red-100 text-red-700',
        };
        return map[status] || 'bg-gray-100 text-gray-600';
    },
    getStatusLabel(status) {
        const map = {
            'pending':   'Menunggu Bayar',
            'paid':      'Diproses',
            'making':    'Dibuat',
            'ready':     'Siap',
            'done':      'Selesai',
            'cancelled': 'Dibatalkan',
        };
        return map[status] || status;
    },
    getNextActionLabel(status) {
        const map = {
            'paid':   'Mulai Buat',
            'making': 'Tandai Siap',
            'ready':  'Selesaikan',
        };
        return map[status] || '';
    },
    getNextActionIcon(status) {
        const map = {
            'paid':   'chef-hat',
            'making': 'bell',
            'ready':  'check-check',
        };
        return map[status] || 'check';
    },
    getNextActionColor(status) {
        const map = {
            'paid':   'bg-blue-500 hover:bg-blue-600',
            'making': 'bg-purple-500 hover:bg-purple-600',
            'ready':  'bg-green-500 hover:bg-green-600',
        };
        return map[status] || 'bg-gray-500';
    }
}">

{{-- ===================== MOBILE OVERLAY ===================== --}}
<div x-show="sidebarOpen"
     @click="sidebarOpen = false"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     x-cloak
     class="fixed inset-0 bg-black/40 backdrop-blur-sm z-30 lg:hidden"></div>

{{-- ===================== SIDEBAR ===================== --}}
<aside class="fixed inset-y-0 left-0 z-40 w-64 bg-[var(--c-dk)] flex flex-col transition-transform duration-300 ease-in-out shadow-2xl"
       :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">

    {{-- Logo --}}
    <div class="px-6 py-6 border-b border-white/10">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-white/90 flex items-center justify-center p-1.5 shadow-sm">
                <img src="{{ asset('images/logo.png') }}" alt="Logo" class="w-full h-full object-contain">
            </div>
            <div>
                <p class="text-white font-bold text-sm leading-none">Skena Coffee</p>
                <p class="text-[var(--c-lt)]/60 text-xs mt-0.5">Kasir Panel</p>
            </div>
        </div>
    </div>

    {{-- Nav Links --}}
    <nav class="flex-1 px-3 py-5 space-y-1 overflow-y-auto">
        <p class="text-[var(--c-lt)]/40 text-[10px] uppercase tracking-widest px-3 mb-3">Menu</p>

        @php
        $navItems = [
            ['key'=>'dashboard',  'label'=>'Dashboard',          'icon'=>'layout-dashboard'],
            ['key'=>'orders',     'label'=>'Manajemen Order',     'icon'=>'receipt'],
        ];
        @endphp

        @foreach($navItems as $nav)
        <button @click="activePage = '{{ $nav['key'] }}'; sidebarOpen = false"
                id="kasir-nav-{{ $nav['key'] }}"
                :class="activePage === '{{ $nav['key'] }}' ? 'sidebar-link sidebar-link-active' : 'sidebar-link sidebar-link-inactive'"
                class="sidebar-link w-full text-left">
            <i data-lucide="{{ $nav['icon'] }}" class="w-4 h-4 shrink-0"></i>
            <span>{{ $nav['label'] }}</span>
            @if($nav['key'] === 'orders')
            <span class="ml-auto bg-white/20 text-white text-[10px] font-bold px-2 py-0.5 rounded-full"
                  x-text="orders.filter(o => ['paid','making','ready'].includes(o.status)).length"
                  x-show="orders.filter(o => ['paid','making','ready'].includes(o.status)).length > 0"></span>
            @endif
        </button>
        @endforeach

        <div class="pt-4">
            <p class="text-[var(--c-lt)]/40 text-[10px] uppercase tracking-widest px-3 mb-3">Laporan</p>
            <button @click="activePage = 'analytics'; sidebarOpen = false"
                    id="kasir-nav-analytics"
                    :class="activePage === 'analytics' ? 'sidebar-link sidebar-link-active' : 'sidebar-link sidebar-link-inactive'"
                    class="sidebar-link w-full text-left">
                <i data-lucide="bar-chart-2" class="w-4 h-4 shrink-0"></i>
                <span>Laporan</span>
            </button>
        </div>
    </nav>

    {{-- User Card --}}
    <div class="px-3 py-4 border-t border-white/10">
        <div class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-white/5 transition-colors">
            <div class="w-8 h-8 rounded-full bg-[var(--c-lt)]/20 flex items-center justify-center text-[var(--c-lt)] font-bold text-sm border border-[var(--c-lt)]/30">K</div>
            <div class="flex-1 min-w-0">
                <p class="text-white font-semibold text-xs truncate">{{ $user->name }}</p>
                <p class="text-[var(--c-lt)]/50 text-[10px] truncate">{{ $user->email }}</p>
            </div>
            <form action="{{ route('admin.logout') }}" method="POST">
                @csrf
                <button type="submit" title="Logout" class="cursor-pointer">
                    <i data-lucide="log-out" class="w-4 h-4 text-[var(--c-lt)]/40 hover:text-[var(--c-lt)] transition-colors"></i>
                </button>
            </form>
        </div>
    </div>
</aside>

{{-- ===================== MAIN AREA ===================== --}}
<div class="lg:pl-64 min-h-screen flex flex-col">

    {{-- TOP BAR --}}
    <header class="sticky top-0 z-20 bg-white/80 backdrop-blur-md border-b border-[var(--c-lt)]/30 shadow-sm overflow-visible">
        <div class="flex items-center justify-between px-4 sm:px-6 h-16 gap-4">
            <div class="flex items-center gap-3">
                {{-- Mobile hamburger --}}
                <button @click="sidebarOpen = !sidebarOpen"
                        class="lg:hidden w-9 h-9 rounded-xl border border-[var(--c-lt)] flex items-center justify-center hover:bg-[var(--c-bg)] transition-colors"
                        id="kasir-hamburger">
                    <i data-lucide="menu" class="w-4 h-4 text-[var(--c-dk)]"></i>
                </button>
                <div>
                    <h1 class="font-bold text-[var(--c-dk)] text-base" x-text="{
                        dashboard: 'Dashboard Kasir',
                        orders: 'Manajemen Order',
                        analytics: 'Laporan Penjualan'
                    }[activePage] || 'Dashboard Kasir'"></h1>
                    <p class="text-xs text-[var(--c-md)]/60">{{ now()->isoFormat('dddd, D MMMM Y') }}</p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                {{-- Notification Bell with Dropdown --}}
                <div x-data="{ notifOpen: false }">
                    <button x-ref="notifBellKasir" @click="notifOpen = !notifOpen"
                            class="relative w-9 h-9 rounded-xl border border-[var(--c-lt)] flex items-center justify-center hover:bg-[var(--c-bg)] transition-colors">
                        <i data-lucide="bell" class="w-4 h-4 text-[var(--c-dk)]"></i>
                        <span style="width:16px;height:16px;min-width:16px;min-height:16px;max-width:16px;max-height:16px;font-size:9px;line-height:1;top:-6px;right:-6px;"
                              class="absolute bg-red-500 rounded-full text-white font-bold flex items-center justify-center shadow-sm"
                              x-text="orders.filter(o => ['paid','making','ready'].includes(o.status)).length"
                              x-show="orders.filter(o => ['paid','making','ready'].includes(o.status)).length > 0"></span>
                    </button>

                    {{-- Teleported Dropdown --}}
                    <template x-teleport="body">
                        <div x-show="notifOpen" x-cloak
                             @click.away="notifOpen = false"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                             x-transition:leave-end="opacity-0 translate-y-2 scale-95"
                             class="fixed z-[9999] w-80 bg-white rounded-2xl border border-[var(--c-lt)]/30 shadow-2xl overflow-hidden"
                             x-effect="if(notifOpen) { const r = $refs.notifBellKasir.getBoundingClientRect(); $el.style.top = (r.bottom + 8) + 'px'; $el.style.right = (window.innerWidth - r.right) + 'px'; $nextTick(() => lucide.createIcons()); }">

                            {{-- Header --}}
                            <div class="px-4 py-3 border-b border-[var(--c-lt)]/20 flex items-center justify-between bg-[var(--c-bg)]/50">
                                <div class="flex items-center gap-2">
                                    <i data-lucide="bell" class="w-4 h-4 text-[var(--c-dk)]"></i>
                                    <span class="font-bold text-[var(--c-dk)] text-sm">Notifikasi</span>
                                </div>
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full"
                                      :class="orders.filter(o => ['paid','making','ready'].includes(o.status)).length > 0 ? 'bg-red-100 text-red-600' : 'bg-gray-100 text-gray-500'"
                                      x-text="orders.filter(o => ['paid','making','ready'].includes(o.status)).length + ' aktif'"></span>
                            </div>

                            {{-- Notification List --}}
                            <div class="max-h-80 overflow-y-auto">
                                <template x-for="order in orders.filter(o => ['paid','making','ready'].includes(o.status)).slice(0, 8)" :key="order.id">
                                    <div class="px-4 py-3 border-b border-[var(--c-lt)]/10 hover:bg-[var(--c-bg)]/50 transition-colors cursor-pointer"
                                         @click="activePage = 'orders'; notifOpen = false">
                                        <div class="flex items-start gap-3">
                                            <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0 mt-0.5"
                                                 :class="order.status === 'paid' ? 'bg-blue-100' : order.status === 'making' ? 'bg-purple-100' : 'bg-green-100'">
                                                <i :data-lucide="order.status === 'paid' ? 'credit-card' : order.status === 'making' ? 'chef-hat' : 'bell'"
                                                   class="w-3.5 h-3.5"
                                                   :class="order.status === 'paid' ? 'text-blue-600' : order.status === 'making' ? 'text-purple-600' : 'text-green-600'"></i>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-center justify-between gap-2">
                                                    <span class="text-xs font-bold text-[var(--c-dk)] truncate" x-text="'Pesanan ' + order.id"></span>
                                                    <span class="badge text-[9px] px-1.5 py-0.5 shrink-0"
                                                          :class="getStatusClass(order.status)"
                                                          x-text="getStatusLabel(order.status)"></span>
                                                </div>
                                                <p class="text-[11px] text-[var(--c-md)]/70 mt-0.5" x-text="'Meja ' + order.table + ' · ' + order.items_count + ' item · Rp ' + order.total.toLocaleString('id-ID')"></p>
                                                <p class="text-[10px] text-[var(--c-md)]/40 mt-1" x-text="order.time"></p>
                                            </div>
                                        </div>
                                    </div>
                                </template>

                                {{-- Empty State --}}
                                <div x-show="orders.filter(o => ['paid','making','ready'].includes(o.status)).length === 0"
                                     class="py-10 px-4 text-center">
                                    <div class="w-14 h-14 bg-[var(--c-lt)]/15 rounded-2xl flex items-center justify-center mx-auto mb-3">
                                        <i data-lucide="bell-off" class="w-6 h-6 text-[var(--c-md)]/30"></i>
                                    </div>
                                    <p class="text-sm font-semibold text-[var(--c-dk)]/70">Tidak ada notifikasi</p>
                                    <p class="text-xs text-[var(--c-md)]/40 mt-1">Pesanan baru akan muncul di sini</p>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
                {{-- Refresh --}}
                <button onclick="window.location.reload()" class="w-9 h-9 rounded-xl border border-[var(--c-lt)] flex items-center justify-center hover:bg-[var(--c-bg)] transition-colors">
                    <i data-lucide="refresh-cw" class="w-4 h-4 text-[var(--c-dk)]"></i>
                </button>
            </div>
        </div>
    </header>

    {{-- PAGE CONTENT --}}
    <main class="flex-1 p-4 sm:p-6 overflow-auto">

        {{-- ======= DASHBOARD PAGE ======= --}}
        <div x-show="activePage === 'dashboard'" class="space-y-6">

            {{-- Stats Cards --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                {{-- Total Order --}}
                <div class="stat-card bg-[var(--c-dk)] border-none">
                    <div class="flex items-start justify-between mb-4">
                        <div class="w-10 h-10 bg-white/15 rounded-xl flex items-center justify-center">
                            <i data-lucide="receipt" class="w-5 h-5 text-white opacity-90"></i>
                        </div>
                        <span class="text-xs font-medium text-white opacity-70 bg-white/10 px-2 py-0.5 rounded-full">hari ini</span>
                    </div>
                    <p class="text-2xl font-extrabold text-white" x-text="stats.total_order"></p>
                    <p class="text-xs text-white opacity-70 mt-1">Total Order</p>
                </div>
                
                {{-- Pendapatan --}}
                <div class="stat-card bg-[var(--c-md)] border-none">
                    <div class="flex items-start justify-between mb-4">
                        <div class="w-10 h-10 bg-white/15 rounded-xl flex items-center justify-center">
                            <i data-lucide="banknote" class="w-5 h-5 text-white opacity-90"></i>
                        </div>
                        <span class="text-xs font-medium text-white opacity-70 bg-white/10 px-2 py-0.5 rounded-full">hari ini</span>
                    </div>
                    <p class="text-2xl font-extrabold text-white" x-text="'Rp ' + (stats.pendapatan || 0).toLocaleString('id-ID')"></p>
                    <p class="text-xs text-white opacity-70 mt-1">Pendapatan</p>
                </div>
                
                {{-- Pesanan Aktif --}}
                <div class="stat-card bg-[var(--c-bg)] border border-[var(--c-lt)]/30">
                    <div class="flex items-start justify-between mb-4">
                        <div class="w-10 h-10 bg-[var(--c-lt)]/20 rounded-xl flex items-center justify-center">
                            <i data-lucide="clock" class="w-5 h-5 text-[var(--c-dk)] opacity-90"></i>
                        </div>
                        <span class="text-xs font-medium text-[var(--c-dk)] opacity-70 bg-[var(--c-lt)]/20 px-2 py-0.5 rounded-full">aktif</span>
                    </div>
                    <p class="text-2xl font-extrabold text-[var(--c-dk)]" x-text="stats.aktif"></p>
                    <p class="text-xs text-[var(--c-dk)] opacity-70 mt-1">Pesanan Aktif</p>
                </div>

                {{-- Rata-rata --}}
                <div class="stat-card bg-[var(--c-bg)] border border-[var(--c-lt)]/30">
                    <div class="flex items-start justify-between mb-4">
                        <div class="w-10 h-10 bg-[var(--c-lt)]/20 rounded-xl flex items-center justify-center">
                            <i data-lucide="trending-up" class="w-5 h-5 text-[var(--c-dk)] opacity-90"></i>
                        </div>
                        <span class="text-xs font-medium text-[var(--c-dk)] opacity-70 bg-[var(--c-lt)]/20 px-2 py-0.5 rounded-full">avg</span>
                    </div>
                    <p class="text-2xl font-extrabold text-[var(--c-dk)]" x-text="'Rp ' + (stats.avg_order || 0).toLocaleString('id-ID')"></p>
                    <p class="text-xs text-[var(--c-dk)] opacity-70 mt-1">Rata-rata / Order</p>
                </div>
            </div>

            {{-- Charts Row --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
                {{-- Sales Chart --}}
                <div class="lg:col-span-2 bg-white rounded-2xl border border-[var(--c-lt)]/30 p-5 shadow-sm">
                    <div class="flex items-center justify-between mb-5">
                        <div>
                            <h3 class="font-bold text-[var(--c-dk)] text-sm">Grafik Penjualan</h3>
                            <p class="text-xs text-[var(--c-md)]/60 mt-0.5">7 hari terakhir</p>
                        </div>
                    </div>
                    <div class="w-full h-48 mt-4 relative">
                        <canvas id="kasirSalesChart"></canvas>
                    </div>
                </div>

                {{-- Top Menu --}}
                <div class="bg-white rounded-2xl border border-[var(--c-lt)]/30 p-5 shadow-sm">
                    <div class="flex items-center justify-between mb-5">
                        <div>
                            <h3 class="font-bold text-[var(--c-dk)] text-sm">Menu Terlaris</h3>
                            <p class="text-xs text-[var(--c-md)]/60 mt-0.5">Berdasarkan total pesanan</p>
                        </div>
                    </div>
                    <div class="space-y-4">
                        <template x-for="m in topMenus" :key="m.name">
                            <div>
                                <div class="flex items-center gap-2 mb-1.5">
                                    <i data-lucide="coffee" class="w-4 h-4 text-[var(--c-md)] shrink-0"></i>
                                    <span class="text-xs font-semibold text-[var(--c-dk)] flex-1 truncate" x-text="m.name"></span>
                                    <span class="text-xs text-[var(--c-md)]/60 font-medium" x-text="m.pct + '%'"></span>
                                </div>
                                <div class="h-1.5 bg-[var(--c-lt)]/20 rounded-full overflow-hidden">
                                    <div class="h-full bg-[var(--c-dk)] rounded-full transition-all duration-700"
                                         :style="`width: ${m.pct}%`"></div>
                                </div>
                            </div>
                        </template>
                        <div x-show="topMenus.length === 0" class="text-center text-xs text-[var(--c-md)]/60 py-4">
                            Belum ada data penjualan.
                        </div>
                    </div>
                </div>
            </div>

            {{-- Active Orders Quick View --}}
            <div class="bg-white rounded-2xl border border-[var(--c-lt)]/30 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-[var(--c-lt)]/20 flex items-center justify-between">
                    <div>
                        <h3 class="font-bold text-[var(--c-dk)] text-sm">Pesanan Aktif</h3>
                        <p class="text-xs text-[var(--c-md)]/60 mt-0.5">Pesanan yang perlu diproses</p>
                    </div>
                    <button @click="activePage = 'orders'" class="btn-outline text-xs py-1.5">
                        Lihat Semua <i data-lucide="arrow-right" class="w-3 h-3"></i>
                    </button>
                </div>

                {{-- Active order cards (mobile-friendly) --}}
                <div class="p-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                    <template x-for="order in orders.filter(o => ['paid','making','ready'].includes(o.status)).slice(0, 6)" :key="order.id">
                        <div class="bg-[var(--c-bg)]/50 rounded-xl border border-[var(--c-lt)]/30 p-4 hover:shadow-md transition-all duration-200">
                            <div class="flex items-center justify-between mb-3">
                                <span class="font-mono font-bold text-[var(--c-dk)] text-xs" x-text="order.id"></span>
                                <span class="badge text-[10px] px-2 py-0.5"
                                      :class="getStatusClass(order.status)"
                                      x-text="getStatusLabel(order.status)"></span>
                            </div>
                            <div class="space-y-1.5 mb-3">
                                <div class="flex items-center gap-2 text-xs">
                                    <i data-lucide="armchair" class="w-3.5 h-3.5 text-[var(--c-md)]/50"></i>
                                    <span class="text-[var(--c-md)]" x-text="'Meja ' + order.table"></span>
                                </div>
                                <div class="flex items-center gap-2 text-xs">
                                    <i data-lucide="user" class="w-3.5 h-3.5 text-[var(--c-md)]/50"></i>
                                    <span class="text-[var(--c-md)]" x-text="order.customer_name || '-'"></span>
                                </div>
                                <div class="flex items-center gap-2 text-xs">
                                    <i data-lucide="shopping-bag" class="w-3.5 h-3.5 text-[var(--c-md)]/50"></i>
                                    <span class="text-[var(--c-md)]" x-text="order.items_count + ' item'"></span>
                                </div>
                            </div>
                            <div class="flex items-center justify-between pt-3 border-t border-[var(--c-lt)]/30">
                                <span class="font-bold text-[var(--c-dk)] text-sm" x-text="'Rp ' + order.total.toLocaleString('id-ID')"></span>
                                <div class="flex gap-1.5">
                                    <button @click="openReceipt(order)" class="w-8 h-8 rounded-lg bg-white flex items-center justify-center hover:bg-[var(--c-lt)]/40 transition-colors border border-[var(--c-lt)]/30" title="Lihat Resi">
                                        <i data-lucide="eye" class="w-3.5 h-3.5 text-[var(--c-md)]"></i>
                                    </button>
                                    <template x-if="['paid','making','ready'].includes(order.status)">
                                        <button @click="advanceOrderStatus(order)" 
                                                class="h-8 px-3 rounded-lg text-white text-[11px] font-bold flex items-center gap-1.5 transition-colors"
                                                :class="getNextActionColor(order.status)"
                                                :title="getNextActionLabel(order.status)">
                                            <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                                            <span class="hidden sm:inline" x-text="getNextActionLabel(order.status)"></span>
                                        </button>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <div x-show="orders.filter(o => ['paid','making','ready'].includes(o.status)).length === 0"
                     class="p-8 text-center">
                    <div class="w-16 h-16 bg-[var(--c-lt)]/20 rounded-3xl flex items-center justify-center mb-3 mx-auto">
                        <i data-lucide="check-circle" class="w-7 h-7 text-[var(--c-md)]/50"></i>
                    </div>
                    <p class="text-sm font-semibold text-[var(--c-dk)]">Semua pesanan selesai! 🎉</p>
                    <p class="text-xs text-[var(--c-md)]/60 mt-1">Tidak ada pesanan aktif saat ini.</p>
                </div>
            </div>
        </div>

        {{-- ======= ORDERS PAGE ======= --}}
        <div x-show="activePage === 'orders'" class="space-y-5">
            {{-- Filter Bar --}}
            <div class="bg-white rounded-2xl border border-[var(--c-lt)]/30 p-4 shadow-sm flex flex-wrap gap-3">
                <div class="relative flex-1 min-w-[200px] max-w-xs">
                    <i data-lucide="search" class="w-4 h-4 text-[var(--c-md)]/50 absolute left-3 top-1/2 -translate-y-1/2"></i>
                    <input type="text" x-model="searchQuery" placeholder="Cari order ID, meja, atau pelanggan..." class="input-field w-full text-xs py-2.5 pl-10">
                </div>
                <div class="flex gap-2 flex-wrap">
                    <template x-for="f in [
                        {id: 'all', label: 'Semua'},
                        {id: 'paid', label: 'Diproses'},
                        {id: 'making', label: 'Dibuat'},
                        {id: 'ready', label: 'Siap'},
                        {id: 'done', label: 'Selesai'}
                    ]" :key="f.id">
                        <button @click="filterStatus = f.id" 
                                :class="filterStatus === f.id ? 'category-chip-active' : 'category-chip-inactive'"
                                class="category-chip text-xs py-1.5" x-text="f.label"></button>
                    </template>
                </div>
            </div>

            {{-- Orders Table --}}
            <div class="bg-white rounded-2xl border border-[var(--c-lt)]/30 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-[var(--c-lt)]/20 flex items-center justify-between">
                    <div>
                        <h3 class="font-bold text-[var(--c-dk)] text-sm">Semua Order</h3>
                        <p class="text-xs text-[var(--c-md)]/60 mt-0.5" x-text="filteredOrders.length + ' pesanan ditemukan'"></p>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[700px]">
                        <thead class="bg-[var(--c-bg)]/50">
                            <tr>
                                <th class="table-header text-left">Order ID</th>
                                <th class="table-header text-left">Pelanggan</th>
                                <th class="table-header text-left">Meja</th>
                                <th class="table-header text-left">Item</th>
                                <th class="table-header text-left">Total</th>
                                <th class="table-header text-left">Status</th>
                                <th class="table-header text-left">Waktu</th>
                                <th class="table-header text-left">Aksi</th>
                            </tr>
                        </thead>
                        <tbody x-data class="divide-y divide-[var(--c-lt)]/10">
                            <template x-for="order in filteredOrders" :key="order.id">
                                <tr class="hover:bg-[var(--c-bg)]/30 transition-colors duration-150">
                                    <td class="table-cell font-mono font-bold text-[var(--c-dk)] text-xs" x-text="order.id"></td>
                                    <td class="table-cell text-xs text-[var(--c-dk)]" x-text="order.customer_name || '-'"></td>
                                    <td class="table-cell">
                                        <span class="bg-[var(--c-lt)]/30 text-[var(--c-dk)] font-bold text-xs px-2 py-1 rounded-lg" x-text="'Meja ' + order.table"></span>
                                    </td>
                                    <td class="table-cell text-xs text-[var(--c-md)]/70" x-text="order.items_count + ' item'"></td>
                                    <td class="table-cell font-bold text-[var(--c-dk)] text-xs" x-text="'Rp ' + order.total.toLocaleString('id-ID')"></td>
                                    <td class="table-cell">
                                        <span class="badge text-[10px] px-2.5 py-1"
                                              :class="getStatusClass(order.status)"
                                              x-text="getStatusLabel(order.status)"></span>
                                    </td>
                                    <td class="table-cell text-xs text-[var(--c-md)]/50" x-text="order.time"></td>
                                    <td class="table-cell">
                                        <div class="flex items-center gap-1.5">
                                            <button @click="openReceipt(order)" class="w-7 h-7 rounded-lg bg-[var(--c-bg)] flex items-center justify-center hover:bg-[var(--c-lt)]/40 transition-colors" title="Lihat Resi">
                                                <i data-lucide="eye" class="w-3.5 h-3.5 text-[var(--c-md)]"></i>
                                            </button>
                                            
                                            <template x-if="order.status === 'paid'">
                                                <button @click="advanceOrderStatus(order)" class="w-7 h-7 rounded-lg bg-blue-50 flex items-center justify-center hover:bg-blue-100 transition-colors" title="Mulai Buat">
                                                    <i data-lucide="play" class="w-3.5 h-3.5 text-blue-600"></i>
                                                </button>
                                            </template>
                                            <template x-if="order.status === 'making'">
                                                <button @click="advanceOrderStatus(order)" class="w-7 h-7 rounded-lg bg-purple-50 flex items-center justify-center hover:bg-purple-100 transition-colors" title="Tandai Siap">
                                                    <i data-lucide="bell" class="w-3.5 h-3.5 text-purple-600"></i>
                                                </button>
                                            </template>
                                            <template x-if="order.status === 'ready'">
                                                <button @click="advanceOrderStatus(order)" class="w-7 h-7 rounded-lg bg-green-50 flex items-center justify-center hover:bg-green-100 transition-colors" title="Selesaikan">
                                                    <i data-lucide="check-check" class="w-3.5 h-3.5 text-green-600"></i>
                                                </button>
                                            </template>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                            <tr x-show="filteredOrders.length === 0">
                                <td colspan="8" class="table-cell text-center text-xs text-[var(--c-md)]/60 py-8">
                                    Tidak ada pesanan ditemukan.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- ======= ANALYTICS / LAPORAN PAGE ======= --}}
        <div x-show="activePage === 'analytics'" x-data="kasirAnalyticsApp()"
             x-init="init(); $watch('activeChart', () => { if (!loading && Object.keys(analyticsData).length) renderCharts(); });"
             @load-kasir-analytics.window="loadData()"
             class="space-y-5" x-cloak>

            {{-- Filter Bar --}}
            <div class="bg-white rounded-2xl border border-[var(--c-lt)]/30 p-4 shadow-sm">
                <div class="flex flex-wrap items-center gap-3">
                    <div class="flex gap-2 flex-wrap flex-1">
                        <template x-for="r in ranges" :key="r.value">
                            <button @click="setRange(r.value)"
                                    :class="activeRange === r.value ? 'category-chip category-chip-active' : 'category-chip category-chip-inactive'"
                                    class="category-chip text-xs py-1.5" x-text="r.label"></button>
                        </template>
                    </div>
                    {{-- Custom date --}}
                    <div x-show="activeRange === 'custom'" class="flex items-center gap-2" x-cloak>
                        <input type="date" x-model="customFrom" class="input-field text-xs py-1.5 px-3" style="min-width:130px;">
                        <span class="text-xs text-[var(--c-md)]">s/d</span>
                        <input type="date" x-model="customTo" class="input-field text-xs py-1.5 px-3" style="min-width:130px;">
                        <button @click="loadData()" class="btn-primary text-xs py-1.5 px-3">Terapkan</button>
                    </div>
                    {{-- Export --}}
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
            <div x-show="loading" class="flex items-center justify-center py-16">
                <div class="flex flex-col items-center gap-3">
                    <div class="w-10 h-10 border-4 border-[var(--c-lt)] border-t-[var(--c-dk)] rounded-full animate-spin"></div>
                    <p class="text-sm text-[var(--c-md)]/60">Memuat data laporan...</p>
                </div>
            </div>

            {{-- Data Section: pakai visibility (bukan display:none) agar canvas punya dimensi saat Chart.js render --}}
            <div class="space-y-5" :style="loading ? 'visibility:hidden;pointer-events:none' : 'visibility:visible'">
                {{-- Summary Cards --}}
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="stat-card bg-[var(--c-dk)] border-none">
                        <div class="flex items-start justify-between mb-3">
                            <div class="w-9 h-9 bg-white/15 rounded-xl flex items-center justify-center">
                                <i data-lucide="banknote" class="w-4 h-4 text-white opacity-90"></i>
                            </div>
                        </div>
                        <p class="text-lg font-extrabold text-white" x-text="'Rp ' + (analyticsData.summary?.total_revenue || 0).toLocaleString('id-ID')"></p>
                        <p class="text-[10px] text-white opacity-70 mt-1">Total Pendapatan</p>
                    </div>
                    <div class="stat-card bg-[var(--c-md)] border-none">
                        <div class="flex items-start justify-between mb-3">
                            <div class="w-9 h-9 bg-white/15 rounded-xl flex items-center justify-center">
                                <i data-lucide="receipt" class="w-4 h-4 text-white opacity-90"></i>
                            </div>
                        </div>
                        <p class="text-lg font-extrabold text-white" x-text="(analyticsData.summary?.total_orders || 0)"></p>
                        <p class="text-[10px] text-white opacity-70 mt-1">Total Order</p>
                    </div>
                    <div class="stat-card bg-[var(--c-bg)] border border-[var(--c-lt)]/30">
                        <div class="flex items-start justify-between mb-3">
                            <div class="w-9 h-9 bg-[var(--c-lt)]/20 rounded-xl flex items-center justify-center">
                                <i data-lucide="trending-up" class="w-4 h-4 text-[var(--c-dk)] opacity-90"></i>
                            </div>
                        </div>
                        <p class="text-lg font-extrabold text-[var(--c-dk)]" x-text="'Rp ' + (analyticsData.summary?.avg_order_value || 0).toLocaleString('id-ID')"></p>
                        <p class="text-[10px] text-[var(--c-dk)] opacity-70 mt-1">Rata-rata / Order</p>
                    </div>
                    <div class="stat-card bg-[var(--c-bg)] border border-[var(--c-lt)]/30">
                        <div class="flex items-start justify-between mb-3">
                            <div class="w-9 h-9 bg-[var(--c-lt)]/20 rounded-xl flex items-center justify-center">
                                <i data-lucide="coffee" class="w-4 h-4 text-[var(--c-dk)] opacity-90"></i>
                            </div>
                        </div>
                        <p class="text-lg font-extrabold text-[var(--c-dk)]" x-text="(analyticsData.summary?.total_items || 0)"></p>
                        <p class="text-[10px] text-[var(--c-dk)] opacity-70 mt-1">Total Item Terjual</p>
                    </div>
                </div>

                {{-- Revenue Chart --}}
                <div class="bg-white rounded-2xl border border-[var(--c-lt)]/30 p-5 shadow-sm">
                    <div class="flex items-center justify-between mb-5">
                        <div>
                            <h3 class="font-bold text-[var(--c-dk)] text-sm">Grafik Pendapatan</h3>
                            <p class="text-xs text-[var(--c-md)]/60 mt-0.5">Tren penjualan berdasarkan periode</p>
                        </div>
                        <div class="flex gap-2">
                            <button @click="activeChart='revenue'" :class="activeChart==='revenue' ? 'category-chip-active' : 'category-chip-inactive'" class="category-chip text-[10px] px-3 py-1">Pendapatan</button>
                            <button @click="activeChart='orders'" :class="activeChart==='orders' ? 'category-chip-active' : 'category-chip-inactive'" class="category-chip text-[10px] px-3 py-1">Jumlah Order</button>
                        </div>
                    </div>
                    <div class="w-full h-56 relative">
                        <canvas id="kasirAnalyticsMainChart"></canvas>
                    </div>
                </div>

                {{-- Top Menu + Peak Hours --}}
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                    {{-- Top Menu --}}
                    <div class="bg-white rounded-2xl border border-[var(--c-lt)]/30 p-5 shadow-sm">
                        <h3 class="font-bold text-[var(--c-dk)] text-sm mb-4">Menu Terlaris</h3>
                        <div class="space-y-3 max-h-64 overflow-y-auto pr-1">
                            <template x-for="(m, idx) in (analyticsData.top_menus || [])" :key="m.name">
                                <div class="flex items-center gap-3">
                                    <div class="w-6 h-6 rounded-lg flex items-center justify-center text-[10px] font-extrabold text-white shrink-0"
                                         :style="idx === 0 ? 'background:var(--c-dk)' : idx === 1 ? 'background:var(--c-md)' : idx === 2 ? 'background:var(--c-md-lt)' : 'background:var(--c-lt)'"
                                         x-text="idx + 1"></div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex justify-between items-center mb-1">
                                            <span class="text-xs font-semibold text-[var(--c-dk)] truncate" x-text="m.name"></span>
                                            <span class="text-[10px] text-[var(--c-md)]/70 shrink-0 ml-2" x-text="m.qty + 'x'"></span>
                                        </div>
                                        <div class="h-1.5 bg-[var(--c-lt)]/20 rounded-full overflow-hidden">
                                            <div class="h-full bg-[var(--c-dk)] rounded-full"
                                                 :style="`width:${analyticsData.top_menus?.length ? Math.round((m.qty / analyticsData.top_menus[0].qty) * 100) : 0}%`"></div>
                                        </div>
                                        <div class="text-[10px] text-[var(--c-md)]/60 mt-0.5" x-text="'Rp ' + m.revenue.toLocaleString('id-ID')"></div>
                                    </div>
                                </div>
                            </template>
                            <div x-show="!analyticsData.top_menus?.length" class="text-center text-xs text-[var(--c-md)]/60 py-6">
                                Belum ada data
                            </div>
                        </div>
                    </div>

                    {{-- Peak Hours --}}
                    <div class="bg-white rounded-2xl border border-[var(--c-lt)]/30 p-5 shadow-sm">
                        <h3 class="font-bold text-[var(--c-dk)] text-sm mb-4">Jam Sibuk</h3>
                        <div class="w-full h-56 relative">
                            <canvas id="kasirAnalyticsPeakChart"></canvas>
                        </div>
                    </div>
                </div>

                {{-- Daily Breakdown Table --}}
                <div class="bg-white rounded-2xl border border-[var(--c-lt)]/30 shadow-sm overflow-hidden">
                    <div class="px-5 py-4 border-b border-[var(--c-lt)]/20">
                        <h3 class="font-bold text-[var(--c-dk)] text-sm">Rincian Per Periode</h3>
                        <p class="text-xs text-[var(--c-md)]/60 mt-0.5">Pendapatan dan jumlah order per periode</p>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[400px]">
                            <thead class="bg-[var(--c-bg)]/50">
                                <tr>
                                    <th class="table-header text-left">Periode</th>
                                    <th class="table-header text-right">Jumlah Order</th>
                                    <th class="table-header text-right">Pendapatan</th>
                                    <th class="table-header text-right">Rata-rata</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[var(--c-lt)]/10">
                                <template x-for="row in (analyticsData.daily_breakdown || [])" :key="row.period">
                                    <tr class="hover:bg-[var(--c-bg)]/30 transition-colors" x-show="row.orders > 0">
                                        <td class="table-cell font-semibold text-[var(--c-dk)] text-xs" x-text="row.period"></td>
                                        <td class="table-cell text-right text-xs text-[var(--c-md)]" x-text="row.orders + ' order'"></td>
                                        <td class="table-cell text-right font-bold text-[var(--c-dk)] text-xs" x-text="'Rp ' + row.revenue.toLocaleString('id-ID')"></td>
                                        <td class="table-cell text-right text-xs text-[var(--c-md)]" x-text="row.orders > 0 ? 'Rp ' + Math.round(row.revenue / row.orders).toLocaleString('id-ID') : '-'"></td>
                                    </tr>
                                </template>
                                <tr x-show="!(analyticsData.daily_breakdown || []).some(r => r.orders > 0)">
                                    <td colspan="4" class="table-cell text-center text-xs text-[var(--c-md)]/60 py-6">Tidak ada data untuk periode ini.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </main>

    {{-- BOTTOM BAR --}}
    <footer class="border-t border-[var(--c-lt)]/30 px-6 py-3 bg-white">
        <p class="text-xs text-[var(--c-md)]/50 text-center">© {{ date('Y') }} Skena Coffee Kasir Panel · v1.0.0</p>
    </footer>
</div>

{{-- ======= RECEIPT MODAL ======= --}}
<template x-teleport="body">
<div x-show="showReceiptModal" x-cloak
     class="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-black/65"
     @click.self="showReceiptModal = false"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-[400px] max-h-[90vh] flex flex-col m-auto"
         @click.stop
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-8 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 translate-y-8 scale-95">
         
         <div class="p-5 text-center border-b border-dashed border-[var(--c-lt)]">
             <h2 class="font-bold text-xl text-[var(--c-dk)]">Skena Coffee</h2>
             <p class="text-xs text-[var(--c-md)]/60 mt-1" x-text="'Resi Order #' + (selectedOrder?.id || '')"></p>
         </div>

         <div class="p-5 flex-1 overflow-y-auto print:overflow-visible">
             <div class="space-y-2 mb-5">
                 <div class="flex justify-between text-sm">
                     <span class="text-[var(--c-md)]/70">Waktu</span>
                     <span class="font-medium text-[var(--c-dk)]" x-text="selectedOrder?.date_formatted"></span>
                 </div>
                 <div class="flex justify-between text-sm">
                     <span class="text-[var(--c-md)]/70">Pelanggan</span>
                     <span class="font-medium text-[var(--c-dk)]" x-text="selectedOrder?.customer_name || '-'"></span>
                 </div>
                 <div class="flex justify-between text-sm">
                     <span class="text-[var(--c-md)]/70">Meja</span>
                     <span class="font-medium text-[var(--c-dk)]" x-text="selectedOrder?.table"></span>
                 </div>
                 <div class="flex justify-between text-sm">
                     <span class="text-[var(--c-md)]/70">Pembayaran</span>
                     <span class="font-medium text-[var(--c-dk)] uppercase" x-text="selectedOrder?.payment_method || 'QRIS'"></span>
                 </div>
                 <div class="flex justify-between text-sm">
                     <span class="text-[var(--c-md)]/70">Status</span>
                     <span class="badge text-[10px] px-2.5 py-1"
                           :class="getStatusClass(selectedOrder?.status)"
                           x-text="getStatusLabel(selectedOrder?.status)"></span>
                 </div>
             </div>

             <div class="border-t border-b border-dashed border-[var(--c-lt)] py-4 mb-4 space-y-3">
                 <template x-if="selectedOrder?.items_detail">
                     <template x-for="item in selectedOrder.items_detail" :key="item.name">
                         <div class="flex justify-between text-sm">
                             <div class="flex-1 pr-2">
                                 <p class="font-semibold text-[var(--c-dk)]" x-text="item.name"></p>
                                 <p class="text-xs text-[var(--c-md)]/60" x-text="item.qty + 'x @ Rp ' + (item.price || 0).toLocaleString('id-ID')"></p>
                             </div>
                             <span class="font-medium text-[var(--c-dk)]" x-text="'Rp ' + ((item.price || 0) * (item.qty || 1)).toLocaleString('id-ID')"></span>
                         </div>
                     </template>
                 </template>
             </div>

             <div class="space-y-1">
                 <div class="flex justify-between text-sm text-[var(--c-md)]/70">
                     <span>Subtotal</span>
                     <span x-text="'Rp ' + (selectedOrder?.subtotal || 0).toLocaleString('id-ID')"></span>
                 </div>
                 <div class="flex justify-between text-sm text-[var(--c-md)]/70">
                     <span>Pajak (10%)</span>
                     <span x-text="'Rp ' + (selectedOrder?.tax || 0).toLocaleString('id-ID')"></span>
                 </div>
                 <div class="flex justify-between text-lg font-bold text-[var(--c-dk)] pt-2 mt-2 border-t border-[var(--c-lt)]/30">
                     <span>Total</span>
                     <span x-text="'Rp ' + (selectedOrder?.total || 0).toLocaleString('id-ID')"></span>
                 </div>
             </div>
         </div>

         <div class="p-4 bg-[var(--c-bg)]/30 rounded-b-2xl border-t border-[var(--c-lt)]/20 flex gap-3">
             <button @click="printReceipt()" class="flex-1 btn-primary justify-center py-2.5 text-sm flex items-center gap-2">
                 <i data-lucide="printer" class="w-4 h-4"></i> Cetak
             </button>
             <button @click="showReceiptModal = false" class="flex-1 btn-outline justify-center py-2.5 text-sm">Tutup</button>
         </div>
    </div>
</div>
</template>

<script>
    document.addEventListener('DOMContentLoaded', () => { 
        lucide.createIcons(); 
        
        // Fetch computed CSS variables for Chart.js
        const style = getComputedStyle(document.body);
        const cDk = style.getPropertyValue('--c-dk').trim();
        const cMd = style.getPropertyValue('--c-md').trim();
        const cLt = style.getPropertyValue('--c-lt').trim();
        
        // Initialize Sales Chart
        const salesCtx = document.getElementById('kasirSalesChart');
        if (salesCtx) {
            new Chart(salesCtx, {
                type: 'line',
                data: {
                    labels: {{ Illuminate\Support\Js::from($salesLabels) }},
                    datasets: [{
                        label: 'Penjualan (Juta Rp)',
                        data: {{ Illuminate\Support\Js::from($salesData) }},
                        borderColor: cMd,
                        backgroundColor: 'transparent',
                        fill: false,
                        tension: 0.4,
                        borderWidth: 3,
                        pointBackgroundColor: '#fff',
                        pointBorderColor: cMd,
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: (ctx) => {
                                    const val = ctx.raw * 1000000;
                                    return 'Rp ' + (val >= 1000000 ? (val/1000000).toFixed(1)+'jt' : val.toLocaleString('id-ID'));
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            border: { display: false },
                            grid: { color: cLt, drawBorder: false },
                            ticks: {
                                color: cMd,
                                font: { size: 10 },
                                callback: v => v >= 1 ? v.toFixed(1)+'jt' : (v > 0 ? (v*1000).toFixed(0)+'k' : 0)
                            }
                        },
                        x: {
                            border: { display: false },
                            grid: { display: false },
                            ticks: { color: cMd, font: { size: 10 } }
                        }
                    }
                }
            });
        }
    });

    const observer = new MutationObserver(() => {
        observer.disconnect();
        lucide.createIcons();
        observer.observe(document.body, { childList: true, subtree: true });
    });
    observer.observe(document.body, { childList: true, subtree: true });
</script>

<script>
// ─── Analytics Alpine Component for Kasir ─────────────────────────────────────
function kasirAnalyticsApp() {
    return {
        loading: false,
        activeRange: '7',
        activeChart: 'revenue',
        customFrom: '',
        customTo: '',
        analyticsData: {},
        mainChart: null,
        peakChart: null,

        ranges: [
            { value: '7',    label: '7 Hari' },
            { value: '30',   label: '30 Hari' },
            { value: '90',   label: '3 Bulan' },
            { value: '365',  label: '1 Tahun' },
            { value: 'custom', label: 'Custom' },
        ],

        get csvUrl() {
            return this._buildUrl('/kasir/api/analytics/export-csv');
        },
        get pdfUrl() {
            return this._buildUrl('/kasir/api/analytics/export-pdf');
        },

        _buildUrl(base) {
            let url = base + '?range=' + this.activeRange;
            if (this.activeRange === 'custom') {
                url += '&date_from=' + this.customFrom + '&date_to=' + this.customTo;
            }
            return url;
        },

        init() {
            this.loadData();
        },

        setRange(val) {
            this.activeRange = val;
            if (val !== 'custom') this.loadData();
        },

        async loadData() {
            this.loading = true;
            try {
                const url = this._buildUrl('/kasir/api/analytics/data');
                const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
                this.analyticsData = await res.json();
                this.loading = false;
                // $nextTick + 200ms agar DOM update visibility selesai sebelum chart render
                this.$nextTick(() => {
                    setTimeout(() => this.renderCharts(), 200);
                });
            } catch (e) {
                console.error('Analytics load error:', e);
                this.loading = false;
            }
        },

        renderCharts() {
            const style = getComputedStyle(document.body);
            // Fallback warna hardcode jika CSS vars belum terbaca
            const cDk = style.getPropertyValue('--c-dk').trim() || '#1a3c34';
            const cMd = style.getPropertyValue('--c-md').trim() || '#2d6a5e';
            const cLt = style.getPropertyValue('--c-lt').trim() || '#84c5b4';

            // Destroy chart lama dengan Chart.getChart() untuk bebaskan canvas context
            ['kasirAnalyticsMainChart', 'kasirAnalyticsPeakChart'].forEach(id => {
                const ex = Chart.getChart(id);
                if (ex) ex.destroy();
            });
            if (this.mainChart) { try { this.mainChart.destroy(); } catch(e) {} this.mainChart = null; }
            if (this.peakChart) { try { this.peakChart.destroy(); } catch(e) {} this.peakChart = null; }

            // ── Main Chart ──
            const mainCtx = document.getElementById('kasirAnalyticsMainChart');
            if (mainCtx) {
                try {
                const isRevenue = this.activeChart === 'revenue';
                const chartData = this.analyticsData.chart || { labels: [], revenue: [], orders: [] };

                this.mainChart = new Chart(mainCtx, {
                    type: 'line',
                    data: {
                        labels: chartData.labels,
                        datasets: [{
                            label: isRevenue ? 'Pendapatan (Rp)' : 'Jumlah Order',
                            data: isRevenue ? chartData.revenue : chartData.orders,
                            borderColor: isRevenue ? cMd : cDk,
                            backgroundColor: 'transparent',
                            fill: false,
                            tension: 0.4,
                            borderWidth: 3,
                            pointBackgroundColor: '#fff',
                            pointBorderColor: isRevenue ? cMd : cDk,
                            pointBorderWidth: 2,
                            pointRadius: 4,
                            pointHoverRadius: 6
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label: (ctx) => isRevenue
                                        ? 'Rp ' + ctx.raw.toLocaleString('id-ID')
                                        : ctx.raw + ' order'
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                border: { display: false },
                                grid: { color: cLt, drawBorder: false },
                                ticks: {
                                    color: cMd,
                                    font: { size: 10 },
                                    callback: (v) => isRevenue
                                        ? (v >= 1000000 ? (v/1000000).toFixed(1) + 'jt' : (v >= 1000 ? (v/1000).toFixed(0) + 'k' : v))
                                        : v
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
                } catch(err) { console.error('Kasir main chart error:', err); }
            }

            // ── Peak Hours Chart ──
            const peakCtx = document.getElementById('kasirAnalyticsPeakChart');
            if (peakCtx) {
                try {
                const peak = this.analyticsData.peak_hours || { labels: [], data: [] };
                const maxPeak = Math.max(...peak.data, 1);
                this.peakChart = new Chart(peakCtx, {
                    type: 'bar',
                    data: {
                        labels: peak.labels,
                        datasets: [{
                            label: 'Jumlah Order',
                            data: peak.data,
                            backgroundColor: peak.data.map(v => v === maxPeak && maxPeak > 0 ? cDk : cLt + '90'),
                            borderRadius: 6,
                            maxBarThickness: 40
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            y: {
                                beginAtZero: true,
                                border: { display: false },
                                grid: { color: cLt, drawBorder: false },
                                ticks: { color: cMd, font: { size: 10 }, precision: 0, stepSize: 1 }
                            },
                            x: {
                                border: { display: false },
                                grid: { display: false },
                                ticks: { color: cMd, font: { size: 9 } }
                            }
                        }
                    }
                });
                } catch(err) { console.error('Kasir peak chart error:', err); }
            }
            // Force resize agar Chart.js recalculate dimensi canvas
            setTimeout(() => window.dispatchEvent(new Event('resize')), 50);
        },
    };
}

// Listen for Alpine page changes and trigger analytics data reload when switching to analytics tab
document.addEventListener('DOMContentLoaded', () => {
    document.body.addEventListener('click', (e) => {
        const btn = e.target.closest('button[id^="kasir-nav-analytics"]');
        if (btn) {
            setTimeout(() => {
                window.dispatchEvent(new CustomEvent('load-kasir-analytics'));
            }, 150);
        }
    });
});
</script>

</body>
</html>
