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
    <title>Admin Dashboard — Skena Coffee</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

</head>
<body class="font-sans bg-[var(--c-bg)]/50 text-[var(--c-dk)] antialiased" :data-theme="theme" x-data="{
    activePage: 'dashboard',
    sidebarOpen: false,
    theme: localStorage.getItem('theme') || 'green',
    orders: {{ Illuminate\Support\Js::from($orders) }},
    stats: {{ Illuminate\Support\Js::from($stats) }},
    salesLabels: {{ Illuminate\Support\Js::from($salesLabels) }},
    salesData: {{ Illuminate\Support\Js::from($salesData) }},
    visitLabels: {{ Illuminate\Support\Js::from($visitLabels) }},
    visitData: {{ Illuminate\Support\Js::from($visitData) }},
    topMenus: {{ Illuminate\Support\Js::from($topMenus) }},
    categoryChart: {{ Illuminate\Support\Js::from($categoryChart) }},
    categories: {{ Illuminate\Support\Js::from($categories) }},
    showCategoryModal: false,
    showCategoryDeleteConfirm: false,
    categoryToDelete: null,
    categoryForm: { id: null, name: '', sort_order: 0 },

    openAddCategory() {
        this.categoryForm = { id: null, name: '', sort_order: 0 };
        this.showCategoryModal = true;
    },
    openEditCategory(cat) {
        this.categoryForm = { ...cat };
        this.showCategoryModal = true;
    },
    confirmDeleteCategory(cat) {
        this.categoryToDelete = cat;
        this.showCategoryDeleteConfirm = true;
    },
    async saveCategory() {
        let url = '/admin/api/categories';
        let method = 'POST';
        if (this.categoryForm.id) {
            url += '/' + this.categoryForm.id;
            method = 'PUT';
        }
        
        try {
            const response = await fetch(url, {
                method: method,
                headers: { 
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(this.categoryForm)
            });
            const data = await response.json();
            if (response.ok) {
                if (this.categoryForm.id) {
                    const index = this.categories.findIndex(c => c.id === this.categoryForm.id);
                    if (index !== -1) this.categories[index] = data.category;
                } else {
                    this.categories.push(data.category);
                }
                this.categories.sort((a,b) => a.sort_order - b.sort_order);
                this.showCategoryModal = false;
                alert(data.message);
            } else {
                alert('Error: ' + (data.message || 'Gagal menyimpan kategori'));
            }
        } catch(e) {
            console.error(e);
            alert('Kesalahan jaringan');
        }
    },
    async deleteCategory() {
        if (!this.categoryToDelete) return;
        try {
            const response = await fetch('/admin/api/categories/' + this.categoryToDelete.id, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            });
            const data = await response.json();
            if (response.ok) {
                this.categories = this.categories.filter(c => c.id !== this.categoryToDelete.id);
                this.showCategoryDeleteConfirm = false;
                this.categoryToDelete = null;
                alert(data.message);
            } else {
                alert('Error: ' + (data.message || 'Gagal menghapus kategori'));
            }
        } catch (e) {
            console.error(e);
            alert('Kesalahan jaringan');
        }
    },

    menus: {{ Illuminate\Support\Js::from($menus) }},
    menuSearch: '',
    get filteredMenus() {
        if (!this.menuSearch.trim()) return this.menus;
        const q = this.menuSearch.trim().toLowerCase();
        return this.menus.filter(m =>
            m.name.toLowerCase().includes(q) ||
            (m.category && m.category.name.toLowerCase().includes(q))
        );
    },
    showMenuModal: false,
    showMenuDeleteConfirm: false,
    menuToDelete: null,
    menuForm: { id: null, name: '', description: '', category_id: '', price: '', has_hot: false, price_hot: '', desc_hot: '', has_ice: false, price_ice: '', desc_ice: '', tag: '', is_available: true, image: null },
    
    openAddMenu() {
        this.menuForm = { id: null, name: '', description: '', category_id: this.categories.length > 0 ? this.categories[0].id : '', price: '', has_hot: false, price_hot: '', desc_hot: '', has_ice: false, price_ice: '', desc_ice: '', tag: '', is_available: true, image: null };
        if (this.$refs.imageInput) this.$refs.imageInput.value = '';
        this.showMenuModal = true;
    },
    openEditMenu(menu) {
        this.menuForm = { ...menu, category_id: menu.category_id, image: null }; // Reset image file input, keep old data
        if (this.$refs.imageInput) this.$refs.imageInput.value = '';
        this.showMenuModal = true;
    },
    confirmDeleteMenu(menu) {
        this.menuToDelete = menu;
        this.showMenuDeleteConfirm = true;
    },
    async saveMenu() {
        const formData = new FormData();
        formData.append('name', this.menuForm.name);
        formData.append('description', this.menuForm.description || '');
        formData.append('category_id', this.menuForm.category_id);
        
        // Append variant logic
        formData.append('has_hot', this.menuForm.has_hot ? '1' : '0');
        if (this.menuForm.has_hot) {
            formData.append('price_hot', this.menuForm.price_hot);
            formData.append('desc_hot', this.menuForm.desc_hot || '');
        }
        formData.append('has_ice', this.menuForm.has_ice ? '1' : '0');
        if (this.menuForm.has_ice) {
            formData.append('price_ice', this.menuForm.price_ice);
            formData.append('desc_ice', this.menuForm.desc_ice || '');
        }
        
        // Only append regular price if variants aren't both checked (or just append it anyway, backend accepts nullable)
        formData.append('price', this.menuForm.price || '0');

        formData.append('tag', this.menuForm.tag || '');
        formData.append('is_available', this.menuForm.is_available ? '1' : '0');
        if (this.menuForm.image instanceof File) {
            formData.append('image', this.menuForm.image);
        }

        let url = '/admin/api/menus';
        if (this.menuForm.id) {
            url += '/' + this.menuForm.id;
            formData.append('_method', 'POST'); // Use POST with _method=POST because of file upload handling in Laravel if we used PUT, but wait, my route is POST for update already. Let me fix route in web.php. Ah, I defined it as POST.
        }

        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: { 
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: formData
            });
            const data = await response.json();
            if (response.ok) {
                if (this.menuForm.id) {
                    const index = this.menus.findIndex(m => m.id === this.menuForm.id);
                    if (index !== -1) this.menus[index] = data.menu;
                } else {
                    this.menus.push(data.menu);
                }
                this.showMenuModal = false;
                alert(data.message);
            } else {
                let errorMsg = data.message || 'Gagal menyimpan menu';
                if (data.errors) {
                    const firstError = Object.values(data.errors)[0][0];
                    if (firstError) errorMsg += ' - ' + firstError;
                }
                alert('Error: ' + errorMsg);
            }
        } catch (e) {
            console.error(e);
            alert('Terjadi kesalahan jaringan');
        }
    },
    async deleteMenu() {
        if (!this.menuToDelete) return;
        try {
            const response = await fetch('/admin/api/menus/' + this.menuToDelete.id, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            });
            if (response.ok) {
                this.menus = this.menus.filter(m => m.id !== this.menuToDelete.id);
                this.showMenuDeleteConfirm = false;
                this.menuToDelete = null;
            }
        } catch (e) {
            console.error(e);
            alert('Gagal menghapus menu');
        }
    },
    async toggleAvailability(item) {
        const newVal = !item.is_available;
        const formData = new FormData();
        formData.append('name', item.name);
        formData.append('description', item.description || '');
        formData.append('category_id', item.category_id);
        formData.append('has_hot', item.has_hot ? '1' : '0');
        formData.append('has_ice', item.has_ice ? '1' : '0');
        formData.append('price', item.price || '0');
        formData.append('price_hot', item.price_hot || '0');
        formData.append('price_ice', item.price_ice || '0');
        formData.append('tag', item.tag || '');
        formData.append('is_available', newVal ? '1' : '0');
        try {
            const response = await fetch('/admin/api/menus/' + item.id, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: formData
            });
            const data = await response.json();
            if (response.ok) {
                const index = this.menus.findIndex(m => m.id === item.id);
                if (index !== -1) this.menus[index] = data.menu;
            } else {
                alert('Gagal mengubah status: ' + (data.message || ''));
            }
        } catch (e) {
            console.error(e);
            alert('Terjadi kesalahan jaringan');
        }
    },
    tables: {{ Illuminate\Support\Js::from($tables) }},
    tablesLoading: false,
    async fetchTables() {
        try {
            const res = await fetch('/admin/api/tables', {
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
            });
            if (res.ok) {
                this.tables = await res.json();
            }
        } catch(e) { console.error('Gagal memuat meja:', e); }
    },
    showAddModal: false,
    showQrModal: false,
    showDeleteConfirm: false,
    selectedTable: null,
    tableToDelete: null,
    newTableNumber: '',
    newTableCapacity: '4',
    newTableName: '',
    async addTable() {
        if (!this.newTableNumber) return;
        const num = parseInt(this.newTableNumber);
        try {
            const res = await fetch('/admin/api/tables', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    number: num,
                    name: this.newTableName || 'Meja ' + num,
                    capacity: parseInt(this.newTableCapacity) || 4
                })
            });
            const data = await res.json();
            if (res.ok) {
                this.tables.push(data.table);
                this.tables.sort((a, b) => a.number - b.number);
                this.newTableNumber = '';
                this.newTableName = '';
                this.newTableCapacity = '4';
                this.showAddModal = false;
            } else {
                alert('Error: ' + (data.message || 'Gagal menambah meja'));
            }
        } catch(e) {
            console.error(e);
            alert('Kesalahan jaringan');
        }
    },
    confirmDelete(table) {
        this.tableToDelete = table;
        this.showDeleteConfirm = true;
    },
    async deleteTable() {
        if (!this.tableToDelete) return;
        try {
            const res = await fetch('/admin/api/tables/' + this.tableToDelete.id, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
            });
            if (res.ok) {
                this.tables = this.tables.filter(t => t.id !== this.tableToDelete.id);
                this.showDeleteConfirm = false;
                this.tableToDelete = null;
            } else {
                const data = await res.json();
                alert('Error: ' + (data.message || 'Gagal menghapus meja'));
            }
        } catch(e) {
            console.error(e);
            alert('Kesalahan jaringan');
        }
    },
    openQr(table) {
        this.selectedTable = table;
        this.showQrModal = true;
        this.$nextTick(() => {
            const el = document.getElementById('qr-canvas');
            el.innerHTML = '';
            const url = '{{ $dynamicBaseUrl }}/scan/' + table.number;
            const cDk = getComputedStyle(document.body).getPropertyValue('--c-dk').trim() || '#1E3830';
            new QRCode(el, {
                text: url,
                width: 220,
                height: 220,
                colorDark: cDk,
                colorLight: '#ffffff',
                correctLevel: QRCode.CorrectLevel.H
            });
        });
    },
    downloadQr() {
        const canvas = document.querySelector('#qr-canvas canvas');
        if (!canvas) return;
        const link = document.createElement('a');
        link.download = 'QR-Meja-' + this.selectedTable.number + '.png';
        link.href = canvas.toDataURL('image/png');
        link.click();
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

    // ── Realtime Polling ────────────────────────────────────────────────
    liveConnected: true,
    lastUpdated: '',
    newOrderAlert: false,
    _pollingTimer: null,

    startPolling() {
        this._pollingTimer = setInterval(async () => {
            try {
                const res = await fetch('/admin/api/live', { headers: { 'Accept': 'application/json' } });
                if (!res.ok) { this.liveConnected = false; return; }
                const data = await res.json();
                this.liveConnected = true;
                this.lastUpdated = data.server_time;

                // Deteksi order baru
                const oldCount = this.orders.length;
                const newCount = data.orders.length;
                const newLatestId = data.orders[0]?.id;
                const oldLatestId = this.orders[0]?.id;
                if (newLatestId && newLatestId !== oldLatestId) {
                    this.newOrderAlert = true;
                    setTimeout(() => { this.newOrderAlert = false; }, 4000);
                }

                // Update state
                this.stats  = data.stats;
                this.orders = data.orders;
            } catch (e) {
                this.liveConnected = false;
                console.warn('Polling error:', e);
            }
        }, 20000); // setiap 20 detik
    }
}" x-init="startPolling(); lastUpdated = new Date().toLocaleTimeString('id-ID', {hour:'2-digit',minute:'2-digit',second:'2-digit'})">

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
                <p class="text-[var(--c-lt)]/60 text-xs mt-0.5">Admin Panel</p>
            </div>
        </div>
    </div>

    {{-- Nav Links --}}
    <nav class="flex-1 px-3 py-5 space-y-1 overflow-y-auto">
        <p class="text-[var(--c-lt)]/40 text-[10px] uppercase tracking-widest px-3 mb-3">Main</p>

        @php
        $navItems = [
            ['key'=>'dashboard',  'label'=>'Dashboard',          'icon'=>'layout-dashboard'],
            ['key'=>'categories', 'label'=>'Manajemen Kategori',  'icon'=>'tags'],
            ['key'=>'menu',       'label'=>'Manajemen Menu',      'icon'=>'coffee'],
            ['key'=>'tables',     'label'=>'Manajemen Meja',      'icon'=>'armchair'],
        ];
        @endphp

        @foreach($navItems as $nav)
        <button @click="activePage = '{{ $nav['key'] }}'; sidebarOpen = false"
                id="nav-{{ $nav['key'] }}"
                :class="activePage === '{{ $nav['key'] }}' ? 'sidebar-link sidebar-link-active' : 'sidebar-link sidebar-link-inactive'"
                class="sidebar-link w-full text-left">
            <i data-lucide="{{ $nav['icon'] }}" class="w-4 h-4 shrink-0"></i>
            <span>{{ $nav['label'] }}</span>
        </button>
        @endforeach

        <div class="pt-4">
            <p class="text-[var(--c-lt)]/40 text-[10px] uppercase tracking-widest px-3 mb-3">Laporan</p>
            @php
            $reportItems = [
                ['key'=>'analytics',  'label'=>'Analytics',   'icon'=>'bar-chart-2'],
                ['key'=>'settings',   'label'=>'Pengaturan',  'icon'=>'settings'],
            ];
            @endphp
            @foreach($reportItems as $nav)
            <button @click="activePage = '{{ $nav['key'] }}'; sidebarOpen = false"
                    :class="activePage === '{{ $nav['key'] }}' ? 'sidebar-link sidebar-link-active' : 'sidebar-link sidebar-link-inactive'"
                    class="sidebar-link w-full text-left">
                <i data-lucide="{{ $nav['icon'] }}" class="w-4 h-4 shrink-0"></i>
                <span>{{ $nav['label'] }}</span>
            </button>
            @endforeach
        </div>
    </nav>

    {{-- User Card --}}
    <div class="px-3 py-4 border-t border-white/10">
        <div class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-white/5 transition-colors">
            <div class="w-8 h-8 rounded-full bg-[var(--c-lt)]/20 flex items-center justify-center text-[var(--c-lt)] font-bold text-sm border border-[var(--c-lt)]/30">A</div>
            <div class="flex-1 min-w-0">
                <p class="text-white font-semibold text-xs truncate">{{ auth('admin')->user()->name ?? 'Admin Skena' }}</p>
                <p class="text-[var(--c-lt)]/50 text-[10px] truncate">{{ auth('admin')->user()->email ?? 'admin@skenacoffee.id' }}</p>
            </div>
            <form method="POST" action="{{ route('admin.logout') }}" class="shrink-0 m-0 p-0 flex">
                @csrf
                <button type="submit" class="bg-transparent border-0 p-1 m-0 flex items-center justify-center group" title="Logout">
                    <i data-lucide="log-out" class="w-4 h-4 text-[var(--c-lt)]/40 group-hover:text-red-400 transition-colors cursor-pointer"></i>
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
                        id="admin-hamburger">
                    <i data-lucide="menu" class="w-4 h-4 text-[var(--c-dk)]"></i>
                </button>
                <div>
                    <h1 class="font-bold text-[var(--c-dk)] text-base" x-text="{
                        dashboard: 'Dashboard',
                        menu: 'Manajemen Menu',
                        categories: 'Manajemen Kategori',
                        tables: 'Manajemen Meja',
                        analytics: 'Analytics',
                        settings: 'Pengaturan'
                    }[activePage] || 'Dashboard'"></h1>
                    <p class="text-xs text-[var(--c-md)]/60">{{ now()->isoFormat('dddd, D MMMM Y') }}</p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                {{-- Notification Bell with Dropdown --}}
                <div x-data="{ notifOpen: false }">
                    <button x-ref="notifBellAdmin" @click="notifOpen = !notifOpen"
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
                             x-effect="if(notifOpen) { const r = $refs.notifBellAdmin.getBoundingClientRect(); $el.style.top = (r.bottom + 8) + 'px'; $el.style.right = (window.innerWidth - r.right) + 'px'; $nextTick(() => lucide.createIcons()); }">

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
                                         @click="activePage = 'dashboard'; notifOpen = false">
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
                {{-- LIVE Indicator + Refresh --}}
                <div class="flex items-center gap-2">
                    <div class="flex items-center gap-1.5 px-2.5 py-1.5 rounded-xl border text-[10px] font-bold uppercase tracking-wide"
                         :class="liveConnected ? 'border-green-200 bg-green-50 text-green-700' : 'border-red-200 bg-red-50 text-red-600'">
                        <span class="w-1.5 h-1.5 rounded-full animate-pulse"
                              :class="liveConnected ? 'bg-green-500' : 'bg-red-500'"></span>
                        <span x-text="liveConnected ? 'LIVE' : 'OFFLINE'"></span>
                    </div>
                    <button @click="window.location.reload()"
                            class="w-9 h-9 rounded-xl border border-[var(--c-lt)] flex items-center justify-center hover:bg-[var(--c-bg)] transition-colors"
                            title="Refresh halaman">
                        <i data-lucide="refresh-cw" class="w-4 h-4 text-[var(--c-dk)]"></i>
                    </button>
                </div>
                {{-- View Site --}}
                <a href="{{ route('home') }}" target="_blank" class="hidden sm:flex btn-outline text-xs py-2">
                    <i data-lucide="external-link" class="w-3 h-3"></i>
                    Lihat Website
                </a>
            </div>

        </div>
    </header>

    {{-- PAGE CONTENT --}}
    <main class="flex-1 p-4 sm:p-6 overflow-auto">

        {{-- ── New Order Alert Banner ── --}}
        <div x-show="newOrderAlert" x-cloak
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 -translate-y-3"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-3"
             class="mb-4 flex items-center gap-3 bg-[var(--c-dk)] text-white text-sm font-semibold px-4 py-3 rounded-2xl shadow-lg">
            <span class="w-2 h-2 rounded-full bg-green-400 animate-ping"></span>
            <span>🔔 Order baru masuk!</span>
            <button @click="newOrderAlert = false" class="ml-auto text-white/60 hover:text-white transition-colors">✕</button>
        </div>

        {{-- ======= DASHBOARD PAGE ======= --}}
        <div x-show="activePage === 'dashboard'" class="space-y-6">

            {{-- Stats Cards --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                {{-- Total Order --}}
                <div class="stat-card bg-[var(--c-dk)] border-none">
                    <div class="flex items-start justify-between mb-4">
                        <div class="w-10 h-10 bg-white/15 rounded-xl flex items-center justify-center">
                            <i data-lucide="receipt" class="w-5 h-5 text-white opacity-90"></i>
                        </div>
                        <span class="text-xs font-medium text-white opacity-70 bg-white/10 px-2 py-0.5 rounded-full">hari ini</span>
                    </div>
                    <p class="text-2xl font-extrabold text-white" x-text="stats.total_order"></p>
                    <p class="text-xs text-white opacity-70 mt-1">Total Order Hari Ini</p>
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
                    <p class="text-xs text-white opacity-70 mt-1">Pendapatan Hari Ini</p>
                </div>
                
                {{-- Pesanan Aktif --}}
                <div class="stat-card bg-[var(--c-bg)] border border-[var(--c-lt)]/30">
                    <div class="flex items-start justify-between mb-4">
                        <div class="w-10 h-10 bg-[var(--c-lt)]/20 rounded-xl flex items-center justify-center">
                            <i data-lucide="coffee" class="w-5 h-5 text-[var(--c-dk)] opacity-90"></i>
                        </div>
                        <span class="text-xs font-medium text-[var(--c-dk)] opacity-70 bg-[var(--c-lt)]/20 px-2 py-0.5 rounded-full">sekarang</span>
                    </div>
                    <p class="text-2xl font-extrabold text-[var(--c-dk)]" x-text="stats.aktif"></p>
                    <p class="text-xs text-[var(--c-dk)] opacity-70 mt-1">Pesanan Aktif</p>
                </div>
            </div>

            {{-- Charts Row --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
                {{-- Sales Chart --}}
                <div class="lg:col-span-2 bg-white rounded-2xl border border-[var(--c-lt)]/30 p-5 shadow-sm">
                    <div class="flex items-center justify-between mb-5">
                        <div>
                            <h3 class="font-bold text-[var(--c-dk)] text-sm">Grafik Penjualan</h3>
                            <p class="text-xs text-[var(--c-md)]/60 mt-0.5" id="salesChartSubtitle">7 hari terakhir</p>
                        </div>
                        <div class="flex gap-2">
                            <button type="button" data-period="week" class="sales-period-btn category-chip category-chip-active text-[10px] px-3 py-1">Minggu</button>
                            <button type="button" data-period="month" class="sales-period-btn category-chip category-chip-inactive text-[10px] px-3 py-1">Bulan</button>
                        </div>
                    </div>

                    {{-- Real Chart.js Dummy Chart --}}
                    <div class="w-full h-48 mt-4 relative">
                        <canvas id="salesChart"></canvas>
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

            {{-- Second Charts Row --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                {{-- Visit Line Chart --}}
                <div class="bg-white rounded-2xl border border-[var(--c-lt)]/30 p-5 shadow-sm">
                    <div class="flex items-center justify-between mb-5">
                        <div>
                            <h3 class="font-bold text-[var(--c-dk)] text-sm">Tren Kunjungan (Jam)</h3>
                            <p class="text-xs text-[var(--c-md)]/60 mt-0.5">7 hari terakhir</p>
                        </div>
                    </div>
                    <div class="w-full h-48 mt-4 relative">
                        <canvas id="visitChart"></canvas>
                    </div>
                </div>

                {{-- Category Doughnut Chart --}}
                <div class="bg-white rounded-2xl border border-[var(--c-lt)]/30 p-5 shadow-sm">
                    <div class="flex items-center justify-between mb-5">
                        <div>
                            <h3 class="font-bold text-[var(--c-dk)] text-sm">Kategori Terfavorit</h3>
                            <p class="text-xs text-[var(--c-md)]/60 mt-0.5">Bulan ini</p>
                        </div>
                    </div>
                    <div class="w-full h-48 mt-4 relative">
                        <canvas id="categoryChart"></canvas>
                    </div>
                </div>
            </div>

            {{-- Recent Orders (Read-only) --}}
            <div class="bg-white rounded-2xl border border-[var(--c-lt)]/30 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-[var(--c-lt)]/20 flex items-center justify-between">
                    <div>
                        <h3 class="font-bold text-[var(--c-dk)] text-sm">Order Terbaru</h3>
                        <p class="text-xs text-[var(--c-md)]/60 mt-0.5">Real-time order masuk</p>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[560px]">
                        <thead class="bg-[var(--c-bg)]/50">
                            <tr>
                                <th class="table-header text-left">Order ID</th>
                                <th class="table-header text-left">Meja</th>
                                <th class="table-header text-left">Item</th>
                                <th class="table-header text-left">Total</th>
                                <th class="table-header text-left">Status</th>
                                <th class="table-header text-left">Waktu</th>
                            </tr>
                        </thead>
                        <tbody x-data class="divide-y divide-[var(--c-lt)]/10">
                            <template x-for="order in orders.slice(0, 5)" :key="order.id">
                                <tr class="hover:bg-[var(--c-bg)]/30 transition-colors duration-150">
                                    <td class="table-cell font-mono font-bold text-[var(--c-dk)] text-xs" x-text="order.id"></td>
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
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>



        {{-- ======= CATEGORY MANAGEMENT PAGE ======= --}}
        <template x-if="activePage === 'categories'">
        <div class="space-y-5">
            <div class="flex flex-wrap gap-3 items-center justify-between">
                <div>
                    <h2 class="font-bold text-[var(--c-dk)] text-base">Kategori Menu</h2>
                    <p class="text-xs text-[var(--c-md)]/60 mt-0.5">Kelola kategori untuk menu Anda</p>
                </div>
                <button @click="openAddCategory()" class="btn-primary text-xs py-2.5">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                    Tambah Kategori
                </button>
            </div>

            <div class="bg-white rounded-2xl border border-[var(--c-lt)]/30 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[500px]">
                        <thead class="bg-[var(--c-bg)]/50">
                            <tr>
                                <th class="table-header text-left">Nama Kategori</th>
                                <th class="table-header text-left">Urutan Tampil</th>
                                <th class="table-header text-left">Aksi</th>
                            </tr>
                        </thead>
                        <tbody x-data class="divide-y divide-[var(--c-lt)]/10">
                            <template x-for="cat in categories" :key="cat.id">
                                <tr class="hover:bg-[var(--c-bg)]/30 transition-colors duration-150">
                                    <td class="table-cell font-semibold text-[var(--c-dk)] text-sm" x-text="cat.name"></td>
                                    <td class="table-cell text-xs text-[var(--c-md)]/70" x-text="cat.sort_order"></td>
                                    <td class="table-cell">
                                        <div class="flex items-center gap-1.5">
                                            <button @click="openEditCategory(cat)" class="w-7 h-7 rounded-lg bg-[var(--c-bg)] flex items-center justify-center hover:bg-[var(--c-lt)]/40 transition-colors">
                                                <i data-lucide="pencil" class="w-3.5 h-3.5 text-[var(--c-md)]"></i>
                                            </button>
                                            <button @click="confirmDeleteCategory(cat)" class="w-7 h-7 rounded-lg bg-red-50 flex items-center justify-center hover:bg-red-100 transition-colors">
                                                <i data-lucide="trash-2" class="w-3.5 h-3.5 text-red-500"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        </template>

        {{-- ======= MENU MANAGEMENT PAGE ======= --}}
        <template x-if="activePage === 'menu'">
        <div class="space-y-5">
            <div class="flex flex-wrap gap-3 items-center justify-between">
                {{-- Search Bar: flex wrapper agar icon & input tidak overlap --}}
                <div class="flex items-center gap-2 bg-white border border-[var(--c-lt)] rounded-xl px-3 py-2 w-64 focus-within:ring-2 focus-within:ring-[var(--c-md)]/40 focus-within:border-[var(--c-md)] transition-all duration-200">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-[var(--c-md)]/50 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                    </svg>
                    <input
                        type="text"
                        x-model="menuSearch"
                        placeholder="Cari nama / kategori..."
                        class="flex-1 bg-transparent outline-none text-xs text-[var(--c-dk)] placeholder-[var(--c-lt)] min-w-0"
                    >
                    <button
                        x-show="menuSearch"
                        x-cloak
                        @click="menuSearch = ''"
                        class="shrink-0 text-[var(--c-md)]/40 hover:text-[var(--c-dk)] transition-colors"
                        title="Hapus pencarian"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                        </svg>
                    </button>
                </div>
                <button @click="openAddMenu()" class="btn-primary text-xs py-2.5">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                    Tambah Menu
                </button>
            </div>



            <div class="bg-white rounded-2xl border border-[var(--c-lt)]/30 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[620px]">
                        <thead class="bg-[var(--c-bg)]/50">
                            <tr>
                                <th class="table-header text-left">Menu</th>
                                <th class="table-header text-left">Kategori</th>
                                <th class="table-header text-left">Harga</th>
                                <th class="table-header text-left">Terjual</th>
                                <th class="table-header text-left">Stok</th>
                                <th class="table-header text-left">Aksi</th>
                            </tr>
                        </thead>
                        <tbody x-data class="divide-y divide-[var(--c-lt)]/10">
                            {{-- Empty state saat pencarian tidak ditemukan --}}
                            <tr x-show="filteredMenus.length === 0">
                                <td colspan="6" class="py-10 text-center">
                                    <div class="flex flex-col items-center gap-2">
                                        <i data-lucide="search-x" class="w-8 h-8 text-[var(--c-lt)]"></i>
                                        <p class="text-sm font-semibold text-[var(--c-dk)]/60">Menu tidak ditemukan</p>
                                        <p class="text-xs text-[var(--c-md)]/50">Coba kata kunci lain</p>
                                    </div>
                                </td>
                            </tr>
                            <template x-for="item in filteredMenus" :key="item.id">
                                <tr class="hover:bg-[var(--c-bg)]/30 transition-colors duration-150">
                                    <td class="table-cell">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-xl bg-[var(--c-bg)] flex items-center justify-center text-base shrink-0 overflow-hidden border border-[var(--c-lt)]/30">
                                                <template x-if="item.image_url">
                                                    <img :src="item.image_url" class="w-full h-full object-cover">
                                                </template>
                                                <template x-if="!item.image_url">
                                                    <span>
                                                        <i data-lucide="coffee" class="w-4 h-4 text-[var(--c-md)]"></i>
                                                    </span>
                                                </template>
                                            </div>
                                            <span class="font-semibold text-[var(--c-dk)] text-sm" x-text="item.name"></span>
                                        </div>
                                    </td>
                                    <td class="table-cell">
                                        <span class="badge bg-[var(--c-lt)]/30 text-[var(--c-md)] text-[10px]" x-text="item.category ? item.category.name : '-'"></span>
                                    </td>
                                    <td class="table-cell">
                                        <template x-if="item.has_hot || item.has_ice">
                                            <div class="flex flex-col gap-0.5 text-[11px]">
                                                <template x-if="item.has_hot">
                                                    <span class="font-bold text-orange-600 bg-orange-50 px-1.5 py-0.5 rounded w-fit">Hot: Rp <span x-text="item.price_hot?.toLocaleString('id-ID')"></span></span>
                                                </template>
                                                <template x-if="item.has_ice">
                                                    <span class="font-bold text-blue-600 bg-blue-50 px-1.5 py-0.5 rounded w-fit mt-0.5">Ice: Rp <span x-text="item.price_ice?.toLocaleString('id-ID')"></span></span>
                                                </template>
                                            </div>
                                        </template>
                                        <template x-if="!item.has_hot && !item.has_ice">
                                            <span class="font-bold text-[var(--c-dk)] text-sm" x-text="'Rp ' + (item.price || 0).toLocaleString('id-ID')"></span>
                                        </template>
                                    </td>
                                    <td class="table-cell text-xs text-[var(--c-md)]/70" x-text="item.sold + 'x'"></td>
                                    <td class="table-cell">
                                        <button @click="toggleAvailability(item)"
                                                :class="item.is_available ? 'bg-green-100 text-green-700 hover:bg-red-50 hover:text-red-600' : 'bg-red-100 text-red-700 hover:bg-green-50 hover:text-green-600'"
                                                class="badge text-[10px] transition-colors duration-200 cursor-pointer"
                                                :title="item.is_available ? 'Klik untuk set Habis' : 'Klik untuk set Tersedia'"
                                                x-text="item.is_available ? 'Tersedia' : 'Habis'">
                                        </button>
                                    </td>
                                    <td class="table-cell">
                                        <div class="flex items-center gap-1.5">
                                            <button @click="openEditMenu(item)" class="w-7 h-7 rounded-lg bg-[var(--c-bg)] flex items-center justify-center hover:bg-[var(--c-lt)]/40 transition-colors">
                                                <i data-lucide="pencil" class="w-3.5 h-3.5 text-[var(--c-md)]"></i>
                                            </button>
                                            <button @click="confirmDeleteMenu(item)" class="w-7 h-7 rounded-lg bg-red-50 flex items-center justify-center hover:bg-red-100 transition-colors">
                                                <i data-lucide="trash-2" class="w-3.5 h-3.5 text-red-500"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        </template>

        {{-- ======= TABLES PAGE ======= --}}
        <template x-if="activePage === 'tables'">
        <div class="space-y-5">

            {{-- Header row --}}
            <div class="flex flex-wrap gap-3 items-center justify-between">
                <div>
                    <h2 class="font-bold text-[var(--c-dk)] text-base">Daftar Meja</h2>
                    <p class="text-xs text-[var(--c-md)]/60 mt-0.5" x-text="tables.length + ' meja terdaftar'"></p>
                </div>
                <button @click="showAddModal = true" class="btn-primary text-xs py-2.5">
                    + Tambah Meja
                </button>
            </div>




            {{-- Tables Grid --}}
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                <template x-for="table in tables" :key="table.id">
                    <div class="bg-white rounded-2xl shadow-sm overflow-hidden transition-all duration-200 hover:shadow-lg hover:-translate-y-1"
                         style="border: 1px solid var(--c-lt);">

                        <div class="h-1 w-full" style="background:var(--c-dk);"></div>

                        <div class="p-5">
                            {{-- Number badge only --}}
                            <div class="flex items-center justify-between mb-4">
                                <div class="w-12 h-12 rounded-2xl flex items-center justify-center font-extrabold text-xl text-[var(--c-dk)]"
                                     style="background:#DDD3C9;">
                                    <span x-text="table.number"></span>
                                </div>
                            </div>

                            <p class="font-bold text-[var(--c-dk)] text-sm mb-4" x-text="table.name"></p>

                            {{-- Actions --}}
                            <div class="flex gap-2">
                                <button @click="openQr(table)"
                                        class="flex-1 flex items-center justify-center gap-2 text-[var(--c-dk)] text-xs font-bold py-2.5 rounded-xl transition-all"
                                        style="background:var(--c-bg);border:1px solid var(--c-lt);"
                                        onmouseover="this.style.background='var(--c-lt)'" onmouseout="this.style.background='var(--c-bg)'">
                                    <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M3 3h6v6H3V3zm2 2v2h2V5H5zm8-2h6v6h-6V3zm2 2v2h2V5h-2zM3 13h6v6H3v-6zm2 2v2h2v-2H5zm13-2h-2v2h-2v2h2v-2h2v2h2v-2h-2v-2zm2 6h-2v2h2v-2zm-4 0h-2v2h2v-2z"/>
                                    </svg>
                                    Lihat QR
                                </button>
                                <button @click="confirmDelete(table)"
                                        class="w-10 h-10 shrink-0 flex items-center justify-center rounded-xl transition-colors"
                                        style="background:#fef2f2;border:1px solid #fecaca;"
                                        onmouseover="this.style.background='#fee2e2'" onmouseout="this.style.background='#fef2f2'">
                                    <svg class="w-4 h-4" style="color:#ef4444;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
        </template>

        {{-- ======= SETTINGS PAGE ======= --}}
        <template x-if="activePage === 'settings'">
        <div class="max-w-xl mx-auto space-y-6"
             x-data="{
                loading: false,
                successMsg: '',
                errorMsg: '',
                form: { current_password: '', new_password: '', new_password_confirmation: '' },
                async submit() {
                    this.loading = true;
                    this.successMsg = '';
                    this.errorMsg = '';
                    try {
                        const res = await fetch('/admin/api/settings/password', {
                            method: 'PUT',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify(this.form)
                        });
                        const data = await res.json();
                        if(res.ok) {
                            this.successMsg = data.message;
                            this.form = { current_password: '', new_password: '', new_password_confirmation: '' };
                        } else {
                            this.errorMsg = data.message || 'Gagal mengubah kata sandi.';
                        }
                    } catch(e) {
                        this.errorMsg = 'Terjadi kesalahan jaringan.';
                    }
                    this.loading = false;
                }
             }">
            
            <div class="bg-white rounded-2xl border border-[var(--c-lt)]/30 p-6 shadow-sm mt-6">
                <div class="flex items-center gap-3 mb-1">
                    <div class="w-10 h-10 bg-[var(--c-bg)] rounded-xl flex items-center justify-center">
                        <i data-lucide="lock" class="w-5 h-5 text-[var(--c-dk)]"></i>
                    </div>
                    <h3 class="text-lg font-bold text-[var(--c-dk)]">Ubah Kata Sandi</h3>
                </div>
                <p class="text-xs text-[var(--c-md)]/70 mb-6 pl-13">Pastikan kata sandi baru Anda kuat dan aman.</p>
                
                <div x-show="successMsg" class="mb-5 p-3 bg-green-50 text-green-700 text-sm rounded-xl border border-green-200 flex items-center gap-2">
                    <i data-lucide="check-circle" class="w-4 h-4"></i>
                    <span x-text="successMsg"></span>
                </div>
                <div x-show="errorMsg" class="mb-5 p-3 bg-red-50 text-red-700 text-sm rounded-xl border border-red-200 flex items-center gap-2">
                    <i data-lucide="alert-circle" class="w-4 h-4"></i>
                    <span x-text="errorMsg"></span>
                </div>

                <form @submit.prevent="submit" class="space-y-5">
                    <div>
                        <label class="block text-xs font-bold text-[var(--c-dk)] mb-1.5">Kata Sandi Saat Ini</label>
                        <input type="password" x-model="form.current_password" required class="input-field w-full text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-[var(--c-dk)] mb-1.5">Kata Sandi Baru</label>
                        <input type="password" x-model="form.new_password" required minlength="6" class="input-field w-full text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-[var(--c-dk)] mb-1.5">Konfirmasi Kata Sandi Baru</label>
                        <input type="password" x-model="form.new_password_confirmation" required minlength="6" class="input-field w-full text-sm">
                    </div>
                    <div class="pt-2">
                        <button type="submit" :disabled="loading" class="btn-primary py-2.5 px-6 text-sm font-bold flex items-center justify-center gap-2 disabled:opacity-50">
                            <i data-lucide="save" class="w-4 h-4" x-show="!loading"></i>
                            <div x-show="loading" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                            <span x-text="loading ? 'Menyimpan...' : 'Simpan Perubahan'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
        </template>

        {{-- ======= ANALYTICS PAGE ======= --}}
        <template x-if="activePage === 'analytics'">
        <div x-data="analyticsApp()"
             x-init="init(); $watch('activeChart', () => { if (!loading && Object.keys(analyticsData).length) renderCharts(); });"
             @load-analytics.window="loadData()"
             class="space-y-5">

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
                    <p class="text-sm text-[var(--c-md)]/60">Memuat data analytics...</p>
                </div>
            </div>

            {{-- Data section: visibility (bukan display:none) agar canvas punya dimensi --}}
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
                    <div class="stat-card bg-[var(--c-md-lt)] border-none">
                        <div class="flex items-start justify-between mb-3">
                            <div class="w-9 h-9 bg-white/15 rounded-xl flex items-center justify-center">
                                <i data-lucide="trending-up" class="w-4 h-4 text-white opacity-90"></i>
                            </div>
                        </div>
                        <p class="text-lg font-extrabold text-white" x-text="'Rp ' + (analyticsData.summary?.avg_order_value || 0).toLocaleString('id-ID')"></p>
                        <p class="text-[10px] text-white opacity-70 mt-1">Rata-rata / Order</p>
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
                        <canvas id="analyticsMainChart"></canvas>
                    </div>
                </div>

                {{-- Bottom Row: Top Menu + Peak Hours --}}
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
                        <h3 class="font-bold text-[var(--c-dk)] text-sm mb-4">Jam Sibuk (Periode Terpilih)</h3>
                        <div class="w-full h-56 relative">
                            <canvas id="analyticsPeakChart"></canvas>
                        </div>
                    </div>
                </div>

                {{-- Daily Breakdown Table --}}
                <div class="bg-white rounded-2xl border border-[var(--c-lt)]/30 shadow-sm overflow-hidden">
                    <div class="px-5 py-4 border-b border-[var(--c-lt)]/20 flex items-center justify-between">
                        <div>
                            <h3 class="font-bold text-[var(--c-dk)] text-sm">Rincian Per Periode</h3>
                            <p class="text-xs text-[var(--c-md)]/60 mt-0.5">Pendapatan dan jumlah order per periode</p>
                        </div>
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
        </template>

    </main>

    {{-- BOTTOM BAR --}}
    <footer class="border-t border-[var(--c-lt)]/30 px-6 py-3 bg-white">
        <p class="text-xs text-[var(--c-md)]/50 text-center">© {{ date('Y') }} Skena Coffee Admin Panel · v1.0.0</p>
    </footer>
</div>

{{-- ======= MODALS (outside main div, inside Alpine scope) ======= --}}

{{-- QR Modal --}}
<template x-teleport="body">
<div x-show="showQrModal" x-cloak
     style="position:fixed;top:0;left:0;width:100vw;height:100vh;z-index:9999;display:flex;align-items:center;justify-content:center;padding:16px;background:rgba(0,0,0,0.65);"
     @click.self="showQrModal = false"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0">
    <div style="background:white;border-radius:24px;box-shadow:0 25px 50px rgba(0,0,0,0.3);width:100%;max-width:380px;padding:32px;margin:auto;"
         @click.stop
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100">

        <div class="flex items-center justify-between mb-6">
            <div>
                <h3 class="font-extrabold text-[var(--c-dk)] text-xl" x-text="selectedTable ? selectedTable.name : ''"></h3>
                <p class="text-xs text-[var(--c-md)]/60 mt-0.5">Scan QR code untuk akses menu</p>
            </div>
            <button @click="showQrModal = false"
                    style="width:32px;height:32px;background:var(--c-bg);border-radius:10px;display:flex;align-items:center;justify-content:center;border:none;cursor:pointer;">
                <svg style="width:16px;height:16px;color:var(--c-dk);" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <div style="display:flex;justify-content:center;margin-bottom:20px;">
            <div style="padding:20px;background:#fafafa;border-radius:16px;border:2px solid #e7d5bf;">
                <div id="qr-canvas"></div>
            </div>
        </div>

        <div style="background:var(--c-bg);border-radius:12px;padding:12px 16px;margin-bottom:20px;">
            <p style="font-size:10px;color:var(--c-md);font-weight:600;text-transform:uppercase;letter-spacing:0.05em;margin:0 0 4px;">URL Scan</p>
            <p style="font-size:11px;color:var(--c-dk);font-family:monospace;word-break:break-all;margin:0;"
               x-text="'{{ $dynamicBaseUrl }}/scan/' + (selectedTable ? selectedTable.number : '')"></p>
        </div>

        <button @click="downloadQr()"
                style="width:100%;background:var(--c-dk);color:var(--c-bg);border:none;border-radius:12px;padding:14px;font-weight:700;font-size:14px;cursor:pointer;"
                onmouseover="this.style.background='var(--c-md)'" onmouseout="this.style.background='var(--c-dk)'">
            ⬇ Download QR PNG
        </button>
    </div>
</div>
</template>

{{-- Add Table Modal --}}
<template x-teleport="body">
<div x-show="showAddModal" x-cloak
     style="position:fixed;top:0;left:0;width:100vw;height:100vh;z-index:9999;display:flex;align-items:center;justify-content:center;padding:16px;background:rgba(0,0,0,0.65);"
     @click.self="showAddModal = false"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0">
    <div style="background:white;border-radius:24px;box-shadow:0 25px 50px rgba(0,0,0,0.3);width:100%;max-width:440px;padding:32px;margin:auto;"
         @click.stop
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100">

        <h3 class="font-extrabold text-[var(--c-dk)] text-lg mb-1">Tambah Meja Baru</h3>
        <p class="text-xs text-[var(--c-md)]/60 mb-6">QR code otomatis dibuat setelah meja ditambahkan</p>

        <div class="space-y-4 mb-6">
            <div>
                <label class="block text-xs font-bold text-[var(--c-dk)] mb-1.5">Nomor Meja <span style="color:#ef4444">*</span></label>
                <input type="number" x-model="newTableNumber" min="1" placeholder="Contoh: 9"
                       class="input-field w-full text-sm">
            </div>
            <div>
                <label class="block text-xs font-bold text-[var(--c-dk)] mb-1.5">Nama Meja <span class="font-normal text-[var(--c-md)]/50">(opsional)</span></label>
                <input type="text" x-model="newTableName" placeholder="Contoh: Meja VIP, Meja Pojok..."
                       class="input-field w-full text-sm">
            </div>
            <div>
                <label class="block text-xs font-bold text-[var(--c-dk)] mb-1.5">Kapasitas Kursi</label>
                <select x-model="newTableCapacity" class="input-field w-full text-sm">
                    <option value="2">2 kursi</option>
                    <option value="4" selected>4 kursi</option>
                    <option value="6">6 kursi</option>
                    <option value="8">8 kursi</option>
                    <option value="10">10 kursi</option>
                </select>
            </div>
        </div>

        <div class="flex gap-3">
            <button @click="addTable()" :disabled="!newTableNumber"
                    class="flex-1 btn-primary text-sm py-3 disabled:opacity-40 disabled:cursor-not-allowed">
                + Tambah Meja
            </button>
            <button @click="showAddModal = false" class="flex-1 btn-outline text-sm py-3">
                Batal
            </button>
        </div>
    </div>
</div>
</template>

{{-- Delete Confirm Modal --}}
<template x-teleport="body">
<div x-show="showDeleteConfirm" x-cloak
     style="position:fixed;top:0;left:0;width:100vw;height:100vh;z-index:9999;display:flex;align-items:center;justify-content:center;padding:16px;background:rgba(0,0,0,0.65);"
     @click.self="showDeleteConfirm = false"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0">
    <div style="background:white;border-radius:24px;box-shadow:0 25px 50px rgba(0,0,0,0.3);width:100%;max-width:360px;padding:32px;text-align:center;margin:auto;"
         @click.stop
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100">

        <div style="width:56px;height:56px;background:#fee2e2;border-radius:16px;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
            <svg style="width:24px;height:24px;color:#ef4444;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
            </svg>
        </div>
        <h3 class="font-extrabold text-[var(--c-dk)] text-lg mb-2">Hapus Meja?</h3>
        <p class="text-sm text-[var(--c-md)]/70 mb-6"
           x-text="tableToDelete ? 'Yakin hapus ' + tableToDelete.name + '? QR code meja ini tidak aktif lagi.' : ''"></p>

        <div class="flex gap-3">
            <button @click="deleteTable()"
                    style="flex:1;background:#ef4444;color:white;border:none;border-radius:12px;padding:14px;font-weight:700;font-size:14px;cursor:pointer;"
                    onmouseover="this.style.background='#dc2626'" onmouseout="this.style.background='#ef4444'">
                Ya, Hapus
            </button>
            <button @click="showDeleteConfirm = false" class="flex-1 btn-outline text-sm py-3">
                Batal
            </button>
        </div>
    </div>
</template>

{{-- Category Add/Edit Modal --}}
<template x-teleport="body">
<div x-show="showCategoryModal" x-cloak
     class="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-black/65"
     @click.self="showCategoryModal = false"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0">
    <div class="bg-white rounded-[24px] shadow-2xl w-full max-w-[400px] p-6 sm:p-8 m-auto"
         @click.stop
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100">

        <div class="flex items-center justify-between mb-6">
            <div>
                <h3 class="font-extrabold text-[var(--c-dk)] text-lg mb-1" x-text="categoryForm.id ? 'Edit Kategori' : 'Tambah Kategori'"></h3>
            </div>
            <button @click="showCategoryModal = false"
                    class="w-8 h-8 bg-[var(--c-bg)] rounded-xl flex items-center justify-center hover:bg-[var(--c-lt)]/50 transition-colors">
                <i data-lucide="x" class="w-4 h-4 text-[var(--c-dk)]"></i>
            </button>
        </div>

        <form @submit.prevent="saveCategory()" class="space-y-4">
            <div>
                <label class="block text-xs font-bold text-[var(--c-dk)] mb-1.5">Nama Kategori *</label>
                <input type="text" x-model="categoryForm.name" required class="input-field w-full text-sm">
            </div>

            <div>
                <label class="block text-xs font-bold text-[var(--c-dk)] mb-1.5">Urutan Tampil</label>
                <input type="number" x-model="categoryForm.sort_order" min="0" class="input-field w-full text-sm">
                <p class="text-[10px] text-[var(--c-md)] mt-1.5">Angka lebih kecil akan tampil lebih dulu.</p>
            </div>

            <div class="flex gap-3 pt-4">
                <button type="submit" class="flex-1 btn-primary text-sm py-3 font-bold flex items-center justify-center gap-2">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    Simpan Kategori
                </button>
            </div>
        </form>
    </div>
</div>
</template>

{{-- Category Delete Confirm --}}
<template x-teleport="body">
<div x-show="showCategoryDeleteConfirm" x-cloak
     class="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-black/65"
     @click.self="showCategoryDeleteConfirm = false"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0">
    <div class="bg-white rounded-[24px] shadow-2xl w-full max-w-[400px] p-6 text-center m-auto"
         @click.stop
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100">
        
        <div class="w-16 h-16 bg-red-50 rounded-full flex items-center justify-center mx-auto mb-4 border-4 border-red-100">
            <svg class="w-8 h-8 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
        </div>
        
        <h3 class="font-extrabold text-[var(--c-dk)] text-lg mb-2">Hapus Kategori?</h3>
        <p class="text-sm text-[var(--c-md)]/70 mb-6">Apakah Anda yakin ingin menghapus <strong class="text-[var(--c-dk)]" x-text="categoryToDelete?.name"></strong>? Pastikan tidak ada menu yang menggunakan kategori ini.</p>
        
        <div class="flex gap-3">
            <button @click="showCategoryDeleteConfirm = false"
                    class="flex-1 py-3 text-sm font-bold text-[var(--c-dk)] bg-[var(--c-bg)] rounded-xl border border-[var(--c-lt)] hover:bg-[var(--c-lt)] transition-colors">
                Batal
            </button>
            <button @click="deleteCategory()"
                    class="flex-1 py-3 text-sm font-bold text-white bg-red-600 rounded-xl border border-red-600 hover:bg-red-700 transition-colors">
                Ya, Hapus
            </button>
        </div>
    </div>
</div>
</template>

{{-- Menu Add/Edit Modal --}}
<template x-teleport="body">
<div x-show="showMenuModal" x-cloak
     class="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-black/65"
     @click.self="showMenuModal = false"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0">
    <div class="bg-white rounded-[24px] shadow-2xl w-full max-w-[500px] p-6 sm:p-8 m-auto max-h-[90vh] overflow-y-auto"
         @click.stop
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100">

        <div class="flex items-center justify-between mb-6">
            <div>
                <h3 class="font-extrabold text-[var(--c-dk)] text-lg mb-1" x-text="menuForm.id ? 'Edit Menu' : 'Tambah Menu'"></h3>
            </div>
            <button @click="showMenuModal = false"
                    class="w-8 h-8 bg-[var(--c-bg)] rounded-xl flex items-center justify-center hover:bg-[var(--c-lt)]/50 transition-colors">
                <i data-lucide="x" class="w-4 h-4 text-[var(--c-dk)]"></i>
            </button>
        </div>

        <form @submit.prevent="saveMenu()" class="space-y-4">
            <div>
                <label class="block text-xs font-bold text-[var(--c-dk)] mb-1.5">Nama Menu *</label>
                <input type="text" x-model="menuForm.name" required class="input-field w-full text-sm">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-[var(--c-dk)] mb-1.5">Kategori *</label>
                    <select x-model="menuForm.category_id" required class="input-field w-full text-sm">
                        <template x-for="cat in categories" :key="cat.id">
                            <option :value="cat.id" x-text="cat.name"></option>
                        </template>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-[var(--c-dk)] mb-1.5">Harga Reguler (Rp) <span x-show="!menuForm.has_hot && !menuForm.has_ice">*</span></label>
                    <input type="number" x-model="menuForm.price" :required="!menuForm.has_hot && !menuForm.has_ice" min="0" class="input-field w-full text-sm" :disabled="menuForm.has_hot || menuForm.has_ice">
                    <p class="text-[10px] text-[var(--c-md)] mt-1.5" x-show="menuForm.has_hot || menuForm.has_ice">Harga reguler diabaikan karena varian aktif.</p>
                </div>
            </div>

            <div x-show="!menuForm.has_hot && !menuForm.has_ice">
                <label class="block text-xs font-bold text-[var(--c-dk)] mb-1.5">Deskripsi Singkat</label>
                <textarea x-model="menuForm.description" rows="2" class="input-field w-full text-sm"></textarea>
            </div>

            {{-- Varian Hot & Ice --}}
            <div class="bg-[var(--c-bg)]/50 p-4 rounded-xl border border-[var(--c-lt)]/30 space-y-4">
                <div class="flex items-center gap-6">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" x-model="menuForm.has_hot" class="w-4 h-4 text-[var(--c-dk)] rounded border-[var(--c-lt)] focus:ring-[var(--c-dk)]">
                        <span class="text-sm font-bold text-[var(--c-dk)]">Tersedia Varian Hot</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" x-model="menuForm.has_ice" class="w-4 h-4 text-[var(--c-dk)] rounded border-[var(--c-lt)] focus:ring-[var(--c-dk)]">
                        <span class="text-sm font-bold text-[var(--c-dk)]">Tersedia Varian Ice</span>
                    </label>
                </div>

                {{-- Hot Fields --}}
                <div x-show="menuForm.has_hot" class="space-y-3 pt-2 border-t border-[var(--c-lt)]/30" x-collapse>
                    <h4 class="font-bold text-sm text-[var(--c-dk)] flex items-center gap-1.5"><i data-lucide="flame" class="w-4 h-4 text-orange-500"></i> Detail Varian Hot</h4>
                    <div class="grid grid-cols-1 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-[var(--c-dk)] mb-1.5">Harga Hot (Rp) *</label>
                            <input type="number" x-model="menuForm.price_hot" :required="menuForm.has_hot" min="0" class="input-field w-full text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-[var(--c-dk)] mb-1.5">Deskripsi Hot</label>
                            <textarea x-model="menuForm.desc_hot" rows="2" class="input-field w-full text-sm"></textarea>
                        </div>
                    </div>
                </div>

                {{-- Ice Fields --}}
                <div x-show="menuForm.has_ice" class="space-y-3 pt-2 border-t border-[var(--c-lt)]/30" x-collapse>
                    <h4 class="font-bold text-sm text-[var(--c-dk)] flex items-center gap-1.5"><i data-lucide="snowflake" class="w-4 h-4 text-blue-500"></i> Detail Varian Ice</h4>
                    <div class="grid grid-cols-1 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-[var(--c-dk)] mb-1.5">Harga Ice (Rp) *</label>
                            <input type="number" x-model="menuForm.price_ice" :required="menuForm.has_ice" min="0" class="input-field w-full text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-[var(--c-dk)] mb-1.5">Deskripsi Ice</label>
                            <textarea x-model="menuForm.desc_ice" rows="2" class="input-field w-full text-sm"></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-[var(--c-dk)] mb-1.5">Tag (opsional)</label>
                    <input type="text" x-model="menuForm.tag" placeholder="Misal: Terlaris" class="input-field w-full text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold text-[var(--c-dk)] mb-1.5">Ketersediaan</label>
                    <select x-model="menuForm.is_available" class="input-field w-full text-sm">
                        <option :value="true">Tersedia</option>
                        <option :value="false">Habis</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-[var(--c-dk)] mb-1.5">Gambar Menu</label>
                <input type="file" x-ref="imageInput" accept="image/*" @change="menuForm.image = $event.target.files[0]"
                       class="block w-full text-sm text-[var(--c-md)]
                              file:mr-4 file:py-2 file:px-4
                              file:rounded-full file:border-0
                              file:text-xs file:font-bold
                              file:bg-[var(--c-bg)] file:text-[var(--c-dk)]
                              hover:file:bg-[var(--c-lt)]/50
                              border border-[var(--c-lt)]/50 rounded-xl p-2 cursor-pointer">
                <p class="text-[10px] text-[var(--c-md)] mt-1.5" x-show="menuForm.id && !menuForm.image">Biarkan kosong jika tidak ingin mengubah gambar.</p>
            </div>

            <div class="flex gap-3 pt-4">
                <button type="submit" class="flex-1 btn-primary text-sm py-3 font-bold flex items-center justify-center gap-2">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    Simpan Menu
                </button>
            </div>
        </form>
    </div>
</div>
</template>

{{-- Menu Delete Confirm --}}
<template x-teleport="body">
<div x-show="showMenuDeleteConfirm" x-cloak
     class="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-black/65"
     @click.self="showMenuDeleteConfirm = false"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0">
    <div class="bg-white rounded-[24px] shadow-2xl w-full max-w-[400px] p-6 text-center m-auto"
         @click.stop
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100">
        
        <div class="w-16 h-16 bg-red-50 rounded-full flex items-center justify-center mx-auto mb-4 border-4 border-red-100">
            <svg class="w-8 h-8 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
        </div>
        
        <h3 class="font-extrabold text-[var(--c-dk)] text-lg mb-2">Hapus Menu?</h3>
        <p class="text-sm text-[var(--c-md)]/70 mb-6">Apakah Anda yakin ingin menghapus <strong class="text-[var(--c-dk)]" x-text="menuToDelete?.name"></strong>? Tindakan ini tidak dapat dibatalkan.</p>
        
        <div class="flex gap-3">
            <button @click="showMenuDeleteConfirm = false"
                    class="flex-1 py-3 text-sm font-bold text-[var(--c-dk)] bg-[var(--c-bg)] rounded-xl border border-[var(--c-lt)] hover:bg-[var(--c-lt)] transition-colors">
                Batal
            </button>
            <button @click="deleteMenu()"
                    class="flex-1 py-3 text-sm font-bold text-white bg-red-600 rounded-xl border border-red-600 hover:bg-red-700 transition-colors">
                Ya, Hapus
            </button>
        </div>
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
        const cBg = style.getPropertyValue('--c-bg').trim();
        const cAc = style.getPropertyValue('--c-ac').trim();
        
        // Initialize Sales Chart (dengan toggle Minggu/Bulan)
        const salesCtx = document.getElementById('salesChart');
        const salesDatasets = {
            week: {
                labels: {{ Illuminate\Support\Js::from($salesLabels) }},
                data: {{ Illuminate\Support\Js::from($salesData) }},
                subtitle: '7 hari terakhir'
            },
            month: {
                labels: {{ Illuminate\Support\Js::from($salesLabelsMonth) }},
                data: {{ Illuminate\Support\Js::from($salesDataMonth) }},
                subtitle: '30 hari terakhir'
            }
        };
        let salesChartInstance = null;

        function renderSalesChart(period) {
            if (!salesCtx) return;
            const ds = salesDatasets[period] || salesDatasets.week;

            if (salesChartInstance) {
                salesChartInstance.destroy();
            }

            salesChartInstance = new Chart(salesCtx, {
                type: 'line',
                data: {
                    labels: ds.labels,
                    datasets: [{
                        label: 'Penjualan (Juta Rp)',
                        data: ds.data,
                        borderColor: cMd,
                        backgroundColor: 'transparent',
                        fill: false,
                        tension: 0.4,
                        borderWidth: 3,
                        pointBackgroundColor: '#fff',
                        pointBorderColor: cMd,
                        pointBorderWidth: 2,
                        pointRadius: period === 'month' ? 2 : 4,
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
                                    const val = ctx.raw * 1000000; // convert back to Rp
                                    return 'Rp ' + (val >= 1000000 ? (val/1000000).toFixed(1)+'jt' : val.toLocaleString('id-ID'));
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            border: { display: false },
                            grid: {
                                color: cLt,
                                drawBorder: false,
                            },
                            ticks: {
                                color: cMd,
                                font: { size: 10 },
                                callback: v => v >= 1 ? v.toFixed(1)+'jt' : (v > 0 ? (v*1000).toFixed(0)+'k' : 0)
                            }
                        },
                        x: {
                            border: { display: false },
                            grid: { display: false },
                            ticks: {
                                color: cMd,
                                font: { size: 10 },
                                maxRotation: period === 'month' ? 45 : 0,
                                autoSkip: true,
                                maxTicksLimit: period === 'month' ? 10 : 7
                            }
                        }
                    }
                }
            });

            // Update subtitle
            const subtitleEl = document.getElementById('salesChartSubtitle');
            if (subtitleEl) subtitleEl.textContent = ds.subtitle;
        }

        // Initial render
        renderSalesChart('week');

        // Wire up period buttons
        document.querySelectorAll('.sales-period-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const period = this.dataset.period;
                // Update active state
                document.querySelectorAll('.sales-period-btn').forEach(b => {
                    b.classList.remove('category-chip-active');
                    b.classList.add('category-chip-inactive');
                });
                this.classList.remove('category-chip-inactive');
                this.classList.add('category-chip-active');
                // Re-render chart
                renderSalesChart(period);
            });
        });


        // Visit Line Chart
        const visitCtx = document.getElementById('visitChart');
        if (visitCtx) {
            const visitRawData = {{ Illuminate\Support\Js::from($visitData) }};
            const visitMax = Math.max(...visitRawData, 1); // minimal 1 agar skala tidak desimal
            new Chart(visitCtx, {
                type: 'bar',
                data: {
                    labels: {{ Illuminate\Support\Js::from($visitLabels) }},
                    datasets: [{
                        label: 'Jumlah Order',
                        data: visitRawData,
                        backgroundColor: visitRawData.map((v) => v === visitMax && visitMax > 0 ? cDk : cLt + '80'),
                        borderRadius: 6,
                        maxBarThickness: 40,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: ctx => ctx.raw + ' order'
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            border: { display: false },
                            grid: { color: cLt + '60', drawBorder: false },
                            ticks: {
                                color: cMd,
                                font: { size: 10 },
                                precision: 0,        // angka bulat
                                stepSize: 1          // step minimal 1
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

        // Category Doughnut Chart — data real dari transaksi
        const catCtx = document.getElementById('categoryChart');
        if (catCtx) {
            const catData = {{ Illuminate\Support\Js::from($categoryChart) }};
            // Palet 6 warna berbasis tema
            const catColors = [cDk, cMd, cLt, cAc,
                getComputedStyle(document.body).getPropertyValue('--c-md-lt').trim() || '#8faba3',
                '#c4b5a0'
            ];
            const totalQty = catData.data.reduce((a, b) => a + b, 0);
            new Chart(catCtx, {
                type: 'doughnut',
                data: {
                    labels: catData.labels,
                    datasets: [{
                        data: catData.data,
                        backgroundColor: catData.labels.map((_, i) => catColors[i % catColors.length]),
                        borderWidth: 2,
                        borderColor: '#fff',
                        hoverOffset: 8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right',
                            labels: {
                                color: cDk,
                                font: { size: 11, family: 'Plus Jakarta Sans' },
                                usePointStyle: true,
                                boxWidth: 8,
                                padding: 12
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: ctx => {
                                    const pct = totalQty > 0 ? Math.round((ctx.raw / totalQty) * 100) : 0;
                                    return ` ${ctx.raw} item (${pct}%)`;
                                }
                            }
                        }
                    },
                    cutout: '68%'
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
// ─── Analytics Alpine Component ───────────────────────────────────────────────
function analyticsApp() {
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
            return this._buildUrl('/admin/api/analytics/export-csv');
        },
        get pdfUrl() {
            return this._buildUrl('/admin/api/analytics/export-pdf');
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
                const url = this._buildUrl('/admin/api/analytics/data');
                const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
                this.analyticsData = await res.json();
                this.loading = false;
                // $nextTick + 200ms agar DOM update visibility selesai sebelum render chart
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
            // Fallback warna jika CSS vars belum terbaca di beberapa browser
            const cDk = style.getPropertyValue('--c-dk').trim() || '#1a3c34';
            const cMd = style.getPropertyValue('--c-md').trim() || '#2d6a5e';
            const cLt = style.getPropertyValue('--c-lt').trim() || '#84c5b4';

            // Destroy semua chart lama dengan Chart.getChart() untuk bebaskan canvas context
            ['analyticsMainChart', 'analyticsPeakChart'].forEach(id => {
                const ex = Chart.getChart(id);
                if (ex) ex.destroy();
            });
            if (this.mainChart) { try { this.mainChart.destroy(); } catch(e) {} this.mainChart = null; }
            if (this.peakChart) { try { this.peakChart.destroy(); } catch(e) {} this.peakChart = null; }

            // ── Main Chart ──
            const mainCtx = document.getElementById('analyticsMainChart');
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
                } catch(err) { console.error('Admin main chart error:', err); }
            }

            // ── Peak Hours Chart ──
            const peakCtx = document.getElementById('analyticsPeakChart');
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
                } catch(err) { console.error('Admin peak chart error:', err); }
            }
            // Force resize agar Chart.js recalculate dimensi canvas
            setTimeout(() => window.dispatchEvent(new Event('resize')), 50);
        },

        // Re-render when chart type switches
        $watch_activeChart(val) {
            this.renderCharts();
        }
    };
}

// Listen for Alpine page changes and trigger analytics data reload when switching to analytics tab
document.addEventListener('DOMContentLoaded', () => {
    document.body.addEventListener('click', (e) => {
        const btn = e.target.closest('button[id^="nav-analytics"]');
        if (btn) {
            // Dispatch event so analytics div can reload data if already initialized
            setTimeout(() => {
                window.dispatchEvent(new CustomEvent('load-analytics'));
            }, 150);
        }
    });
});
</script>

</body>
</html>
