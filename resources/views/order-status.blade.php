@extends('layouts.app')

@section('title', 'Status Pesanan — Skena Coffee')
@section('meta_description', 'Pantau status pesanan kopi kamu di Skena Coffee secara real-time')

@section('content')

@php
$statusMap = [
    'pending'   => ['label' => 'Menunggu', 'color' => 'yellow', 'icon' => '⏳', 'step' => 0],
    'paid'      => ['label' => 'Diproses', 'color' => 'blue',   'icon' => '💳', 'step' => 1],
    'making'    => ['label' => 'Dibuat',   'color' => 'orange', 'icon' => '☕', 'step' => 2],
    'ready'     => ['label' => 'Siap',     'color' => 'green',  'icon' => '✅', 'step' => 3],
    'done'      => ['label' => 'Selesai',  'color' => 'gray',   'icon' => '🎉', 'step' => 4],
    'cancelled' => ['label' => 'Batal',    'color' => 'red',    'icon' => '❌', 'step' => -1],
];

$steps = [
    ['key' => 'paid',   'label' => 'Pembayaran Diterima', 'desc' => 'Pembayaran berhasil dikonfirmasi'],
    ['key' => 'making', 'label' => 'Sedang Dibuat',       'desc' => 'Barista sedang menyiapkan pesananmu'],
    ['key' => 'ready',  'label' => 'Siap Diambil',        'desc' => 'Pesananmu sudah siap! Ambil di counter'],
    ['key' => 'done',   'label' => 'Selesai',             'desc' => 'Pesanan sudah diambil. Terima kasih!'],
];

$currentStep = 0;
if ($order) {
    $currentStep = match($order->status) {
        'paid'    => 0,
        'making'  => 1,
        'ready'   => 2,
        'done'    => 3,
        default   => 0,
    };
}
@endphp

