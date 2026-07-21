@extends('layouts.app')

@section('title', 'Keranjang — Skena Coffee')
@section('meta_description', 'Lihat dan kelola pesananmu di Skena Coffee')

@section('content')

<div x-data="{
    note: '',
    get tableNumber() { return $store.cart.tableNumber; },
    get items() { return $store.cart.items; },
    get total() { return $store.cart.total; },
    get isEmpty() { return $store.cart.count === 0; },
    updateQty(index, delta) {
        const newQty = this.items[index].qty + delta;
        $store.cart.updateQty(index, newQty);
    },
    removeItem(index) {
        $store.cart.remove(index);
    }
}" class="min-h-screen pb-32 md:pb-8">

    {{-- PAGE HEADER --}}
    <div class="bg-white border-b border-[var(--c-lt)]/30">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-5">
            <div class="flex items-center gap-3">
                <a :href="`{{ route('menu') }}${tableNumber ? '?table=' + tableNumber : ''}`" class="w-9 h-9 rounded-xl border border-[var(--c-lt)] flex items-center justify-center hover:bg-[var(--c-bg)] transition-colors duration-200">
                    <i data-lucide="arrow-left" class="w-4 h-4 text-[var(--c-dk)]"></i>
                </a>
                <div>
                    <h1 class="text-lg font-bold text-[var(--c-dk)]">Keranjang Pesanan</h1>
                    <p class="text-xs text-[var(--c-md)]/60" x-text="$store.cart.count + ' item dipilih'"></p>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6">

        {{-- EMPTY STATE --}}
        <div x-show="isEmpty" class="flex flex-col items-center justify-center py-20 text-center">
            <div class="w-20 h-20 bg-[var(--c-lt)]/20 rounded-3xl flex items-center justify-center mb-5 text-4xl">🛒</div>
            <h3 class="text-xl font-bold text-[var(--c-dk)] mb-2">Keranjang Masih Kosong</h3>
            <p class="text-[var(--c-md)]/60 text-sm mb-6 max-w-xs">Yuk, pilih menu favoritmu dan nikmati kopi premium Skena!</p>
            <a :href="`{{ route('menu') }}${tableNumber ? '?table=' + tableNumber : ''}`" class="btn-primary">
                <i data-lucide="coffee" class="w-4 h-4"></i>
                Pilih Menu
            </a>
        </div>

        {{-- CART CONTENT --}}
        <div x-show="!isEmpty">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                {{-- LEFT — Cart Items --}}
                <div class="lg:col-span-2 space-y-3">

                    {{-- Items List --}}
                    <div class="bg-white rounded-2xl border border-[var(--c-lt)]/30 overflow-hidden shadow-sm">
                        <div class="px-5 py-4 border-b border-[var(--c-lt)]/20">
                            <h2 class="font-bold text-[var(--c-dk)] text-sm">Item Pesanan</h2>
                        </div>

                        <template x-for="(item, index) in items" :key="index">
                            <div class="flex items-start gap-4 p-4 border-b border-[var(--c-lt)]/20 last:border-b-0 hover:bg-[var(--c-bg)]/30 transition-colors duration-200">
                                {{-- Thumbnail --}}
                                <div class="w-14 h-14 rounded-2xl overflow-hidden bg-gradient-to-br from-[var(--c-dk)] to-[var(--c-md)] flex items-center justify-center text-2xl shrink-0">
                                    <template x-if="item.image_url">
                                        <img :src="item.image_url" class="w-full h-full object-cover">
                                    </template>
                                    <template x-if="!item.image_url">
                                        <span>☕</span>
                                    </template>
                                </div>

                                {{-- Item Info --}}
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-bold text-[var(--c-dk)] text-sm" x-text="item.name"></h3>
                                    <template x-if="item.variant">
                                        <span class="inline-flex items-center gap-1 text-[10px] font-semibold px-2 py-0.5 rounded-full mt-0.5"
                                              :class="item.variant === 'Hot' ? 'bg-orange-100 text-orange-600' : 'bg-blue-100 text-blue-600'"
                                              x-text="item.variant === 'Hot' ? '☕ Hot' : '🧊 Ice'"></span>
                                    </template>
                                    <p class="text-xs text-[var(--c-md)]/50 mt-0.5 italic" x-show="item.note" x-text="'Catatan: ' + item.note"></p>
                                    <div class="flex items-center justify-between mt-2.5">
                                        {{-- Qty Control --}}
                                        <div class="flex items-center gap-2 bg-[var(--c-bg)] rounded-xl p-1">
                                            <button @click="updateQty(index, -1)"
                                                    class="w-6 h-6 rounded-lg bg-white flex items-center justify-center hover:bg-[var(--c-dk)] hover:text-white transition-all duration-150 active:scale-90 shadow-sm">
                                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M20 12H4"/></svg>
                                            </button>
                                            <span class="text-sm font-bold text-[var(--c-dk)] w-5 text-center" x-text="item.qty"></span>
                                            <button @click="updateQty(index, 1)"
                                                    class="w-6 h-6 rounded-lg bg-white flex items-center justify-center hover:bg-[var(--c-dk)] hover:text-white transition-all duration-150 active:scale-90 shadow-sm">
                                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14"/></svg>
                                            </button>
                                        </div>
                                        {{-- Item Total --}}
                                        <span class="font-extrabold text-[var(--c-dk)] text-sm" x-text="'Rp ' + (item.price * item.qty).toLocaleString('id-ID')"></span>
                                    </div>
                                </div>

                                {{-- Delete Button --}}
                                <button @click="removeItem(index)"
                                        class="w-8 h-8 rounded-xl flex items-center justify-center text-red-400 hover:bg-red-50 hover:text-red-600 transition-all duration-200 active:scale-90 shrink-0 mt-0.5">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </div>
                        </template>
                    </div>

                    {{-- Table Number --}}
                    <div class="bg-white rounded-2xl border border-[var(--c-lt)]/30 p-5 shadow-sm">
                        <label for="table-number" class="block text-sm font-semibold text-[var(--c-dk)] mb-2">
                            <span class="flex items-center gap-2">
                                <i data-lucide="armchair" class="w-4 h-4 text-[var(--c-md)]"></i>
                                Nomor Meja (Otomatis)
                            </span>
                        </label>
                        <input type="text"
                               id="table-number"
                               :value="tableNumber"
                               readonly
                               class="input-field bg-[var(--c-bg)]/50 cursor-not-allowed border-[var(--c-lt)]/30 font-bold text-[var(--c-dk)]">
                        <p class="text-[var(--c-md)]/50 text-xs mt-1.5">Meja terisi otomatis dari hasil scan QR Anda.</p>
                    </div>

                    {{-- Order Note --}}
                    <div class="bg-white rounded-2xl border border-[var(--c-lt)]/30 p-5 shadow-sm">
                        <label for="order-note" class="block text-sm font-semibold text-[var(--c-dk)] mb-2">
                            <span class="flex items-center gap-2">
                                <i data-lucide="pencil-line" class="w-4 h-4 text-[var(--c-md)]"></i>
                                Catatan Pesanan (Opsional)
                            </span>
                        </label>
                        <textarea id="order-note"
                                  x-model="note"
                                  rows="3"
                                  placeholder="Ada permintaan khusus? Tulis di sini..."
                                  class="input-field resize-none"></textarea>
                    </div>
                </div>

                {{-- RIGHT — Summary --}}
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-2xl border border-[var(--c-lt)]/30 p-5 shadow-sm sticky top-28">
                        <h2 class="font-bold text-[var(--c-dk)] mb-5 pb-4 border-b border-[var(--c-lt)]/20">Ringkasan</h2>

                        {{-- Price Breakdown --}}
                        <div class="space-y-3 text-sm">
                            <div class="flex items-center justify-between text-[var(--c-md)]/70">
                                <span>Subtotal</span>
                                <span x-text="'Rp ' + total.toLocaleString('id-ID')"></span>
                            </div>
                            <div class="flex items-center justify-between text-[var(--c-md)]/70">
                                <span>Pajak (10%)</span>
                                <span x-text="'Rp ' + Math.round(total * 0.1).toLocaleString('id-ID')"></span>
                            </div>
                            <div class="flex items-center justify-between text-[var(--c-md)]/70">
                                <span>Biaya Layanan</span>
                                <span class="text-green-600 font-medium">Gratis</span>
                            </div>
                            <div class="border-t border-[var(--c-lt)]/30 pt-3 flex items-center justify-between font-extrabold text-[var(--c-dk)] text-base">
                                <span>Total</span>
                                <span x-text="'Rp ' + Math.round(total * 1.1).toLocaleString('id-ID')"></span>
                            </div>
                        </div>

                        {{-- Promo Code --}}
                        <div class="mt-5 pt-4 border-t border-[var(--c-lt)]/20">
                            <div class="flex gap-2">
                                <input type="text" placeholder="Kode promo" id="promo-code" class="input-field flex-1 text-xs py-2.5">
                                <button class="bg-[var(--c-bg)] text-[var(--c-dk)] px-3 rounded-xl text-xs font-bold hover:bg-[var(--c-lt)]/40 transition-colors duration-200 shrink-0">
                                    Pakai
                                </button>
                            </div>
                        </div>

                        {{-- Checkout Button --}}
                        <a href="{{ route('checkout') }}"
                           id="btn-checkout"
                           class="btn-primary w-full justify-center mt-5 py-4 rounded-2xl text-sm">
                            <i data-lucide="credit-card" class="w-4 h-4"></i>
                            Lanjut ke Checkout
                        </a>

                        {{-- Continue Shopping --}}
                        <a :href="`{{ route('menu') }}${tableNumber ? '?table=' + tableNumber : ''}`" class="w-full text-center block mt-3 text-sm text-[var(--c-md)] hover:text-[var(--c-dk)] transition-colors duration-200">
                            + Tambah Menu Lain
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
