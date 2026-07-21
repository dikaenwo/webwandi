@extends('layouts.app')

@section('title', 'Pembayaran QRIS — Skena Coffee')
@section('meta_description', 'Scan QRIS untuk menyelesaikan pembayaran pesanan Skena Coffee kamu')

@section('content')

<div x-data="{
    timeLeft: 600,
    status: 'waiting',
    orderCode: 'SKN-' + Math.floor(Math.random() * 9000 + 1000),
    get minutes() { return String(Math.floor(this.timeLeft / 60)).padStart(2, '0'); },
    get seconds() { return String(this.timeLeft % 60).padStart(2, '0'); },
    get isExpired() { return this.timeLeft <= 0; },
    startTimer() {
        const interval = setInterval(() => {
            if (this.timeLeft > 0) {
                this.timeLeft--;
            } else {
                clearInterval(interval);
                this.status = 'expired';
            }
        }, 1000);
    },
    simulatePaid() {
        this.status = 'paid';
        this.timeLeft = 999;
        setTimeout(() => { window.location.href = '{{ route('order.status') }}'; }, 2000);
    },
    init() { this.startTimer(); }
}" class="min-h-screen bg-[var(--c-bg)] flex flex-col">

    {{-- HEADER --}}
    <div class="bg-white border-b border-[var(--c-lt)]/30">
        <div class="max-w-lg mx-auto px-4 py-5">
            <div class="flex items-center gap-3">
                <a href="{{ route('checkout') }}" class="w-9 h-9 rounded-xl border border-[var(--c-lt)] flex items-center justify-center hover:bg-[var(--c-bg)] transition-colors duration-200">
                    <i data-lucide="arrow-left" class="w-4 h-4 text-[var(--c-dk)]"></i>
                </a>
                <div>
                    <h1 class="text-lg font-bold text-[var(--c-dk)]">Pembayaran QRIS</h1>
                    <p class="text-xs text-[var(--c-md)]/60" x-text="'Order #' + orderCode"></p>
                </div>
            </div>
        </div>
    </div>

    <div class="flex-1 flex items-start justify-center px-4 py-8">
        <div class="w-full max-w-sm space-y-4">

            {{-- Status Banner --}}
            <div x-show="status === 'paid'"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 class="bg-green-50 border border-green-200 rounded-2xl p-4 flex items-center gap-3">
                <div class="w-10 h-10 bg-green-100 rounded-xl flex items-center justify-center shrink-0">
                    <i data-lucide="check-circle" class="w-5 h-5 text-green-600"></i>
                </div>
                <div>
                    <p class="font-bold text-green-700 text-sm">Pembayaran Berhasil!</p>
                    <p class="text-green-600 text-xs">Mengarahkan ke status pesanan...</p>
                </div>
            </div>

            <div x-show="status === 'expired'"
                 class="bg-red-50 border border-red-200 rounded-2xl p-4 flex items-center gap-3">
                <div class="w-10 h-10 bg-red-100 rounded-xl flex items-center justify-center shrink-0">
                    <i data-lucide="clock" class="w-5 h-5 text-red-600"></i>
                </div>
                <div>
                    <p class="font-bold text-red-700 text-sm">Waktu Habis</p>
                    <p class="text-red-600 text-xs">QR Code telah kedaluwarsa. Silakan pesan ulang.</p>
                </div>
            </div>

            {{-- QR Card --}}
            <div class="bg-white rounded-3xl shadow-lg border border-[var(--c-lt)]/30 overflow-hidden"
                 :class="{ 'opacity-50 pointer-events-none': isExpired }">

                {{-- Card Header --}}
                <div class="bg-gradient-to-r from-[var(--c-dk)] to-[var(--c-md)] px-6 py-4 flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-white/90 flex items-center justify-center p-1 shadow-sm">
                            <img src="{{ asset('images/logo.png') }}" alt="Logo" class="w-full h-full object-contain">
                        </div>
                        <span class="text-white font-bold text-sm">Skena Coffee</span>
                    </div>
                    <span class="text-[var(--c-lt)] text-xs font-medium">QRIS</span>
                </div>

                {{-- QR Code Area --}}
                <div class="px-8 py-6 flex flex-col items-center">
                    <p class="text-xs text-[var(--c-md)]/60 mb-4 font-medium tracking-wider uppercase">Scan untuk membayar</p>

                    {{-- QR Code Visual --}}
                    <div class="relative p-4 border-2 border-[var(--c-dk)]/10 rounded-2xl bg-white shadow-inner">
                        {{-- Simulated QR Code using SVG pattern --}}
                        <svg width="200" height="200" viewBox="0 0 200 200" class="rounded-xl">
                            <!-- Background -->
                            <rect width="200" height="200" fill="white"/>

                            <!-- Top-Left Position Pattern -->
                            <rect x="10" y="10" width="60" height="60" rx="4" fill="var(--c-dk)"/>
                            <rect x="18" y="18" width="44" height="44" rx="2" fill="white"/>
                            <rect x="24" y="24" width="32" height="32" rx="1" fill="var(--c-dk)"/>

                            <!-- Top-Right Position Pattern -->
                            <rect x="130" y="10" width="60" height="60" rx="4" fill="var(--c-dk)"/>
                            <rect x="138" y="18" width="44" height="44" rx="2" fill="white"/>
                            <rect x="144" y="24" width="32" height="32" rx="1" fill="var(--c-dk)"/>

                            <!-- Bottom-Left Position Pattern -->
                            <rect x="10" y="130" width="60" height="60" rx="4" fill="var(--c-dk)"/>
                            <rect x="18" y="138" width="44" height="44" rx="2" fill="white"/>
                            <rect x="24" y="144" width="32" height="32" rx="1" fill="var(--c-dk)"/>

                            <!-- Data modules (simulated) -->
                            <g fill="var(--c-dk)">
                                <rect x="84" y="10" width="8" height="8"/>
                                <rect x="96" y="10" width="8" height="8"/>
                                <rect x="108" y="10" width="8" height="8"/>
                                <rect x="84" y="22" width="8" height="8"/>
                                <rect x="108" y="22" width="8" height="8"/>
                                <rect x="84" y="34" width="8" height="8"/>
                                <rect x="96" y="34" width="8" height="8"/>
                                <rect x="84" y="46" width="8" height="8"/>
                                <rect x="84" y="58" width="8" height="8"/>
                                <rect x="96" y="58" width="8" height="8"/>
                                <rect x="108" y="58" width="8" height="8"/>
                                <rect x="10" y="84" width="8" height="8"/>
                                <rect x="22" y="84" width="8" height="8"/>
                                <rect x="46" y="84" width="8" height="8"/>
                                <rect x="58" y="84" width="8" height="8"/>
                                <rect x="84" y="84" width="8" height="8"/>
                                <rect x="96" y="84" width="8" height="8"/>
                                <rect x="120" y="84" width="8" height="8"/>
                                <rect x="144" y="84" width="8" height="8"/>
                                <rect x="168" y="84" width="8" height="8"/>
                                <rect x="180" y="84" width="8" height="8"/>
                                <rect x="10" y="96" width="8" height="8"/>
                                <rect x="34" y="96" width="8" height="8"/>
                                <rect x="58" y="96" width="8" height="8"/>
                                <rect x="84" y="96" width="8" height="8"/>
                                <rect x="108" y="96" width="8" height="8"/>
                                <rect x="132" y="96" width="8" height="8"/>
                                <rect x="156" y="96" width="8" height="8"/>
                                <rect x="180" y="96" width="8" height="8"/>
                                <rect x="10" y="108" width="8" height="8"/>
                                <rect x="46" y="108" width="8" height="8"/>
                                <rect x="84" y="108" width="8" height="8"/>
                                <rect x="96" y="108" width="8" height="8"/>
                                <rect x="120" y="108" width="8" height="8"/>
                                <rect x="156" y="108" width="8" height="8"/>
                                <rect x="10" y="120" width="8" height="8"/>
                                <rect x="22" y="120" width="8" height="8"/>
                                <rect x="58" y="120" width="8" height="8"/>
                                <rect x="84" y="120" width="8" height="8"/>
                                <rect x="108" y="120" width="8" height="8"/>
                                <rect x="132" y="120" width="8" height="8"/>
                                <rect x="168" y="120" width="8" height="8"/>
                                <rect x="180" y="120" width="8" height="8"/>
                                <rect x="84" y="132" width="8" height="8"/>
                                <rect x="108" y="132" width="8" height="8"/>
                                <rect x="120" y="132" width="8" height="8"/>
                                <rect x="144" y="132" width="8" height="8"/>
                                <rect x="168" y="132" width="8" height="8"/>
                                <rect x="84" y="144" width="8" height="8"/>
                                <rect x="96" y="144" width="8" height="8"/>
                                <rect x="132" y="144" width="8" height="8"/>
                                <rect x="156" y="144" width="8" height="8"/>
                                <rect x="180" y="144" width="8" height="8"/>
                                <rect x="84" y="156" width="8" height="8"/>
                                <rect x="108" y="156" width="8" height="8"/>
                                <rect x="120" y="156" width="8" height="8"/>
                                <rect x="144" y="156" width="8" height="8"/>
                                <rect x="84" y="168" width="8" height="8"/>
                                <rect x="96" y="168" width="8" height="8"/>
                                <rect x="132" y="168" width="8" height="8"/>
                                <rect x="168" y="168" width="8" height="8"/>
                                <rect x="180" y="168" width="8" height="8"/>
                                <rect x="84" y="180" width="8" height="8"/>
                                <rect x="108" y="180" width="8" height="8"/>
                                <rect x="144" y="180" width="8" height="8"/>
                                <rect x="156" y="180" width="8" height="8"/>
                            </g>

                            <!-- Center Logo --}}
                            <rect x="88" y="88" width="24" height="24" rx="4" fill="white"/>
                            <rect x="90" y="90" width="20" height="20" rx="3" fill="var(--c-dk)"/>
                        </svg>

                        {{-- Paid Overlay --}}
                        <div x-show="status === 'paid'"
                             x-transition
                             class="absolute inset-0 flex items-center justify-center bg-white/90 rounded-xl">
                            <div class="text-center">
                                <div class="text-4xl">✅</div>
                                <p class="text-green-700 font-bold text-sm mt-1">Lunas!</p>
                            </div>
                        </div>
                    </div>

                    {{-- Amount --}}
                    <div class="mt-5 text-center">
                        <p class="text-xs text-[var(--c-md)]/60 mb-1">Total Pembayaran</p>
                        <p class="text-2xl font-extrabold text-[var(--c-dk)]" x-text="'Rp ' + ($store.cart.total * 1.1).toLocaleString('id-ID', {maximumFractionDigits: 0})"></p>
                    </div>

                    {{-- Countdown --}}
                    <div class="mt-4 flex items-center gap-2 bg-[var(--c-bg)] px-5 py-2.5 rounded-full">
                        <i data-lucide="timer" class="w-4 h-4 text-[var(--c-md)]"></i>
                        <span class="text-sm font-mono font-bold text-[var(--c-dk)]" x-text="minutes + ':' + seconds"></span>
                        <span class="text-xs text-[var(--c-md)]/60">tersisa</span>
                    </div>
                </div>

                {{-- Instructions --}}
                <div class="px-6 pb-5 space-y-2">
                    @foreach([
                        ['1', 'Buka aplikasi mobile banking / e-wallet kamu'],
                        ['2', 'Pilih fitur Scan QR atau QRIS'],
                        ['3', 'Scan QR Code di atas'],
                        ['4', 'Konfirmasi pembayaran & selesai!'],
                    ] as $step)
                    <div class="flex items-start gap-3 text-xs text-[var(--c-md)]/70">
                        <span class="w-5 h-5 rounded-full bg-[var(--c-lt)]/50 flex items-center justify-center shrink-0 font-bold text-[var(--c-dk)] text-[10px]">{{ $step[0] }}</span>
                        <span>{{ $step[1] }}</span>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="space-y-3">
                {{-- Simulate Paid Button (Demo) --}}
                <button @click="simulatePaid()"
                        id="btn-simulate-paid"
                        x-show="status === 'waiting'"
                        class="btn-primary w-full justify-center py-4 rounded-2xl">
                    <i data-lucide="check-circle" class="w-4 h-4"></i>
                    Simulasi Bayar (Demo)
                </button>

                <a href="{{ route('order.status') }}"
                   class="btn-outline w-full justify-center py-3.5 rounded-2xl">
                    <i data-lucide="receipt" class="w-4 h-4"></i>
                    Cek Status Pesanan
                </a>

                <p class="text-[10px] text-center text-[var(--c-md)]/50">
                    Mengalami kendala? Hubungi kasir atau <a href="#" class="underline">hubungi kami</a>
                </p>
            </div>
        </div>
    </div>
</div>

@endsection
