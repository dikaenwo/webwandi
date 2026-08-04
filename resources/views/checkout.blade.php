@extends('layouts.app')

@section('title', 'Checkout — Skena Coffee')
@section('meta_description', 'Selesaikan pembayaran pesanan kamu di Skena Coffee')

@push('head')
{{-- Midtrans Snap.js for embed fallback --}}
<script src="{{ config('midtrans.is_production') ? 'https://app.midtrans.com/snap/snap.js' : 'https://app.sandbox.midtrans.com/snap/snap.js' }}"
        data-client-key="{{ config('midtrans.client_key') }}"></script>
@endpush

@section('content')

<div x-data="{
    // === Checkout State ===
    items: [],
    customerName: '',
    customerPhone: '',
    customerEmail: '',
    isLoading: false,
    errorMsg: '',

    // === Payment State ===
    paymentMode: '',       // 'qris' or 'snap'
    showPayment: false,
    currentOrderId: '',
    qrUrl: '',
    qrisTotal: 0,
    countdownText: '',
    countdownInterval: null,
    pollingInterval: null,
    paymentSuccess: false,

    get tableNumber() { return $store.cart.tableNumber; },
    get total() { return $store.cart.total; },
    get tax() { return Math.round($store.cart.total * 0.1); },
    get grandTotal() { return $store.cart.total + this.tax; },
    get canCheckout() {
        return this.customerName.trim().length >= 2
            && this.items.length > 0
            && !this.isLoading
            && !this.showPayment;
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
            this.currentOrderId = data.order_id;
            this.qrisTotal = data.total || this.grandTotal;

            // Save order to history
            let history = JSON.parse(localStorage.getItem('skena_order_history') || '[]');
            if (!history.includes(data.order_id)) {
                history.unshift(data.order_id);
                if (history.length > 50) history = history.slice(0, 50);
                localStorage.setItem('skena_order_history', JSON.stringify(history));
            }

            // Use Snap embed — keeps payment UI on page (no mobile redirect)
            this.showPayment = true;
            this.paymentMode = 'snap';

            this.$nextTick(() => {
                const container = document.getElementById('snap-container');
                if (container) container.innerHTML = '';

                window.snap.embed(data.token, {
                    embedId: 'snap-container',
                    onSuccess: async (result) => {
                        await fetch('{{ route('order.update_status') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                            },
                            body: JSON.stringify({ order_id: this.currentOrderId, status: 'paid' }),
                        });
                        $store.cart.clear();
                        window.location.href = '{{ route('order.status') }}?order_id=' + this.currentOrderId;
                    },
                    onPending: (result) => {
                        $store.cart.clear();
                        window.location.href = '{{ route('order.status') }}?order_id=' + this.currentOrderId + '&status=pending';
                    },
                    onError: (result) => {
                        this.resetPayment();
                        this.errorMsg = 'Pembayaran gagal. Silakan coba lagi.';
                    },
                    onClose: () => {
                        this.resetPayment();
                        this.errorMsg = 'Pembayaran dibatalkan.';
                    },
                });
            });

        } catch (err) {
            this.isLoading = false;
            this.errorMsg = err.message || 'Terjadi kesalahan. Coba lagi.';
        }
    },

    startCountdown(seconds) {
        let remaining = seconds;
        this.updateCountdownDisplay(remaining);
        this.countdownInterval = setInterval(() => {
            remaining--;
            if (remaining <= 0) {
                clearInterval(this.countdownInterval);
                this.countdownText = 'Kedaluwarsa';
                this.handleExpired();
                return;
            }
            this.updateCountdownDisplay(remaining);
        }, 1000);
    },

    updateCountdownDisplay(seconds) {
        const m = Math.floor(seconds / 60);
        const s = seconds % 60;
        this.countdownText = String(m).padStart(2,'0') + ':' + String(s).padStart(2,'0');
    },

    startPolling() {
        this.pollingInterval = setInterval(async () => {
            try {
                const res = await fetch('/api/orders/' + this.currentOrderId + '/status');
                if (!res.ok) return;
                const data = await res.json();

                if (data.status === 'paid') {
                    this.paymentSuccess = true;
                    this.cleanup();
                    $store.cart.clear();
                    setTimeout(() => {
                        window.location.href = '{{ route('order.status') }}?order_id=' + this.currentOrderId;
                    }, 2000);
                } else if (data.status === 'cancelled') {
                    this.handleExpired();
                }
            } catch(e) { /* silent */ }
        }, 3000);
    },

    handleExpired() {
        this.cleanup();
        this.resetPayment();
        this.errorMsg = 'Pembayaran kedaluwarsa. Silakan coba lagi.';
    },

    resetPayment() {
        this.showPayment = false;
        this.paymentMode = '';
        this.qrUrl = '';
        this.currentOrderId = '';
        const container = document.getElementById('snap-container');
        if (container) container.innerHTML = '';
    },

    cleanup() {
        if (this.countdownInterval) clearInterval(this.countdownInterval);
        if (this.pollingInterval) clearInterval(this.pollingInterval);
    },

    destroy() { this.cleanup(); }
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
                        <template x-if="showPayment"><i data-lucide="check" class="w-3 h-3 text-white"></i></template>
                        <template x-if="!showPayment"><span class="text-[10px] font-bold text-white">2</span></template>
                    </div>
                    <span class="text-xs font-medium text-[var(--c-dk)]">Checkout</span>
                </div>
                <div class="flex-1 h-px transition-colors duration-300" :class="showPayment ? 'bg-[var(--c-dk)]/30' : 'bg-[var(--c-lt)]/40'"></div>
                <div class="flex items-center gap-2">
                    <div class="w-6 h-6 rounded-full flex items-center justify-center transition-colors duration-300"
                         :class="showPayment ? 'bg-[var(--c-dk)]' : 'border-2 border-[var(--c-lt)]'">
                        <span class="text-[10px] font-medium" :class="showPayment ? 'text-white font-bold' : 'text-[var(--c-lt)]'">3</span>
                    </div>
                    <span class="text-xs transition-colors duration-300" :class="showPayment ? 'font-medium text-[var(--c-dk)]' : 'text-[var(--c-lt)]'">Pembayaran</span>
                </div>
            </div>
        </div>
    </div>

    {{-- ===================== PAYMENT VIEW ===================== --}}
    <div x-show="showPayment" x-cloak x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
         class="max-w-lg mx-auto px-4 py-6 space-y-4">

        {{-- ====== SUCCESS OVERLAY ====== --}}
        <template x-if="paymentSuccess">
            <div class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
                <div class="bg-white rounded-3xl p-8 text-center max-w-sm w-full shadow-2xl" style="animation: bounceIn .5s ease-out">
                    <div class="w-20 h-20 rounded-full bg-green-100 flex items-center justify-center mx-auto mb-4">
                        <svg class="w-10 h-10 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-extrabold text-[var(--c-dk)] mb-2">Pembayaran Berhasil!</h3>
                    <p class="text-sm text-[var(--c-md)]/70">Mengalihkan ke halaman status...</p>
                </div>
            </div>
        </template>

        {{-- ====== QRIS CUSTOM QR MODE ====== --}}
        <template x-if="paymentMode === 'qris'">
            <div>
                {{-- QR Card --}}
                <div class="bg-white rounded-3xl border border-[var(--c-lt)]/30 overflow-hidden shadow-lg">
                    <div class="bg-gradient-to-br from-[var(--c-dk)] to-[var(--c-md)] p-5 text-center text-white relative overflow-hidden">
                        <div class="absolute -top-8 -right-8 w-32 h-32 bg-white/5 rounded-full"></div>
                        <div class="relative z-10">
                            <div class="inline-flex items-center gap-2 bg-white/15 border border-white/20 px-3 py-1 rounded-full text-xs font-medium mb-3">
                                <span class="w-2 h-2 rounded-full bg-green-400 animate-pulse"></span>
                                Menunggu Pembayaran
                            </div>
                            <p class="text-2xl font-extrabold" x-text="'Rp ' + qrisTotal.toLocaleString('id-ID')"></p>
                            <p class="text-xs text-white/60 mt-1" x-text="currentOrderId"></p>
                        </div>
                    </div>

                    <div class="p-6 flex flex-col items-center">
                        <div class="relative">
                            <div class="absolute -inset-3 rounded-2xl border-2 border-dashed border-[var(--c-lt)]/40"></div>
                            <div class="relative bg-white p-3 rounded-xl shadow-inner">
                                <img :src="qrUrl" alt="QRIS QR Code" class="w-56 h-56 sm:w-64 sm:h-64 object-contain"
                                     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <div class="w-56 h-56 sm:w-64 sm:h-64 items-center justify-center text-center text-sm text-red-500" style="display:none">
                                    <p>Gagal memuat QR code.<br>Silakan coba lagi.</p>
                                </div>
                            </div>
                        </div>

                        <div class="mt-5 text-center space-y-2">
                            <p class="text-sm font-bold text-[var(--c-dk)]">Scan QR Code dengan aplikasi apapun</p>
                            <p class="text-xs text-[var(--c-md)]/60">GoPay, OVO, DANA, ShopeePay, LinkAja, atau mobile banking</p>
                        </div>

                        <div class="mt-4 flex items-center gap-2 bg-amber-50 border border-amber-200 px-4 py-2.5 rounded-xl">
                            <i data-lucide="clock" class="w-4 h-4 text-amber-600"></i>
                            <span class="text-sm font-bold text-amber-800">Berlaku</span>
                            <span class="text-sm font-mono font-extrabold text-amber-900" x-text="countdownText"></span>
                        </div>

                        <div class="mt-4 flex items-center gap-2 text-[var(--c-md)]/50">
                            <svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            <span class="text-xs">Mendeteksi pembayaran otomatis...</span>
                        </div>
                    </div>

                    <div class="px-6 pb-5">
                        <div class="bg-[var(--c-bg)]/50 rounded-xl p-4 border border-[var(--c-lt)]/20">
                            <p class="text-xs font-bold text-[var(--c-dk)] mb-2">Cara Bayar:</p>
                            <ol class="text-xs text-[var(--c-md)]/70 space-y-1.5 list-decimal list-inside">
                                <li>Buka aplikasi e-wallet atau mobile banking</li>
                                <li>Pilih menu <strong>Scan QR</strong> atau <strong>QRIS</strong></li>
                                <li>Arahkan kamera ke QR code di atas</li>
                                <li>Konfirmasi pembayaran di aplikasi</li>
                            </ol>
                        </div>
                    </div>

                    <div class="px-6 pb-6">
                        <button @click="cleanup(); resetPayment();" class="w-full py-3 rounded-xl border border-[var(--c-lt)] text-sm text-[var(--c-md)] hover:bg-red-50 hover:border-red-200 hover:text-red-600 transition-all duration-200">
                            Batalkan Pembayaran
                        </button>
                    </div>
                </div>

                <div class="flex items-center justify-center gap-2 text-[var(--c-md)]/40 mt-4">
                    <i data-lucide="shield-check" class="w-4 h-4"></i>
                    <span class="text-xs">Pembayaran aman via Midtrans QRIS</span>
                </div>
            </div>
        </template>

        {{-- ====== SNAP EMBED FALLBACK MODE ====== --}}
        <template x-if="paymentMode === 'snap'">
            <div>
                <div class="bg-gradient-to-br from-[var(--c-dk)] to-[var(--c-md)] rounded-2xl p-4 text-center text-white relative overflow-hidden mb-4">
                    <div class="absolute -top-6 -right-6 w-20 h-20 bg-white/5 rounded-full"></div>
                    <div class="relative z-10 flex items-center justify-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-white/15 flex items-center justify-center">
                            <i data-lucide="shield-check" class="w-5 h-5 text-white"></i>
                        </div>
                        <div class="text-left">
                            <p class="text-sm font-bold">Selesaikan Pembayaran</p>
                            <p class="text-xs text-white/60">Pilih metode bayar di bawah</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl border border-[var(--c-lt)]/30 overflow-hidden shadow-sm">
                    <div id="snap-container" class="w-full" style="min-height: 400px;"></div>
                </div>

                <button @click="resetPayment();" class="w-full mt-4 py-3 rounded-xl border border-[var(--c-lt)] text-sm text-[var(--c-md)] hover:bg-red-50 hover:border-red-200 hover:text-red-600 transition-all duration-200">
                    Batalkan Pembayaran
                </button>
            </div>
        </template>
    </div>

    {{-- ===================== CHECKOUT FORM VIEW ===================== --}}
    <div x-show="!showPayment" class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
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
                                    <template x-if="item.image_url"><img :src="item.image_url" class="w-full h-full object-cover"></template>
                                    <template x-if="!item.image_url"><span class="text-xl">☕</span></template>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="font-semibold text-[var(--c-dk)] text-sm truncate" x-text="item.name"></p>
                                    <template x-if="item.variant"><p class="text-xs text-[var(--c-md)]/60" x-text="item.variant + ' · ' + item.qty + 'x'"></p></template>
                                    <template x-if="!item.variant"><p class="text-xs text-[var(--c-md)]/60" x-text="item.qty + 'x'"></p></template>
                                </div>
                                <span class="font-bold text-[var(--c-dk)] text-sm shrink-0" x-text="'Rp ' + (item.price * item.qty).toLocaleString('id-ID')"></span>
                            </div>
                        </template>
                    </div>
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
                        <div class="flex justify-between text-[var(--c-md)]/70"><span>Subtotal</span><span x-text="'Rp ' + total.toLocaleString('id-ID')"></span></div>
                        <div class="flex justify-between text-[var(--c-md)]/70"><span>Pajak (10%)</span><span x-text="'Rp ' + tax.toLocaleString('id-ID')"></span></div>
                        <div class="flex justify-between text-[var(--c-md)]/70"><span>Diskon</span><span class="text-green-600 font-medium">- Rp 0</span></div>
                    </div>
                    <div class="mt-4 pt-4 border-t border-[var(--c-lt)]/20">
                        <div class="flex justify-between items-center mb-1">
                            <span class="font-extrabold text-[var(--c-dk)]">Total</span>
                            <span class="font-extrabold text-xl text-[var(--c-dk)]" x-text="'Rp ' + grandTotal.toLocaleString('id-ID')"></span>
                        </div>
                        <p class="text-xs text-[var(--c-md)]/50 mb-5">Sudah termasuk pajak</p>

                        <div x-show="errorMsg" x-cloak class="mb-4 p-3 bg-red-50 border border-red-200 rounded-xl flex items-start gap-2">
                            <i data-lucide="alert-circle" class="w-4 h-4 text-red-500 shrink-0 mt-0.5"></i>
                            <p class="text-xs text-red-600" x-text="errorMsg"></p>
                        </div>

                        <button id="btn-pay" @click="pay()" :disabled="!canCheckout"
                                :class="canCheckout ? 'btn-primary' : 'btn-primary opacity-50 cursor-not-allowed'"
                                class="w-full justify-center py-4 rounded-2xl text-sm">
                            <template x-if="!isLoading">
                                <span class="flex items-center justify-center gap-2">
                                    <i data-lucide="credit-card" class="w-4 h-4"></i> Bayar Sekarang
                                </span>
                            </template>
                            <template x-if="isLoading">
                                <span class="flex items-center justify-center gap-2">
                                    <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                                    Memproses...
                                </span>
                            </template>
                        </button>
                        <p class="text-[10px] text-center text-[var(--c-md)]/50 mt-3 leading-relaxed">Dengan melanjutkan, kamu menyetujui syarat dan ketentuan Skena Coffee</p>
                    </div>
                    <div class="mt-4 pt-4 border-t border-[var(--c-lt)]/20 flex items-center gap-2 text-[var(--c-md)]/50">
                        <i data-lucide="shield-check" class="w-4 h-4"></i>
                        <span class="text-xs">Transaksi aman via Midtrans</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@keyframes bounceIn {
    0% { transform: scale(0.3); opacity: 0; }
    50% { transform: scale(1.05); }
    70% { transform: scale(0.95); }
    100% { transform: scale(1); opacity: 1; }
}
</style>

@endsection
