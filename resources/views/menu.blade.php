@extends('layouts.app')

@section('title', 'Menu — Skena Coffee')
@section('meta_description', 'Jelajahi menu kopi dan non-kopi premium Skena Coffee. Kopi panas, kopi dingin, matcha, makanan, dan camilan pilihan.')

@section('content')

@php
@endphp

<div x-data="{
        activeCategory: 'Semua',
        search: '',
        selectedItem: null,
        get filtered() {
            return this.items.filter(item => {
                const matchCat = this.activeCategory === 'Semua' || (item.category && item.category.name === this.activeCategory);
                const matchSearch = item.name.toLowerCase().includes(this.search.toLowerCase()) || (item.description && item.description.toLowerCase().includes(this.search.toLowerCase()));
                return matchCat && matchSearch;
            });
        },
        items: {{ Illuminate\Support\Js::from($menus) }}
     }" class="min-h-screen pb-24 md:pb-8">

    {{-- PAGE HEADER --}}
    <div class="bg-white border-b border-[var(--c-lt)]/30 sticky {{ request()->get('table') ? 'top-0 pt-4' : 'top-16' }} z-30 transition-all duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex flex-col sm:flex-row gap-3 items-center">

                {{-- Search --}}
                <div class="relative w-full sm:w-64 md:w-80 shrink-0">
                    <i data-lucide="search" class="w-4 h-4 text-[var(--c-lt)] absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                    <input type="text"
                           id="menu-search"
                           x-model="search"
                           placeholder="Cari menu favoritmu..."
                           class="input-field pl-10 w-full bg-[var(--c-bg)]/50">
                </div>

                {{-- Filter kategori desktop --}}
                <div class="hidden sm:flex flex-1 min-w-0 items-center gap-2 overflow-x-auto scrollbar-hide py-1">
                    <button @click="activeCategory = 'Semua'"
                            id="filter-semua"
                            class="shrink-0"
                            :class="activeCategory === 'Semua' ? 'category-chip category-chip-active' : 'category-chip category-chip-inactive'">
                        Semua
                    </button>
                    @foreach($categories as $cat)
                    <button @click="activeCategory = '{{ $cat->name }}'"
                            id="filter-{{ \Illuminate\Support\Str::slug($cat->name) }}"
                            class="shrink-0"
                            :class="activeCategory === '{{ $cat->name }}' ? 'category-chip category-chip-active' : 'category-chip category-chip-inactive'">
                        {{ $cat->name }}
                    </button>
                    @endforeach
                </div>
            </div>

            {{-- Mobile category scroll --}}
            <div class="flex sm:hidden gap-2 overflow-x-auto scrollbar-hide mt-3 pb-1 -mx-4 px-4">
                <button @click="activeCategory = 'Semua'"
                        :class="activeCategory === 'Semua' ? 'category-chip category-chip-active' : 'category-chip category-chip-inactive'"
                        class="shrink-0">
                    Semua
                </button>
                @foreach($categories as $cat)
                <button @click="activeCategory = '{{ $cat->name }}'"
                        :class="activeCategory === '{{ $cat->name }}' ? 'category-chip category-chip-active' : 'category-chip category-chip-inactive'"
                        class="shrink-0">
                    {{ $cat->name }}
                </button>
                @endforeach
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">

        {{-- Scan QR Banner --}}
        <div x-show="!$store.cart.tableNumber" class="bg-[var(--c-bg)] border border-[var(--c-lt)]/50 text-[var(--c-dk)] px-4 py-3 rounded-2xl mb-6 flex items-start gap-3 shadow-sm" x-cloak>
            <i data-lucide="scan-line" class="w-5 h-5 shrink-0 mt-0.5 text-[var(--c-md)]"></i>
            <div>
                <p class="font-bold text-sm">Mode Preview Menu</p>
                <p class="text-xs text-[var(--c-md)]/70">Silakan scan QR Code di meja Anda untuk mulai memesan.</p>
            </div>
        </div>

        {{-- Results count & Order Info --}}
        <div class="flex flex-col sm:flex-row sm:items-end justify-between mb-6 gap-4">
            <div class="flex items-center gap-4 justify-between sm:justify-start">
                <div>
                    <h1 class="text-xl font-bold text-[var(--c-dk)]" x-text="activeCategory === 'Semua' ? 'Semua Menu' : activeCategory"></h1>
                    <p class="text-sm text-[var(--c-md)]/60 mt-0.5" x-text="filtered.length + ' menu ditemukan'"></p>
                </div>
                
                {{-- Table Badge --}}
                <div x-show="$store.cart.tableNumber" x-cloak class="flex items-center gap-2 bg-[var(--c-bg)] border border-[var(--c-lt)]/50 px-3 py-1.5 rounded-lg shrink-0">
                    <i data-lucide="table-2" class="w-4 h-4 text-[var(--c-md)]"></i>
                    <div class="flex items-baseline gap-1">
                        <span class="text-[10px] text-[var(--c-md)] uppercase tracking-widest font-semibold">Meja</span>
                        <span class="text-sm font-extrabold text-[var(--c-dk)]" x-text="$store.cart.tableNumber"></span>
                    </div>
                </div>
            </div>

            {{-- Cart Button --}}
            <a :href="`{{ route('cart') }}${$store.cart.tableNumber ? '?table=' + $store.cart.tableNumber : ''}`" x-show="$store.cart.tableNumber" x-cloak class="relative flex items-center justify-center sm:justify-start gap-2 bg-[var(--c-dk)] text-[var(--c-bg)] px-5 py-2.5 rounded-xl text-sm font-bold hover:bg-[var(--c-md)] transition-all duration-300 shadow-sm active:scale-95 w-full sm:w-auto">
                <i data-lucide="shopping-bag" class="w-4 h-4"></i>
                <span>Keranjang</span>
                <span x-show="$store.cart.count > 0" x-text="$store.cart.count" class="absolute -top-2 -right-2 bg-[var(--c-lt)] text-[var(--c-dk)] text-xs font-bold w-5 h-5 rounded-full flex items-center justify-center"></span>
            </a>
        </div>

        {{-- Empty state --}}
        <div x-show="filtered.length === 0" class="flex flex-col items-center justify-center py-20 text-center">
            <div class="w-16 h-16 rounded-2xl bg-[var(--c-bg)] flex items-center justify-center mb-4">
                <i data-lucide="search-x" class="w-8 h-8 text-[var(--c-lt)]"></i>
            </div>
            <h3 class="font-bold text-[var(--c-dk)] mb-1">Menu tidak ditemukan</h3>
            <p class="text-[var(--c-md)]/60 text-sm">Coba kata kunci lain atau pilih kategori yang berbeda</p>
            <button @click="search=''; activeCategory='Semua'" class="btn-outline mt-4 text-xs">Reset Filter</button>
        </div>

        {{-- Menu Grid --}}
        <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4" x-show="filtered.length > 0">
            <template x-for="item in filtered" :key="item.id">
                <div class="card card-hover group relative"
                     :class="!item.is_available ? 'opacity-55 grayscale' : ''"
                     :id="'menu-card-' + item.id">

                    {{-- Image --}}
                    <a :href="`{{ url('menu') }}/${item.id}${$store.cart.tableNumber ? '?table=' + $store.cart.tableNumber : ''}`" class="block">
                        <div class="relative overflow-hidden h-40 bg-[var(--c-bg)]">
                            <template x-if="item.image_url">
                                <img :src="item.image_url" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                            </template>
                            <template x-if="!item.image_url">
                                <div class="absolute inset-0 flex items-center justify-center bg-[var(--c-dk)]">
                                    <i data-lucide="coffee" class="w-12 h-12 text-[var(--c-lt)] group-hover:scale-110 transition-transform duration-500"></i>
                                </div>
                            </template>
                            {{-- Unavailable overlay badge --}}
                            <template x-if="!item.is_available">
                                <div class="absolute inset-0 flex items-center justify-center bg-black/30 z-10">
                                    <span class="bg-white text-gray-700 text-[10px] font-bold px-3 py-1.5 rounded-full uppercase tracking-widest shadow">
                                        Tidak Tersedia
                                    </span>
                                </div>
                            </template>
                            <div x-show="item.tag && item.is_available" class="absolute top-2.5 left-2.5">
                                <span class="bg-[var(--c-lt)] text-[var(--c-dk)] text-[9px] font-bold px-2 py-0.5 rounded-full uppercase tracking-wide" x-text="item.tag"></span>
                            </div>
                        </div>
                    </a>

                    {{-- Content --}}
                    <div class="p-3.5">
                        <a :href="`{{ url('menu') }}/${item.id}${$store.cart.tableNumber ? '?table=' + $store.cart.tableNumber : ''}`">
                            <h3 class="font-bold text-[var(--c-dk)] text-sm leading-snug line-clamp-1 mb-1" x-text="item.name"></h3>
                            <p class="text-[var(--c-md)]/60 text-xs leading-relaxed line-clamp-2 mb-2.5" x-text="item.description"></p>
                        </a>



                        {{-- Price & Add --}}
                        <div class="flex items-center justify-between">
                            <div>
                                <template x-if="item.has_hot || item.has_ice">
                                    <div class="flex gap-3 text-[10px]">
                                        <template x-if="item.has_hot">
                                            <div class="flex flex-col">
                                                <span class="font-bold text-[var(--c-md)]">Hot</span>
                                                <span class="font-extrabold text-[var(--c-dk)] text-xs" x-text="(item.price_hot/1000) + 'K'"></span>
                                            </div>
                                        </template>
                                        <template x-if="item.has_ice">
                                            <div class="flex flex-col">
                                                <span class="font-bold text-[var(--c-md)]">Ice</span>
                                                <span class="font-extrabold text-[var(--c-dk)] text-xs" x-text="(item.price_ice/1000) + 'K'"></span>
                                            </div>
                                        </template>
                                    </div>
                                </template>
                                <template x-if="!item.has_hot && !item.has_ice">
                                    <span class="font-extrabold text-[var(--c-dk)] text-sm" x-text="'Rp ' + (item.price || 0).toLocaleString('id-ID')"></span>
                                </template>
                            </div>
                            <button x-show="$store.cart.tableNumber && item.is_available"
                                    x-cloak
                                    @click.prevent="
                                        if (item.has_hot && item.has_ice) {
                                            selectedItem = item;
                                        } else if (item.has_hot) {
                                            $store.cart.add({id: item.id + '-hot', name: item.name, price: item.price_hot, image_url: item.image_url, variant: 'Hot', qty: 1});
                                        } else if (item.has_ice) {
                                            $store.cart.add({id: item.id + '-ice', name: item.name, price: item.price_ice, image_url: item.image_url, variant: 'Ice', qty: 1});
                                        } else {
                                            $store.cart.add({id: item.id, name: item.name, price: item.price, image_url: item.image_url, variant: null, qty: 1});
                                        }
                                    "
                                    class="w-7 h-7 bg-[var(--c-dk)] rounded-lg flex items-center justify-center hover:bg-[var(--c-md)] transition-all duration-200 active:scale-90 shrink-0 relative z-10">
                                <svg class="w-3.5 h-3.5 text-[var(--c-bg)]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14"/></svg>
                            </button>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>

    {{-- MODAL PILIH VARIAN HOT / ICE --}}
    <div x-show="selectedItem" class="fixed inset-0 z-[99999] flex items-end sm:items-center justify-center" x-cloak>
        {{-- Backdrop --}}
        <div class="absolute inset-0 bg-[var(--c-dk)]/50 backdrop-blur-sm"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="selectedItem = null"></div>

        {{-- Modal Content --}}
        <div class="relative w-full sm:max-w-md bg-[var(--c-bg)] rounded-t-3xl sm:rounded-3xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh]"
             x-transition:enter="transition ease-out duration-300 transform"
             x-transition:enter-start="translate-y-full sm:translate-y-10 opacity-0"
             x-transition:enter-end="translate-y-0 opacity-100"
             x-transition:leave="transition ease-in duration-200 transform"
             x-transition:leave-start="translate-y-0 opacity-100"
             x-transition:leave-end="translate-y-full sm:translate-y-10 opacity-0"
             @click.stop>

            {{-- Drag handle (mobile) --}}
            <div class="flex justify-center pt-3 pb-1 sm:hidden">
                <div class="w-10 h-1 rounded-full bg-[var(--c-lt)]"></div>
            </div>

            {{-- Header --}}
            <div class="px-5 py-4 border-b border-[var(--c-lt)]/40 flex items-start gap-3">
                <template x-if="selectedItem?.image_url">
                    <img :src="selectedItem.image_url" class="w-14 h-14 rounded-xl object-cover shrink-0">
                </template>
                <div class="flex-1 min-w-0">
                    <h3 class="font-extrabold text-[var(--c-dk)] text-base leading-tight" x-text="selectedItem?.name"></h3>
                    <p class="text-xs text-[var(--c-md)]/60 mt-0.5">Pilih varian minuman</p>
                </div>
                <button @click="selectedItem = null" class="w-8 h-8 flex items-center justify-center rounded-full bg-[var(--c-lt)]/30 text-[var(--c-dk)] hover:bg-[var(--c-lt)] transition-colors shrink-0">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            {{-- Body --}}
            <div class="p-5 pb-8 space-y-3 overflow-y-auto">

                {{-- HOT option --}}
                <template x-if="selectedItem?.has_hot">
                    <div class="group bg-white rounded-2xl p-4 border-2 border-[var(--c-lt)]/40 hover:border-[var(--c-md)] hover:bg-[var(--c-bg)]/60 transition-all duration-200 cursor-pointer"
                         @click="
                            $store.cart.add({
                                id: selectedItem.id + '-hot',
                                name: selectedItem.name,
                                price: selectedItem.price_hot,
                                image_url: selectedItem.image_url,
                                variant: 'Hot',
                                qty: 1
                            });
                            selectedItem = null;
                         ">
                        <div class="flex items-center justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-[var(--c-bg)] flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform">
                                    <i data-lucide="flame" class="w-5 h-5 text-[var(--c-md)]"></i>
                                </div>
                                <div>
                                    <h4 class="font-bold text-[var(--c-dk)] text-sm">Hot</h4>
                                    <p class="text-[11px] text-[var(--c-md)]/70 mt-0.5 leading-snug" x-text="selectedItem?.desc_hot || 'Disajikan hangat'"></p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                <span class="font-extrabold text-[var(--c-dk)] text-sm" x-text="'Rp ' + (selectedItem?.price_hot || 0).toLocaleString('id-ID')"></span>
                                <div class="w-7 h-7 rounded-lg bg-[var(--c-md)] group-hover:bg-[var(--c-dk)] flex items-center justify-center transition-colors">
                                    <svg class="w-3.5 h-3.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14"/></svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>

                {{-- ICE option --}}
                <template x-if="selectedItem?.has_ice">
                    <div class="group bg-white rounded-2xl p-4 border-2 border-[var(--c-lt)]/40 hover:border-[var(--c-md-lt)] hover:bg-[var(--c-bg)]/60 transition-all duration-200 cursor-pointer"
                         @click="
                            $store.cart.add({
                                id: selectedItem.id + '-ice',
                                name: selectedItem.name,
                                price: selectedItem.price_ice,
                                image_url: selectedItem.image_url,
                                variant: 'Ice',
                                qty: 1
                            });
                            selectedItem = null;
                         ">
                        <div class="flex items-center justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-[var(--c-bg)] flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform">
                                    <i data-lucide="snowflake" class="w-5 h-5 text-[var(--c-md-lt)]"></i>
                                </div>
                                <div>
                                    <h4 class="font-bold text-[var(--c-dk)] text-sm">Ice</h4>
                                    <p class="text-[11px] text-[var(--c-md)]/70 mt-0.5 leading-snug" x-text="selectedItem?.desc_ice || 'Disajikan dingin dengan es'"></p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                <span class="font-extrabold text-[var(--c-dk)] text-sm" x-text="'Rp ' + (selectedItem?.price_ice || 0).toLocaleString('id-ID')"></span>
                                <div class="w-7 h-7 rounded-lg bg-[var(--c-md-lt)] group-hover:bg-[var(--c-dk)] flex items-center justify-center transition-colors">
                                    <svg class="w-3.5 h-3.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14"/></svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>

            </div>
        </div>
    </div>
</div>

@endsection
