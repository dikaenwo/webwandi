@extends('layouts.app')

@section('title', ($menu['name'] ?? 'Detail Menu') . ' — Skena Coffee')
@section('meta_description', $menu['description'] ?? 'Detail produk Skena Coffee')

@section('content')

@php
$sizes = [
    ['label' => 'Small', 'oz' => '8oz', 'price_add' => 0],
    ['label' => 'Regular', 'oz' => '12oz', 'price_add' => 0],
    ['label' => 'Large', 'oz' => '16oz', 'price_add' => 5000],
];
@endphp

<div x-data="{
    selectedVariant: null,
    qty: 1,
    note: '',
    menu: {{ Illuminate\Support\Js::from($menu) }},
    init() {
        if (this.menu.has_hot && this.menu.has_ice) { this.selectedVariant = 'hot'; }
        else if (this.menu.has_hot) { this.selectedVariant = 'hot'; }
        else if (this.menu.has_ice) { this.selectedVariant = 'ice'; }
    },
    get basePrice() {
        if (this.selectedVariant === 'hot' && this.menu.has_hot) return this.menu.price_hot || 0;
        if (this.selectedVariant === 'ice' && this.menu.has_ice) return this.menu.price_ice || 0;
        return this.menu.price || 0;
    },
    get totalPrice() {
        return this.basePrice * this.qty;
    },
    addToCart() {
        let variantId = this.menu.id;
        let variantName = null;
        if (this.selectedVariant === 'hot') {
            variantId = this.menu.id + '-hot';
            variantName = 'Hot';
        } else if (this.selectedVariant === 'ice') {
            variantId = this.menu.id + '-ice';
            variantName = 'Ice';
        }

        $store.cart.add({
            id: variantId,
            name: this.menu.name,
            price: this.basePrice,
            image_url: this.menu.image_url,
            variant: variantName,
            note: this.note,
            qty: this.qty,
        });
    }
}" class="min-h-screen pb-32 md:pb-8">

    {{-- BREADCRUMB --}}
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
        <nav class="flex items-center gap-2 text-xs text-[var(--c-md)]/60">
            <a :href="`{{ route('home') }}${$store.cart.tableNumber ? '?table=' + $store.cart.tableNumber : ''}`" class="hover:text-[var(--c-dk)] transition-colors">Beranda</a>
            <i data-lucide="chevron-right" class="w-3 h-3"></i>
            <a :href="`{{ route('menu') }}${$store.cart.tableNumber ? '?table=' + $store.cart.tableNumber : ''}`" class="hover:text-[var(--c-dk)] transition-colors">Menu</a>
            <i data-lucide="chevron-right" class="w-3 h-3"></i>
            <span class="text-[var(--c-dk)] font-medium">{{ $menu['name'] }}</span>
        </nav>
    </div>

    {{-- MAIN CONTENT --}}
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12">

            {{-- LEFT — Product Image --}}
            <div class="relative">
                <div class="sticky top-24">
                    {{-- Main Image --}}
                    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-[var(--c-dk)] to-[var(--c-md)] aspect-square max-w-md mx-auto shadow-xl">
                        <div class="absolute inset-0 flex items-center justify-center bg-[var(--c-bg)]">
                            @if($menu['image_url'])
                                <img src="{{ $menu['image_url'] }}" class="w-full h-full object-cover">
                            @else
                                <i data-lucide="coffee" class="w-24 h-24 text-[var(--c-md)] animate-float"></i>
                            @endif
                        </div>

                        {{-- Badge Tag --}}
                        @if($menu['tag'])
                        <div class="absolute top-5 left-5">
                            <span class="bg-[var(--c-lt)] text-[var(--c-dk)] text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wide">{{ $menu['tag'] }}</span>
                        </div>
                        @endif

                        <div class="absolute bottom-5 right-5">
                            <span class="bg-white/10 backdrop-blur-sm text-[var(--c-lt)] text-xs px-3 py-1.5 rounded-full border border-white/20">{{ $menu->category->name }}</span>
                        </div>
                    </div>


                </div>
            </div>

            {{-- RIGHT — Product Details --}}
            <div class="flex flex-col">

                {{-- Scan QR Banner --}}
                <div x-show="!$store.cart.tableNumber" class="bg-amber-50 border border-amber-200 text-amber-800 px-4 py-3 rounded-2xl mb-6 flex items-start gap-3 shadow-sm" x-cloak>
                    <i data-lucide="scan-line" class="w-5 h-5 shrink-0 mt-0.5"></i>
                    <div>
                        <p class="font-bold text-sm">Mode Preview Menu</p>
                        <p class="text-xs opacity-90">Silakan scan QR Code di meja Anda untuk mulai memesan.</p>
                    </div>
                </div>

                {{-- Name --}}
                <div class="mb-4">
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-[var(--c-dk)] leading-tight">{{ $menu['name'] }}</h1>
                </div>

                <p class="text-[var(--c-md)]/80 text-sm leading-relaxed mb-6 border-b border-[var(--c-lt)]/30 pb-6">
                    {{ $menu['description'] }}
                </p>

                {{-- Hot / Ice Variant Selector --}}
                @if($menu['has_hot'] || $menu['has_ice'])
                <div class="mb-6">
                    <p class="text-xs text-[var(--c-md)]/60 mb-3 uppercase tracking-wider font-semibold">Pilih Varian</p>
                    <div class="flex gap-3">
                        @if($menu['has_hot'])
                        <button @click="selectedVariant = 'hot'"
                                :class="selectedVariant === 'hot'
                                    ? 'border-2 border-orange-500 bg-orange-500 text-white shadow-lg shadow-orange-200'
                                    : 'border-2 border-[var(--c-lt)] bg-white text-[var(--c-md)] hover:border-orange-300'"
                                class="flex-1 py-3 px-4 rounded-xl transition-all duration-200 text-center">
                            <div class="flex items-center justify-center gap-2 font-bold text-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z"></path></svg>
                                Hot
                            </div>
                            <div class="text-xs mt-0.5 opacity-80" x-text="'Rp ' + (menu.price_hot||0).toLocaleString('id-ID')"></div>
                        </button>
                        @endif

                        @if($menu['has_ice'])
                        <button @click="selectedVariant = 'ice'"
                                :class="selectedVariant === 'ice'
                                    ? 'border-2 border-blue-500 bg-blue-500 text-white shadow-lg shadow-blue-200'
                                    : 'border-2 border-[var(--c-lt)] bg-white text-[var(--c-md)] hover:border-blue-300'"
                                class="flex-1 py-3 px-4 rounded-xl transition-all duration-200 text-center">
                            <div class="flex items-center justify-center gap-2 font-bold text-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="2" y1="12" x2="22" y2="12"></line><line x1="12" y1="2" x2="12" y2="22"></line><path d="m20 16-4-4 4-4M4 8l4 4-4 4M16 4l-4 4-4-4M8 20l4-4 4 4"></path></svg>
                                Ice
                            </div>
                            <div class="text-xs mt-0.5 opacity-80" x-text="'Rp ' + (menu.price_ice||0).toLocaleString('id-ID')"></div>
                        </button>
                        @endif
                    </div>

                    {{-- Variant Description --}}
                    <div class="mt-3 p-3 rounded-xl bg-[var(--c-bg)]/70 text-sm text-[var(--c-md)]/80 leading-relaxed" 
                         x-show="selectedVariant === 'hot' && menu.desc_hot"
                         x-text="menu.desc_hot">
                    </div>
                    <div class="mt-3 p-3 rounded-xl bg-[var(--c-bg)]/70 text-sm text-[var(--c-md)]/80 leading-relaxed"
                         x-show="selectedVariant === 'ice' && menu.desc_ice"
                         x-text="menu.desc_ice">
                    </div>
                </div>
                @endif

                {{-- Price --}}
                <div class="mb-6">
                    <p class="text-xs text-[var(--c-md)]/60 mb-1 uppercase tracking-wider">Harga</p>
                    <div class="flex items-end gap-2">
                        <span class="text-3xl font-extrabold text-[var(--c-dk)]" x-text="'Rp ' + totalPrice.toLocaleString('id-ID')"></span>
                    </div>
                </div>



                {{-- Quantity --}}
                <div class="mb-6">
                    <p class="text-xs text-[var(--c-md)]/60 mb-3 uppercase tracking-wider font-semibold">Jumlah</p>
                    <div class="flex items-center gap-4">
                        <button @click="qty = Math.max(1, qty - 1)"
                                id="qty-decrease"
                                class="w-10 h-10 rounded-xl border-2 border-[var(--c-lt)] flex items-center justify-center hover:border-[var(--c-dk)] hover:bg-[var(--c-dk)] hover:text-white transition-all duration-200 active:scale-90">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M20 12H4"/></svg>
                        </button>
                        <span class="text-xl font-extrabold text-[var(--c-dk)] w-8 text-center" x-text="qty"></span>
                        <button @click="qty++"
                                id="qty-increase"
                                class="w-10 h-10 rounded-xl border-2 border-[var(--c-lt)] flex items-center justify-center hover:border-[var(--c-dk)] hover:bg-[var(--c-dk)] hover:text-white transition-all duration-200 active:scale-90">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14"/></svg>
                        </button>
                    </div>
                </div>

                {{-- Note --}}
                <div class="mb-6">
                    <p class="text-xs text-[var(--c-md)]/60 mb-2 uppercase tracking-wider font-semibold">Catatan (opsional)</p>
                    <textarea x-model="note"
                              id="product-note"
                              rows="2"
                              placeholder="Contoh: less sugar, extra shot, no foam..."
                              class="input-field resize-none"></textarea>
                </div>

                {{-- Add to Cart Button --}}
                <button x-show="$store.cart.tableNumber"
                        x-cloak
                        @click="addToCart()"
                        id="btn-add-to-cart"
                        class="btn-primary w-full justify-center py-4 text-base rounded-2xl active:scale-95">
                    <i data-lucide="shopping-bag" class="w-5 h-5"></i>
                    Tambah ke Keranjang
                    <span class="ml-auto font-bold" x-text="'Rp ' + totalPrice.toLocaleString('id-ID')"></span>
                </button>

                <a x-show="$store.cart.tableNumber"
                   x-cloak
                   :href="`{{ route('cart') }}${$store.cart.tableNumber ? '?table=' + $store.cart.tableNumber : ''}`" class="btn-secondary w-full justify-center mt-3 py-3.5 rounded-2xl">
                    <i data-lucide="shopping-cart" class="w-4 h-4"></i>
                    Lihat Keranjang
                </a>
            </div>
        </div>

        {{-- RELATED PRODUCTS --}}
        <div class="mt-14">
            <div class="flex items-end justify-between mb-6">
                <div>
                    <h2 class="text-xl font-bold text-[var(--c-dk)]">Menu Lainnya</h2>
                    <p class="text-sm text-[var(--c-md)]/60">Yang mungkin kamu suka</p>
                </div>
                <a :href="`{{ route('menu') }}${$store.cart.tableNumber ? '?table=' + $store.cart.tableNumber : ''}`" class="btn-outline text-xs">Lihat Semua <i data-lucide="arrow-right" class="w-3 h-3"></i></a>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                @foreach($relatedMenus as $related)
                @php $tableQuery = request()->get('table') ? '?table=' . request()->get('table') : ''; @endphp
                <a href="{{ route('menu.detail', $related['id']) }}{{ $tableQuery }}" class="card card-hover group flex items-center gap-4 p-4">
                    <div class="w-16 h-16 rounded-2xl bg-[var(--c-bg)] flex items-center justify-center overflow-hidden shrink-0 group-hover:scale-105 transition-transform duration-300 border border-[var(--c-lt)]/30">
                        @if($related['image_url'])
                            <img src="{{ $related['image_url'] }}" class="w-full h-full object-cover">
                        @else
                            <i data-lucide="coffee" class="w-6 h-6 text-[var(--c-md)]"></i>
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <h4 class="font-bold text-[var(--c-dk)] text-sm truncate">{{ $related['name'] }}</h4>
                        <div class="flex items-center gap-1 mt-0.5">
                            <svg class="w-3 h-3 fill-amber-400" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                            <span class="text-xs text-[var(--c-md)]/70">{{ $related['rating'] }}</span>
                        </div>
                        <p class="font-extrabold text-[var(--c-dk)] text-sm mt-1">Rp {{ number_format($related['price'], 0, ',', '.') }}</p>
                    </div>
                    <i data-lucide="chevron-right" class="w-4 h-4 text-[var(--c-lt)] shrink-0"></i>
                </a>
                @endforeach
            </div>
        </div>

        <div class="md:hidden h-16"></div>
    </div>
</div>

@endsection