{{-- ===================== SINGLE ORDER VIEW (with ?order_id=) ===================== --}}
@if($order)
<div x-data="{
    orderStatus: '{{ $order->status }}',
    orderId: '{{ $order->order_id }}',
    currentStep: {{ $currentStep }},
    steps: {{ Illuminate\Support\Js::from($steps) }},
    polling: null,
    statusLabels: { pending: 'Menunggu', paid: 'Diproses', making: 'Dibuat', ready: 'Siap', done: 'Selesai', cancelled: 'Batal' },
    
    init() {
        // Poll every 8 seconds for live status
        this.polling = setInterval(() => this.checkStatus(), 8000);
    },
    
    async checkStatus() {
        try {
            const res = await fetch('/api/orders/' + this.orderId + '/status');
            if (!res.ok) return;
            const data = await res.json();
            if (data.status !== this.orderStatus) {
                this.orderStatus = data.status;
                this.updateStep();
            }
        } catch(e) { /* silent */ }
    },
    
    updateStep() {
        const map = { paid: 0, making: 1, ready: 2, done: 3 };
        this.currentStep = map[this.orderStatus] ?? 0;
        // Stop polling when done
        if (this.orderStatus === 'done' || this.orderStatus === 'cancelled') {
            clearInterval(this.polling);
        }
    },
    
    destroy() { clearInterval(this.polling); },
    
    get currentLabel() { return this.steps[this.currentStep]?.label || this.statusLabels[this.orderStatus] || ''; },
}" class="min-h-screen pb-32 md:pb-8">

    {{-- HEADER --}}
    <div class="bg-white border-b border-[var(--c-lt)]/30">
        <div class="max-w-lg mx-auto px-4 py-5">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <a href="{{ route('order.status') }}" class="w-9 h-9 rounded-xl border border-[var(--c-lt)] flex items-center justify-center hover:bg-[var(--c-bg)] transition-colors duration-200">
                        <i data-lucide="arrow-left" class="w-4 h-4 text-[var(--c-dk)]"></i>
                    </a>
                    <div>
                        <h1 class="text-lg font-bold text-[var(--c-dk)]">Detail Pesanan</h1>
                        <p class="text-xs text-[var(--c-md)]/60">{{ $order->order_id }}</p>
                    </div>
                </div>
                <div class="flex flex-col items-end">
                    <span class="text-xs text-[var(--c-md)]/60">Meja</span>
                    <span class="text-2xl font-extrabold text-[var(--c-dk)]">{{ $order->table_number }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-lg mx-auto px-4 py-6 space-y-5">

        {{-- LIVE STATUS CARD --}}
        <div class="relative overflow-hidden bg-gradient-to-br from-[var(--c-dk)] to-[var(--c-md)] rounded-3xl p-6 text-center shadow-lg text-white">
            {{-- Animated background circles --}}
            <div class="absolute -top-10 -right-10 w-40 h-40 bg-white/5 rounded-full"></div>
            <div class="absolute -bottom-8 -left-8 w-32 h-32 bg-white/5 rounded-full"></div>
            
            <template x-if="orderStatus !== 'cancelled'">
                <div class="relative z-10">
                    <div class="w-16 h-16 rounded-full bg-white/20 flex items-center justify-center mx-auto mb-3 text-3xl">
                        <span x-text="{'pending':'⏳','paid':'💳','making':'☕','ready':'✅','done':'🎉'}[orderStatus] || '📦'"></span>
                    </div>
                    <p class="text-[var(--c-lt)] text-xs uppercase tracking-widest font-medium mb-1"
                       x-text="statusLabels[orderStatus] || ''"></p>
                    <h2 class="text-2xl font-extrabold text-white mb-1">Halo, {{ $order->customer_name }}!</h2>
                    <p class="text-[var(--c-lt)]/80 text-sm" x-text="steps[currentStep]?.desc || 'Menunggu proses...'"></p>
                    <div class="mt-4 inline-flex items-center gap-2 bg-white/10 backdrop-blur-sm border border-white/20 px-4 py-2 rounded-full">
                        <span class="w-2 h-2 rounded-full animate-pulse"
                              :class="orderStatus === 'done' ? 'bg-gray-300' : 'bg-green-400'"></span>
                        <span class="text-sm font-semibold text-[var(--c-lt)]" x-text="currentLabel"></span>
                        <span x-show="orderStatus !== 'done'" class="text-[10px] text-[var(--c-lt)]/50">● live</span>
                    </div>
                </div>
            </template>

            <template x-if="orderStatus === 'cancelled'">
                <div class="relative z-10">
                    <div class="w-16 h-16 rounded-full bg-red-400/30 flex items-center justify-center mx-auto mb-3 text-3xl">❌</div>
                    <h2 class="text-xl font-extrabold text-white mb-1">Pesanan Dibatalkan</h2>
                    <p class="text-red-200 text-sm">Pembayaran tidak berhasil atau kedaluwarsa.</p>
                </div>
            </template>
        </div>

        {{-- PROGRESS STEPPER --}}
        <div x-show="orderStatus !== 'cancelled' && orderStatus !== 'pending'" 
             class="bg-white rounded-2xl border border-[var(--c-lt)]/30 p-6 shadow-sm"
             x-transition>
            <h2 class="font-bold text-[var(--c-dk)] text-sm mb-5">Progres Pesanan</h2>
            <div class="relative">
                <div class="absolute left-5 top-5 bottom-5 w-0.5 bg-[var(--c-lt)]/30"></div>
                <div class="absolute left-5 top-5 w-0.5 bg-[var(--c-dk)] transition-all duration-700 ease-in-out"
                     :style="`height: calc(${(currentStep / (steps.length - 1)) * 100}% - 10px)`"></div>
                <div class="space-y-6">
                    <template x-for="(step, idx) in steps" :key="step.key">
                        <div class="flex items-start gap-4 relative z-10">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 transition-all duration-500"
                                 :class="idx <= currentStep ? 'bg-[var(--c-dk)] shadow-md shadow-[var(--c-dk)]/20' : 'bg-[var(--c-lt)]/30'">
                                <template x-if="idx < currentStep">
                                    <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                </template>
                                <template x-if="idx === currentStep">
                                    <span class="w-2.5 h-2.5 rounded-full bg-white animate-pulse"></span>
                                </template>
                                <template x-if="idx > currentStep">
                                    <span class="w-2.5 h-2.5 rounded-full bg-[var(--c-lt)]/40"></span>
                                </template>
                            </div>
                            <div class="flex-1 pb-2">
                                <p class="font-bold text-sm transition-colors duration-300"
                                   :class="idx <= currentStep ? 'text-[var(--c-dk)]' : 'text-[var(--c-md)]/40'"
                                   x-text="step.label"></p>
                                <p class="text-xs mt-0.5 transition-colors duration-300"
                                   :class="idx === currentStep ? 'text-[var(--c-md)]' : 'text-[var(--c-md)]/40'"
                                   x-text="step.desc"></p>
                                <p class="text-[10px] text-[var(--c-dk)] font-medium mt-1" x-show="idx === currentStep && orderStatus !== 'done'">● Sekarang</p>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        {{-- ORDER DETAIL --}}
        @if(count($order->items) > 0)
        <div class="bg-white rounded-2xl border border-[var(--c-lt)]/30 overflow-hidden shadow-sm">
            <div class="px-5 py-4 border-b border-[var(--c-lt)]/20 flex items-center justify-between">
                <h2 class="font-bold text-[var(--c-dk)] text-sm">Detail Pesanan</h2>
                <span class="badge bg-[var(--c-bg)] text-[var(--c-md)]">{{ count($order->items) }} item</span>
            </div>
            <div class="p-4 space-y-3">
                @foreach($order->items as $item)
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl overflow-hidden bg-gradient-to-br from-[var(--c-dk)] to-[var(--c-md)] flex items-center justify-center shrink-0">
                        @if(!empty($item['image_url']))
                            <img src="{{ $item['image_url'] }}" class="w-full h-full object-cover">
                        @else
                            <span class="text-xl">☕</span>
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-[var(--c-dk)] text-sm truncate">{{ $item['name'] }}</p>
                        <p class="text-xs text-[var(--c-md)]/60">
                            {{ !empty($item['variant']) ? $item['variant'] . ' · ' : '' }}{{ $item['qty'] }}x
                        </p>
                    </div>
                    <span class="font-bold text-[var(--c-dk)] text-sm">Rp {{ number_format($item['price'] * $item['qty'], 0, ',', '.') }}</span>
                </div>
                @endforeach
            </div>
            <div class="px-5 py-4 border-t border-[var(--c-lt)]/20 bg-[var(--c-bg)]/30 space-y-1">
                <div class="flex justify-between text-xs text-[var(--c-md)]/70">
                    <span>Subtotal</span>
                    <span>Rp {{ number_format($order->subtotal, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-xs text-[var(--c-md)]/70">
                    <span>Pajak (10%)</span>
                    <span>Rp {{ number_format($order->tax, 0, ',', '.') }}</span>
                </div>
                <div class="flex items-center justify-between pt-2 border-t border-[var(--c-lt)]/20">
                    <span class="font-bold text-[var(--c-dk)] text-sm">Total Dibayar</span>
                    <span class="font-extrabold text-[var(--c-dk)] text-base">Rp {{ number_format($order->total, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
        @endif

        {{-- ACTION BUTTONS --}}
        <div class="bg-white rounded-2xl border border-[var(--c-lt)]/30 p-4 shadow-sm">
            <div class="flex gap-2">
                <a :href="`{{ route('menu') }}${$store.cart.tableNumber ? '?table=' + $store.cart.tableNumber : ''}`"
                   class="flex-1 btn-primary justify-center py-3 text-xs">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                    Pesan Lagi
                </a>
                <a href="{{ route('order.status') }}" class="flex-1 btn-outline justify-center py-3 text-xs">
                    <i data-lucide="list" class="w-3.5 h-3.5"></i>
                    Semua Pesanan
                </a>
            </div>
        </div>

    </div>
</div>

@else
{{-- ===================== ORDER HISTORY VIEW (no ?order_id) ===================== --}}
<div x-data="{
    orders: [],
    loading: true,
    
    init() {
        this.loadHistory();
        // Poll every 15 seconds
        setInterval(() => this.loadHistory(), 15000);
    },
    
    async loadHistory() {
        const history = JSON.parse(localStorage.getItem('skena_order_history') || '[]');
        if (history.length === 0) {
            this.loading = false;
            return;
        }
        try {
            const params = history.map(id => 'order_ids[]=' + encodeURIComponent(id)).join('&');
            const res = await fetch('/api/orders/history?' + params);
            if (res.ok) {
                this.orders = await res.json();
            }
        } catch(e) { /* silent */ }
        this.loading = false;
    },
    
    statusBadge(status) {
        const map = {
            pending:   { label: 'Menunggu',  class: 'bg-yellow-100 text-yellow-700', icon: '⏳' },
            paid:      { label: 'Diproses',  class: 'bg-blue-100 text-blue-700',     icon: '💳' },
            making:    { label: 'Dibuat',    class: 'bg-orange-100 text-orange-700', icon: '☕' },
            ready:     { label: 'Siap',      class: 'bg-green-100 text-green-700',   icon: '✅' },
            done:      { label: 'Selesai',   class: 'bg-gray-100 text-gray-600',     icon: '🎉' },
            cancelled: { label: 'Batal',     class: 'bg-red-100 text-red-600',       icon: '❌' },
        };
        return map[status] || { label: status, class: 'bg-gray-100 text-gray-600', icon: '📦' };
    },
    
    isActive(status) {
        return ['paid', 'making', 'ready'].includes(status);
    }
}" class="min-h-screen pb-32 md:pb-8">

    {{-- HEADER --}}
    <div class="bg-white border-b border-[var(--c-lt)]/30">
        <div class="max-w-lg mx-auto px-4 py-5">
            <div class="flex items-center gap-3">
                <a href="{{ route('home') }}" class="w-9 h-9 rounded-xl border border-[var(--c-lt)] flex items-center justify-center hover:bg-[var(--c-bg)] transition-colors duration-200">
                    <i data-lucide="arrow-left" class="w-4 h-4 text-[var(--c-dk)]"></i>
                </a>
                <div>
                    <h1 class="text-lg font-bold text-[var(--c-dk)]">Pesanan Saya</h1>
                    <p class="text-xs text-[var(--c-md)]/60">Riwayat & status pesanan real-time</p>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-lg mx-auto px-4 py-6 space-y-4">

        {{-- LOADING SKELETON --}}
        <template x-if="loading">
            <div class="space-y-4">
                <template x-for="i in 3" :key="i">
                    <div class="bg-white rounded-2xl border border-[var(--c-lt)]/30 p-5 animate-pulse">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 rounded-xl bg-[var(--c-lt)]/30"></div>
                            <div class="flex-1">
                                <div class="h-3 bg-[var(--c-lt)]/30 rounded w-3/4 mb-2"></div>
                                <div class="h-2 bg-[var(--c-lt)]/20 rounded w-1/2"></div>
                            </div>
                            <div class="h-6 w-16 bg-[var(--c-lt)]/30 rounded-full"></div>
                        </div>
                        <div class="h-px bg-[var(--c-lt)]/20 mb-3"></div>
                        <div class="flex justify-between">
                            <div class="h-2 bg-[var(--c-lt)]/20 rounded w-20"></div>
                            <div class="h-3 bg-[var(--c-lt)]/30 rounded w-24"></div>
                        </div>
                    </div>
                </template>
            </div>
        </template>

        {{-- EMPTY STATE --}}
        <div x-show="!loading && orders.length === 0" x-cloak class="text-center py-20">
            <div class="w-20 h-20 rounded-3xl bg-[var(--c-bg)] flex items-center justify-center mx-auto mb-5 border border-[var(--c-lt)]/30">
                <i data-lucide="receipt" class="w-9 h-9 text-[var(--c-lt)]"></i>
            </div>
            <h3 class="font-bold text-[var(--c-dk)] text-lg mb-2">Belum Ada Pesanan</h3>
            <p class="text-sm text-[var(--c-md)]/60 max-w-xs mx-auto mb-6">Pesanan yang kamu buat akan muncul di sini. Kamu bisa pantau statusnya secara real-time!</p>
            <a :href="`{{ route('menu') }}${$store.cart.tableNumber ? '?table=' + $store.cart.tableNumber : ''}`"
               class="btn-primary justify-center py-3 text-sm inline-flex px-8">
                <i data-lucide="coffee" class="w-4 h-4"></i>
                Mulai Pesan
            </a>
        </div>

        {{-- ACTIVE ORDERS SECTION --}}
        <template x-if="!loading && orders.filter(o => isActive(o.status)).length > 0">
            <div>
                <div class="flex items-center gap-2 mb-3">
                    <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
                    <h2 class="font-bold text-[var(--c-dk)] text-sm">Pesanan Aktif</h2>
                </div>
                <div class="space-y-3">
                    <template x-for="order in orders.filter(o => isActive(o.status))" :key="order.id">
                        <a :href="'/order/status?order_id=' + order.id"
                           class="block bg-white rounded-2xl border-2 border-[var(--c-dk)]/10 p-4 shadow-sm hover:shadow-md hover:border-[var(--c-dk)]/30 transition-all duration-200 group">
                            <div class="flex items-center justify-between mb-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-[var(--c-dk)] to-[var(--c-md)] flex items-center justify-center text-lg"
                                         x-text="statusBadge(order.status).icon"></div>
                                    <div>
                                        <p class="font-bold text-[var(--c-dk)] text-sm" x-text="order.id"></p>
                                        <p class="text-[10px] text-[var(--c-md)]/60" x-text="'Meja ' + order.table + ' · ' + order.time_ago"></p>
                                    </div>
                                </div>
                                <span class="text-xs font-bold px-2.5 py-1 rounded-full flex items-center gap-1"
                                      :class="statusBadge(order.status).class">
                                    <span class="w-1.5 h-1.5 rounded-full bg-current animate-pulse"></span>
                                    <span x-text="statusBadge(order.status).label"></span>
                                </span>
                            </div>
                            <div class="h-px bg-[var(--c-lt)]/20 mb-3"></div>
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-[var(--c-md)]/60" x-text="order.items_count + ' item'"></span>
                                <span class="font-extrabold text-[var(--c-dk)] text-sm" x-text="'Rp ' + order.total.toLocaleString('id-ID')"></span>
                            </div>
                            <div class="mt-2 flex items-center justify-end gap-1 text-xs text-[var(--c-md)] group-hover:text-[var(--c-dk)] transition-colors">
                                <span>Lihat Detail</span>
                                <i data-lucide="chevron-right" class="w-3 h-3 group-hover:translate-x-0.5 transition-transform"></i>
                            </div>
                        </a>
                    </template>
                </div>
            </div>
        </template>

        {{-- PAST ORDERS SECTION --}}
        <template x-if="!loading && orders.filter(o => !isActive(o.status)).length > 0">
            <div>
                <h2 class="font-bold text-[var(--c-dk)] text-sm mb-3 mt-4">Riwayat Pesanan</h2>
                <div class="space-y-3">
                    <template x-for="order in orders.filter(o => !isActive(o.status))" :key="order.id">
                        <a :href="'/order/status?order_id=' + order.id"
                           class="block bg-white rounded-2xl border border-[var(--c-lt)]/30 p-4 shadow-sm hover:shadow-md transition-all duration-200 group">
                            <div class="flex items-center justify-between mb-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-[var(--c-bg)] flex items-center justify-center text-lg"
                                         x-text="statusBadge(order.status).icon"></div>
                                    <div>
                                        <p class="font-bold text-[var(--c-dk)] text-sm" x-text="order.id"></p>
                                        <p class="text-[10px] text-[var(--c-md)]/60" x-text="order.date_formatted + ' · Meja ' + order.table"></p>
                                    </div>
                                </div>
                                <span class="text-xs font-bold px-2.5 py-1 rounded-full"
                                      :class="statusBadge(order.status).class"
                                      x-text="statusBadge(order.status).label"></span>
                            </div>
                            <div class="h-px bg-[var(--c-lt)]/20 mb-3"></div>
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-[var(--c-md)]/60" x-text="order.items_count + ' item'"></span>
                                <span class="font-extrabold text-[var(--c-dk)] text-sm" x-text="'Rp ' + order.total.toLocaleString('id-ID')"></span>
                            </div>
                        </a>
                    </template>
                </div>
            </div>
        </template>

        {{-- QUICK ACTIONS --}}
        <div x-show="!loading" class="bg-white rounded-2xl border border-[var(--c-lt)]/30 p-4 shadow-sm">
            <div class="flex gap-2">
                <a :href="`{{ route('menu') }}${$store.cart.tableNumber ? '?table=' + $store.cart.tableNumber : ''}`"
                   class="flex-1 btn-primary justify-center py-3 text-xs">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                    Pesan Lagi
                </a>
                <a href="{{ route('home') }}" class="flex-1 btn-outline justify-center py-3 text-xs">
                    <i data-lucide="home" class="w-3.5 h-3.5"></i>
                    Beranda
                </a>
            </div>
        </div>

    </div>
</div>
@endif

@endsection
