@extends('layouts.app')

@section('title', 'Checkout — Skena Coffee')
@section('meta_description', 'Selesaikan pembayaran pesanan kamu di Skena Coffee')

@push('head')
{{-- Midtrans Snap.js (auto sandbox/production) --}}
<script src="{{ config('midtrans.is_production') ? 'https://app.midtrans.com/snap/snap.js' : 'https://app.sandbox.midtrans.com/snap/snap.js' }}"
        data-client-key="{{ config('midtrans.client_key') }}"></script>
@endpush

@section('content')

<div x-data="{
    items: [],
    customerName: '',
    customerPhone: '',
    customerEmail: '',
    isLoading: false,
    errorMsg: '',
    get tableNumber() { return $store.cart.tableNumber; },
    get total() { return $store.cart.total; },
    get tax() { return Math.round($store.cart.total * 0.1); },
    get grandTotal() { return $store.cart.total + this.tax; },
    get canCheckout() {
        return this.customerName.trim().length >= 2
            && this.items.length > 0
            && !this.isLoading;
    },
    init() { this.items = $store.cart.items; },
    async pay() {
        if (!this.canCheckout) {
            this.errorMsg = 'Mohon isi nama lengkap terlebih dahulu.';
            return;
        }
        this.errorMsg = '';
        this.isLoading = true;

        try {
            const res = await fetch('{{ route('order.create_token') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                },
                body: JSON.stringify({
                    customer_name:  this.customerName,
                    customer_phone: this.customerPhone,
                    customer_email: this.customerEmail,
                    table_number:   this.tableNumber,
                    items: this.items.map(i => ({
                        id:        i.id,
                        name:      i.name,
                        price:     i.price,
                        qty:       i.qty,
                        variant:   i.variant || null,
                        image_url: i.image_url || null,
                    })),
                }),
            });

            const data = await res.json();
            if (!data.success) throw new Error(data.message || 'Gagal membuat transaksi');

            this.isLoading = false;

            // Open Midtrans Snap popup
            const orderId = data.order_id;
            window.snap.pay(data.token, {
                onSuccess: async (result) => {
                    // Payment confirmed by Midtrans
                    await fetch('{{ route('order.update_status') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        },
                        body: JSON.stringify({ order_id: orderId, status: 'paid' }),
                    });
                    // Save order to history in localStorage
                    let history = JSON.parse(localStorage.getItem('skena_order_history') || '[]');
                    if (!history.includes(orderId)) {
                        history.unshift(orderId);
                        // Keep last 50 orders max
                        if (history.length > 50) history = history.slice(0, 50);
                        localStorage.setItem('skena_order_history', JSON.stringify(history));
                    }
                    $store.cart.clear();
                    window.location.href = '{{ route('order.status') }}?order_id=' + orderId;
                },
                onPending: (result) => {
                    let history = JSON.parse(localStorage.getItem('skena_order_history') || '[]');
                    if (!history.includes(orderId)) {
                        history.unshift(orderId);
                        if (history.length > 50) history = history.slice(0, 50);
                        localStorage.setItem('skena_order_history', JSON.stringify(history));
                    }
                    $store.cart.clear();
                    window.location.href = '{{ route('order.status') }}?order_id=' + orderId + '&status=pending';
                },
                onError: (result) => {
                    this.errorMsg = 'Pembayaran gagal. Silakan coba lagi.';
                },
                onClose: () => {
                    // User closed popup without finishing
                    this.errorMsg = 'Popup ditutup sebelum selesai. Silakan coba bayar lagi.';
                },
            });
        } catch (err) {
            this.isLoading = false;
            this.errorMsg = err.message || 'Terjadi kesalahan. Coba lagi.';
        }
    }
}" class="min-h-screen pb-32 md:pb-8">

    {{-- HEADER --}}
    <div class="bg-white border-b border-[var(--c-lt)]/30">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-5">
            <div class="flex items-center gap-3">
                <a href="{{ route('cart') }}" class="w-9 h-9 rounded-xl border border-[var(--c-lt)] flex items-center justify-center hover:bg-[var(--c-bg)] transition-colors duration-200">
                    <i data-lucide="arrow-left" class="w-4 h-4 text-[var(--c-dk)]"></i>
                </a>
                <div>
                    <h1 class="text-lg font-bold text-[var(--c-dk)]">Checkout</h1>
                    <p class="text-xs text-[var(--c-md)]/60">Konfirmasi &amp; bayar pesananmu</p>
                </div>
            </div>

            {{-- Progress Steps --}}
            <div class="flex items-center gap-2 mt-4">
                <div class="flex items-center gap-2">
                    <div class="w-6 h-6 rounded-full bg-[var(--c-dk)] flex items-center justify-center">
                        <i data-lucide="check" class="w-3 h-3 text-white"></i>
                    </div>
                    <span class="text-xs font-medium text-[var(--c-dk)]">Keranjang</span>
                </div>
                <div class="flex-1 h-px bg-[var(--c-dk)]/30"></div>
                <div class="flex items-center gap-2">
                    <div class="w-6 h-6 rounded-full bg-[var(--c-dk)] flex items-center justify-center">
                        <span class="text-[10px] font-bold text-white">2</span>
                    </div>
                    <span class="text-xs font-medium text-[var(--c-dk)]">Checkout</span>
                </div>
                <div class="flex-1 h-px bg-[var(--c-lt)]/40"></div>
                <div class="flex items-center gap-2">
                    <div class="w-6 h-6 rounded-full border-2 border-[var(--c-lt)] flex items-center justify-center">
                        <span class="text-[10px] font-medium text-[var(--c-lt)]">3</span>
                    </div>
                    <span class="text-xs text-[var(--c-lt)]">Pembayaran</span>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- LEFT — Order Summary & Forms --}}
            <div class="lg:col-span-2 space-y-4">

                {{-- Order Summary --}}
                <div class="bg-white rounded-2xl border border-[var(--c-lt)]/30 overflow-hidden shadow-sm">
                    <div class="px-5 py-4 border-b border-[var(--c-lt)]/20 flex items-center justify-between">
                        <h2 class="font-bold text-[var(--c-dk)] text-sm">Ringkasan Pesanan</h2>
                        <a href="{{ route('cart') }}" class="text-xs text-[var(--c-md)] hover:underline">Edit</a>
                    </div>

                    <div class="p-4 space-y-3" x-show="items.length > 0">
                        <template x-for="item in items" :key="item.id">
                            <div class="flex items-center gap-3 py-2 border-b border-[var(--c-lt)]/10 last:border-b-0">
                                <div class="w-10 h-10 rounded-xl overflow-hidden bg-gradient-to-br from-[var(--c-dk)] to-[var(--c-md)] flex items-center justify-center shrink-0">
                                    <template x-if="item.image_url">
                                        <img :src="item.image_url" class="w-full h-full object-cover">
                                    </template>
                                    <template x-if="!item.image_url">
                                        <span class="text-xl">☕</span>
                                    </template>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="font-semibold text-[var(--c-dk)] text-sm truncate" x-text="item.name"></p>
                                    <template x-if="item.variant">
                                        <p class="text-xs text-[var(--c-md)]/60" x-text="item.variant + ' · ' + item.qty + 'x'"></p>
                                    </template>
                                    <template x-if="!item.variant">
                                        <p class="text-xs text-[var(--c-md)]/60" x-text="item.qty + 'x'"></p>
                                    </template>
                                </div>
                                <span class="font-bold text-[var(--c-dk)] text-sm shrink-0" x-text="'Rp ' + (item.price * item.qty).toLocaleString('id-ID')"></span>
                            </div>
                        </template>
                    </div>

                    {{-- Empty fallback --}}
                    <div class="p-6 text-center" x-show="items.length === 0">
                        <p class="text-sm text-[var(--c-md)]/60">Keranjang kosong</p>
                        <a href="{{ route('menu') }}" class="text-sm text-[var(--c-dk)] font-bold hover:underline mt-1 block">Pilih Menu</a>
                    </div>
                </div>

                {{-- Customer Info Form --}}
                <div class="bg-white rounded-2xl border border-[var(--c-lt)]/30 p-5 shadow-sm">
                    <h2 class="font-bold text-[var(--c-dk)] text-sm mb-4">Informasi Pelanggan</h2>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-[var(--c-md)] mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                            <input type="text" x-model="customerName" placeholder="Masukkan nama..." class="input-field w-full" required>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-[var(--c-md)] mb-1">Nomor WhatsApp</label>
                                <input type="tel" x-model="customerPhone" placeholder="08..." class="input-field w-full">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-[var(--c-md)] mb-1">Nomor Meja</label>
                                <input type="text" :value="tableNumber" readonly class="input-field w-full bg-[var(--c-bg)]/50 cursor-not-allowed font-bold">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-[var(--c-md)] mb-1">Email (Opsional)</label>
                            <input type="email" x-model="customerEmail" placeholder="nama@email.com" class="input-field w-full">
                        </div>
                    </div>
                </div>

                {{-- Payment Method --}}
                <div class="bg-white rounded-2xl border border-[var(--c-lt)]/30 p-5 shadow-sm">
                    <h2 class="font-bold text-[var(--c-dk)] text-sm mb-4">Metode Pembayaran</h2>
                    <label class="flex items-center gap-4 p-4 rounded-xl border-2 border-[var(--c-dk)] bg-[var(--c-bg)]/50">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-[var(--c-dk)] to-[var(--c-md)] flex items-center justify-center shrink-0">
                            <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/>
                                <rect x="3" y="14" width="7" height="7" rx="1"/>
                                <path d="M14 14h1v1h-1zm3 0h1v1h-1zm0 3h1v1h-1zm-3 0h1v1h-1z"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="font-bold text-[var(--c-dk)] text-sm">QRIS</p>
                            <p class="text-xs text-[var(--c-md)]/60">Scan QR pakai aplikasi apapun — didukung Midtrans</p>
                        </div>
                        <div class="w-5 h-5 rounded-full border-2 border-[var(--c-dk)] bg-[var(--c-dk)] flex items-center justify-center shrink-0">
                            <div class="w-2 h-2 rounded-full bg-white"></div>
                        </div>
                    </label>
                </div>
            </div>

            {{-- RIGHT — Total & Pay --}}
            <div class="lg:col-span-1">
                <div class="bg-white rounded-2xl border border-[var(--c-lt)]/30 p-5 shadow-sm sticky top-28">
                    <h2 class="font-bold text-[var(--c-dk)] mb-4 pb-3 border-b border-[var(--c-lt)]/20">Total Pembayaran</h2>

                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between text-[var(--c-md)]/70">
                            <span>Subtotal</span>
                            <span x-text="'Rp ' + total.toLocaleString('id-ID')"></span>
                        </div>
                        <div class="flex justify-between text-[var(--c-md)]/70">
                            <span>Pajak (10%)</span>
                            <span x-text="'Rp ' + tax.toLocaleString('id-ID')"></span>
                        </div>
                        <div class="flex justify-between text-[var(--c-md)]/70">
                            <span>Diskon</span>
                            <span class="text-green-600 font-medium">- Rp 0</span>
                        </div>
                    </div>

                    <div class="mt-4 pt-4 border-t border-[var(--c-lt)]/20">
                        <div class="flex justify-between items-center mb-1">
                            <span class="font-extrabold text-[var(--c-dk)]">Total</span>
                            <span class="font-extrabold text-xl text-[var(--c-dk)]" x-text="'Rp ' + grandTotal.toLocaleString('id-ID')"></span>
                        </div>
                        <p class="text-xs text-[var(--c-md)]/50 mb-5">Sudah termasuk pajak</p>

                        {{-- Error Message --}}
                        <div x-show="errorMsg" x-cloak
                             class="mb-4 p-3 bg-red-50 border border-red-200 rounded-xl flex items-start gap-2">
                            <i data-lucide="alert-circle" class="w-4 h-4 text-red-500 shrink-0 mt-0.5"></i>
                            <p class="text-xs text-red-600" x-text="errorMsg"></p>
                        </div>

                        {{-- Pay Button --}}
                        <button id="btn-pay"
                                @click="pay()"
                                :disabled="!canCheckout"
                                :class="canCheckout ? 'btn-primary' : 'btn-primary opacity-50 cursor-not-allowed'"
                                class="w-full justify-center py-4 rounded-2xl text-sm">
                            <template x-if="!isLoading">
                                <span class="flex items-center justify-center gap-2">
                                    <i data-lucide="credit-card" class="w-4 h-4"></i>
                                    Bayar Sekarang
                                </span>
                            </template>
                            <template x-if="isLoading">
                                <span class="flex items-center justify-center gap-2">
                                    <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                    </svg>
                                    Memproses...
                                </span>
                            </template>
                        </button>

                        <p class="text-[10px] text-center text-[var(--c-md)]/50 mt-3 leading-relaxed">
                            Dengan melanjutkan, kamu menyetujui syarat dan ketentuan Skena Coffee
                        </p>
                    </div>

                    {{-- Security Badge --}}
                    <div class="mt-4 pt-4 border-t border-[var(--c-lt)]/20 flex items-center gap-2 text-[var(--c-md)]/50">
                        <i data-lucide="shield-check" class="w-4 h-4"></i>
                        <span class="text-xs">Transaksi aman via Midtrans</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
