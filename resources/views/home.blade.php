@extends('layouts.app')

@section('title', 'Skena Coffee — Taste the Moment')
@section('meta_description', 'Skena Coffee – Kopi premium dengan self-order modern. Pilih menu favoritmu dan nikmati pengalaman ngopi yang santai.')

@section('content')

{{-- ===================== HERO SECTION ===================== --}}
<section class="relative min-h-screen flex items-center justify-center overflow-hidden" id="hero">

    {{-- Background --}}
    <div class="absolute inset-0 bg-hero z-0"></div>

    {{-- Subtle grain overlay --}}
    <div class="absolute inset-0 z-0 opacity-[0.03]"
         style="background-image: url(&quot;data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noise'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noise)'/%3E%3C/svg%3E&quot;); background-size: 200px;"></div>

    {{-- Ambient glows --}}
    <div class="absolute top-1/4 -left-20 w-80 h-80 rounded-full bg-[var(--c-md)]/25 blur-[100px] pointer-events-none z-0"></div>
    <div class="absolute bottom-1/4 -right-20 w-96 h-96 rounded-full bg-[var(--c-lt)]/15 blur-[120px] pointer-events-none z-0"></div>

    {{-- Content --}}
    <div class="relative z-10 text-center px-4 sm:px-6 max-w-4xl mx-auto pb-24 md:pb-0"
         x-data="{ shown: false }" x-intersect.once="shown = true" :class="shown ? 'reveal active' : 'reveal'">

        {{-- Pill Badge --}}
        <div class="inline-flex items-center gap-2 bg-white/10 border border-white/20 text-white/80 px-5 py-2 rounded-full text-xs font-semibold mb-8 backdrop-blur-sm tracking-widest uppercase">
            <span class="w-1.5 h-1.5 bg-emerald-400 rounded-full animate-pulse"></span>
            Cafe · Makassar
        </div>

        {{-- Headline --}}
        <h1 class="font-heading font-extrabold text-white leading-[0.95] tracking-normal uppercase mb-5" style="font-size: clamp(4rem, 13vw, 14rem); transform: scaleX(0.8); white-space: nowrap;">
            Skena Coffee
        </h1>



        {{-- CTA Buttons --}}
        <div class="flex flex-col sm:flex-row gap-3 justify-center items-center">
            <a href="{{ route('menu') }}"
               class="group w-full sm:w-auto bg-[var(--c-bg)] text-[var(--c-dk)] px-8 py-4 rounded-2xl font-bold text-sm tracking-wide hover:bg-white active:scale-95 transition-all duration-300 shadow-lg hover:shadow-2xl flex items-center justify-center gap-2.5">
                <i data-lucide="coffee" class="w-4 h-4 group-hover:rotate-12 transition-transform duration-300"></i>
                Lihat Menu Lengkap
            </a>
            <a href="#bestseller"
               class="w-full sm:w-auto bg-white/10 border border-white/25 text-white px-8 py-4 rounded-2xl font-semibold text-sm tracking-wide hover:bg-white/20 active:scale-95 transition-all duration-300 backdrop-blur-sm flex items-center justify-center gap-2.5">
                <i data-lucide="award" class="w-4 h-4"></i>
                Best Seller
            </a>
        </div>

        {{-- Stats Row --}}
        <div class="flex flex-wrap justify-center gap-12 mt-16">
            <div class="text-center">
                <div class="text-3xl font-extrabold text-white tracking-tight">30+</div>
                <div class="text-white/40 text-xs font-medium mt-1 uppercase tracking-widest">Menu</div>
            </div>
            <div class="w-px h-10 bg-white/10 self-center hidden sm:block"></div>
            <div class="text-center">
                <div class="text-3xl font-extrabold text-white tracking-tight">4.9</div>
                <div class="text-white/40 text-xs font-medium mt-1 uppercase tracking-widest">Rating</div>
            </div>
            <div class="w-px h-10 bg-white/10 self-center hidden sm:block"></div>
            <div class="text-center">
                <div class="text-3xl font-extrabold text-white tracking-tight">100%</div>
                <div class="text-white/40 text-xs font-medium mt-1 uppercase tracking-widest">Premium</div>
            </div>
        </div>
    </div>

    {{-- Scroll Indicator --}}
    <div class="absolute bottom-10 left-1/2 -translate-x-1/2 z-10 hidden md:flex flex-col items-center gap-2">
        <span class="text-[10px] uppercase tracking-[0.3em] text-white/30 font-medium">Scroll</span>
        <div class="w-px h-10 bg-gradient-to-b from-white/30 to-transparent animate-pulse"></div>
    </div>
</section>


{{-- ===================== BESTSELLER SECTION ===================== --}}
<section class="py-20 md:py-28 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto" id="bestseller"
         x-data="bestSellerApp()"
         x-init="init()">

    <div class="flex flex-col sm:flex-row sm:items-end justify-between mb-12 gap-4"
         x-data="{ shown: false }" x-intersect.once="shown = true" :class="shown ? 'reveal active' : 'reveal'">
        <div>
            <div class="flex items-center gap-3 mb-3">
                <div class="w-8 h-px bg-[var(--c-md)]"></div>
                <span class="text-xs font-bold text-[var(--c-md)] uppercase tracking-[0.2em]">Favorit Pelanggan</span>
            </div>
            <div class="flex items-center gap-3 flex-wrap">
                <h2 class="text-3xl md:text-4xl font-extrabold text-[var(--c-dk)] tracking-tight">Best Seller</h2>
                {{-- LIVE Badge --}}
                <span class="flex items-center gap-1.5 bg-green-50 border border-green-200 text-green-700 text-[10px] font-bold px-2.5 py-1 rounded-full uppercase tracking-widest">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                    </span>
                    Live
                </span>
            </div>
            <p class="text-[var(--c-md)]/60 text-sm mt-2">Menu yang paling banyak dipesan berdasarkan data transaksi</p>
            {{-- Last updated --}}
            <p class="text-[10px] text-[var(--c-md)]/40 mt-1 font-medium"
               x-show="lastUpdated" x-cloak
               x-text="'Diperbarui ' + lastUpdated"></p>
        </div>
        <a href="{{ route('menu') }}" class="btn-outline shrink-0 self-start sm:self-auto">
            Lihat Semua <i data-lucide="arrow-right" class="w-4 h-4"></i>
        </a>
    </div>

    {{-- Cards Grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 relative"
         x-data="{ shown: false }" x-intersect.once="shown = true" :class="shown ? 'reveal active' : 'reveal'">

        {{-- Skeleton saat loading pertama --}}
        <template x-if="items.length === 0">
            <template x-for="i in [1,2,3,4]" :key="i">
                <div class="bg-white rounded-3xl overflow-hidden border border-[var(--c-lt)]/20 animate-pulse">
                    <div class="h-52 bg-[var(--c-lt)]/30"></div>
                    <div class="p-5 space-y-3">
                        <div class="h-4 bg-[var(--c-lt)]/30 rounded-full w-3/4"></div>
                        <div class="h-3 bg-[var(--c-lt)]/20 rounded-full w-full"></div>
                        <div class="h-3 bg-[var(--c-lt)]/20 rounded-full w-2/3"></div>
                        <div class="h-5 bg-[var(--c-lt)]/30 rounded-full w-1/2 mt-2"></div>
                    </div>
                </div>
            </template>
        </template>

        {{-- Real items --}}
        <template x-for="item in items" :key="item.id">
            <div class="group bg-white rounded-3xl overflow-hidden shadow-sm hover:shadow-xl transition-all duration-500 hover:-translate-y-1 cursor-pointer border border-[var(--c-lt)]/20"
                 :id="'bs-card-' + item.id">
                {{-- Image --}}
                <div class="relative overflow-hidden h-52 bg-[var(--c-dk)]">
                    <template x-if="item.image_url">
                        <img :src="item.image_url" :alt="item.name"
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700">
                    </template>
                    <template x-if="!item.image_url">
                        <div class="absolute inset-0 flex items-center justify-center">
                            <div class="w-20 h-20 rounded-3xl bg-white/8 flex items-center justify-center group-hover:scale-110 transition-all duration-500 border border-white/10">
                                <i data-lucide="coffee" class="w-9 h-9 text-[var(--c-lt)]"></i>
                            </div>
                        </div>
                    </template>
                    {{-- TERLARIS badge --}}
                    <template x-if="item.total_sold > 0">
                        <div class="absolute top-4 left-4">
                            <span class="bg-[#DDD3C9] text-[var(--c-dk)] text-[10px] font-bold px-3 py-1.5 rounded-full uppercase tracking-widest flex items-center gap-1">
                                <i data-lucide="flame" class="w-3 h-3"></i> TERLARIS
                            </span>
                        </div>
                    </template>
                </div>
                {{-- Content --}}
                <div class="p-5">
                    <h3 class="font-bold text-[var(--c-dk)] text-sm leading-snug mb-1" x-text="item.name"></h3>
                    <p class="text-[var(--c-md)]/60 text-xs leading-relaxed mb-4 line-clamp-2" x-text="item.description"></p>
                    <div class="flex items-center justify-between">
                        <span class="font-extrabold text-[var(--c-dk)] text-base"
                              x-text="'Rp ' + item.price.toLocaleString('id-ID')"></span>
                        <div class="flex items-center gap-1.5 bg-[var(--c-bg)] px-2.5 py-1 rounded-full">
                            <i data-lucide="star" class="w-3 h-3 text-[var(--c-md)] fill-[var(--c-md)]"></i>
                            <span class="text-xs font-bold text-[var(--c-dk)]" x-text="parseFloat(item.rating).toFixed(1)"></span>
                        </div>
                    </div>
                    <template x-if="item.total_sold > 0">
                        <div class="mt-2 flex items-center gap-1 text-[var(--c-md)]/60">
                            <i data-lucide="shopping-bag" class="w-3 h-3"></i>
                            <span class="text-[11px] font-medium" x-text="item.total_sold + ' terjual'"></span>
                        </div>
                    </template>
                </div>
            </div>
        </template>
    </div>
</section>

@php
$bestSellersJson = $bestSellers->map(function ($m) {
    return [
        'id'          => $m->id,
        'name'        => $m->name,
        'description' => $m->description,
        'price'       => $m->price,
        'image_url'   => $m->image_url,
        'rating'      => $m->rating ?? 0,
        'total_sold'  => $m->total_sold ?? 0,
    ];
})->values();
@endphp

<script>
function bestSellerApp() {
    return {
        // Data awal dari server (tidak ada loading flash)
        items: @json($bestSellersJson),
        lastUpdated: '',
        _pollTimer: null,

        init() {
            this._setUpdatedNow();
            // Mulai polling setiap 30 detik
            this._pollTimer = setInterval(() => this.refresh(), 30000);
            // Refresh icons setelah Alpine render
            this.$nextTick(() => { if (typeof lucide !== 'undefined') lucide.createIcons(); });
        },

        async refresh() {
            try {
                const res = await fetch('/api/best-sellers', { headers: { Accept: 'application/json' } });
                if (!res.ok) return;
                const data = await res.json();

                // Cek apakah data berubah (bandingkan ID + total_sold)
                const sig = (arr) => arr.map(i => i.id + ':' + i.total_sold).join(',');
                if (sig(data) !== sig(this.items)) {
                    // Fade out → update → fade in
                    const grid = document.querySelector('[id^="bs-card-"]')?.closest('.grid');
                    if (grid) {
                        grid.style.transition = 'opacity 0.35s ease';
                        grid.style.opacity = '0';
                        await new Promise(r => setTimeout(r, 350));
                        this.items = data;
                        this.$nextTick(() => {
                            if (typeof lucide !== 'undefined') lucide.createIcons();
                            grid.style.opacity = '1';
                        });
                    } else {
                        this.items = data;
                        this.$nextTick(() => { if (typeof lucide !== 'undefined') lucide.createIcons(); });
                    }
                }
                this._setUpdatedNow();
            } catch (e) {
                console.warn('Best seller refresh failed:', e);
            }
        },

        _setUpdatedNow() {
            const now = new Date();
            this.lastUpdated = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
        }
    };
}
</script>



{{-- ===================== CATEGORY SECTION ===================== --}}
<section class="py-20 bg-[var(--c-dk)]" id="categories">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12"
             x-data="{ shown: false }" x-intersect.once="shown = true" :class="shown ? 'reveal active' : 'reveal'">
            <div class="flex items-center justify-center gap-3 mb-3">
                <div class="w-8 h-px bg-[var(--c-lt)]/50"></div>
                <span class="text-xs font-bold text-[var(--c-lt)]/70 uppercase tracking-[0.2em]">Eksplorasi</span>
                <div class="w-8 h-px bg-[var(--c-lt)]/50"></div>
            </div>
            <h2 class="text-3xl md:text-4xl font-extrabold text-white tracking-tight">Menu Categories</h2>
            <p class="text-[var(--c-lt)]/60 text-sm mt-2">Temukan minuman & makanan yang kamu sukai</p>
        </div>

        {{-- Kategori dari database (dinamis) --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4"
             x-data="{ shown: false }" x-intersect.once="shown = true" :class="shown ? 'reveal active' : 'reveal'">
            @foreach($categories as $cat)
            <a href="{{ route('menu', ['category' => $cat['id']]) }}"
               id="cat-{{ \Illuminate\Support\Str::slug($cat['name']) }}"
               class="group flex flex-col items-center p-6 bg-white/5 rounded-3xl hover:bg-white/10 active:scale-95 transition-all duration-300 text-center cursor-pointer border border-white/8 hover:border-white/20 backdrop-blur-sm">
                <div class="w-16 h-16 rounded-2xl {{ $cat['bg'] }} flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300 shadow-md">
                    <i data-lucide="{{ $cat['icon'] }}" class="w-7 h-7 text-white"></i>
                </div>
                <h3 class="font-bold text-white text-sm mb-1">{{ $cat['name'] }}</h3>
                <span class="text-[10px] bg-white/10 text-[var(--c-lt)] px-3 py-1 rounded-full font-semibold border border-white/10 mt-auto">{{ $cat['count'] }} menu</span>
            </a>
            @endforeach
        </div>
    </div>
</section>


{{-- ===================== GALLERY SLIDER ===================== --}}
<section class="py-20 overflow-hidden" id="gallery">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row sm:items-end justify-between mb-10 gap-3"
             x-data="{ shown: false }" x-intersect.once="shown = true" :class="shown ? 'reveal active' : 'reveal'">
            <div>
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-8 h-px bg-[var(--c-md)]"></div>
                    <span class="text-xs font-bold text-[var(--c-md)] uppercase tracking-[0.2em]">Our Vibe</span>
                </div>
                <h2 class="text-3xl md:text-4xl font-extrabold text-[var(--c-dk)] tracking-tight">Galeri Skena</h2>
                <p class="text-[var(--c-md)]/70 text-sm mt-2">Intip keseruan dan momen hangat di Skena Coffee.</p>
            </div>
            <a href="https://www.instagram.com/skenacoffee.id/" target="_blank"
               class="inline-flex items-center gap-2 text-sm font-semibold text-[var(--c-dk)] hover:text-[var(--c-md)] transition-colors shrink-0 group">
                <svg class="w-4 h-4 group-hover:scale-110 transition-transform" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="20" x="2" y="2" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" x2="17.51" y1="6.5" y2="6.5"/></svg> @skenacoffee.id
            </a>
        </div>
    </div>

    {{-- Slider --}}
    <div x-data="{
            current: 0,
            total: 5,
            timer: null,
            startX: 0,
            next() { this.current = (this.current + 1) % this.total; this.resetTimer(); },
            prev() { this.current = (this.current - 1 + this.total) % this.total; this.resetTimer(); },
            goTo(i) { this.current = i; this.resetTimer(); },
            resetTimer() { clearInterval(this.timer); this.timer = setInterval(() => this.next(), 4500); },
            init() { this.timer = setInterval(() => this.next(), 4500); }
         }"
         class="relative"
         @touchstart.passive="startX = $event.touches[0].clientX"
         @touchend.passive="let dx = $event.changedTouches[0].clientX - startX; if(dx < -50) next(); else if(dx > 50) prev();">

        <div class="overflow-hidden">
            <div class="flex transition-transform duration-700 ease-in-out"
                 :style="`transform: translateX(-${current * 100}%)`">

                {{-- SLIDE 1 --}}
                <div class="w-full shrink-0 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
                    <div class="grid grid-cols-4 grid-rows-2 gap-3 md:gap-4 h-[260px] sm:h-[380px] md:h-[480px]">
                        <div class="col-start-1 col-end-3 row-start-1 row-end-3 group relative rounded-3xl overflow-hidden shadow-md">
                            <img src="{{ asset('images/tempat/1.jpg') }}" alt="Galeri 1" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/40 to-transparent pointer-events-none"></div>
                        </div>
                        <div class="col-start-3 col-end-5 row-start-1 row-end-2 group relative rounded-3xl overflow-hidden shadow-md">
                            <img src="{{ asset('images/tempat/2.jpg') }}" alt="Galeri 2" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700">
                        </div>
                        <div class="col-start-3 col-end-4 row-start-2 row-end-3 group relative rounded-3xl overflow-hidden shadow-md">
                            <img src="{{ asset('images/tempat/3.jpg') }}" alt="Galeri 3" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700">
                        </div>
                        <div class="col-start-4 col-end-5 row-start-2 row-end-3 group relative rounded-3xl overflow-hidden shadow-md">
                            <img src="{{ asset('images/tempat/4.jpg') }}" alt="Galeri 4" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700">
                        </div>
                    </div>
                </div>

                {{-- SLIDE 2 --}}
                <div class="w-full shrink-0 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
                    <div class="grid grid-cols-4 grid-rows-2 gap-3 md:gap-4 h-[260px] sm:h-[380px] md:h-[480px]">
                        <div class="col-start-1 col-end-3 row-start-1 row-end-3 group relative rounded-3xl overflow-hidden shadow-md">
                            <img src="{{ asset('images/tempat/5.jpg') }}" alt="Galeri 5" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/40 to-transparent pointer-events-none"></div>
                        </div>
                        <div class="col-start-3 col-end-4 row-start-1 row-end-2 group relative rounded-3xl overflow-hidden shadow-md">
                            <img src="{{ asset('images/tempat/6.jpg') }}" alt="Galeri 6" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700">
                        </div>
                        <div class="col-start-4 col-end-5 row-start-1 row-end-3 group relative rounded-3xl overflow-hidden shadow-md">
                            <img src="{{ asset('images/tempat/7.jpg') }}" alt="Galeri 7" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700">
                        </div>
                        <div class="col-start-3 col-end-4 row-start-2 row-end-3 group relative rounded-3xl overflow-hidden shadow-md">
                            <img src="{{ asset('images/tempat/8.jpg') }}" alt="Galeri 8" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700">
                        </div>
                    </div>
                </div>

                {{-- SLIDE 3 --}}
                <div class="w-full shrink-0 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
                    <div class="grid grid-cols-4 grid-rows-2 gap-3 md:gap-4 h-[260px] sm:h-[380px] md:h-[480px]">
                        <div class="col-start-1 col-end-3 row-start-1 row-end-2 group relative rounded-3xl overflow-hidden shadow-md">
                            <img src="{{ asset('images/tempat/9.jpg') }}" alt="Galeri 9" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700">
                        </div>
                        <div class="col-start-3 col-end-5 row-start-1 row-end-3 group relative rounded-3xl overflow-hidden shadow-md">
                            <img src="{{ asset('images/tempat/10.jpg') }}" alt="Galeri 10" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/40 to-transparent pointer-events-none"></div>
                        </div>
                        <div class="col-start-1 col-end-2 row-start-2 row-end-3 group relative rounded-3xl overflow-hidden shadow-md">
                            <img src="{{ asset('images/tempat/11.jpg') }}" alt="Galeri 11" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700">
                        </div>
                        <div class="col-start-2 col-end-3 row-start-2 row-end-3 group relative rounded-3xl overflow-hidden shadow-md">
                            <img src="{{ asset('images/tempat/12.jpg') }}" alt="Galeri 12" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700">
                        </div>
                    </div>
                </div>

                {{-- SLIDE 4 --}}
                <div class="w-full shrink-0 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
                    <div class="grid grid-cols-4 grid-rows-2 gap-3 md:gap-4 h-[260px] sm:h-[380px] md:h-[480px]">
                        <div class="col-start-1 col-end-2 row-start-1 row-end-3 group relative rounded-3xl overflow-hidden shadow-md">
                            <img src="{{ asset('images/tempat/13.jpg') }}" alt="Galeri 13" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700">
                        </div>
                        <div class="col-start-2 col-end-4 row-start-1 row-end-2 group relative rounded-3xl overflow-hidden shadow-md">
                            <img src="{{ asset('images/tempat/14.jpg') }}" alt="Galeri 14" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700">
                        </div>
                        <div class="col-start-4 col-end-5 row-start-1 row-end-3 group relative rounded-3xl overflow-hidden shadow-md">
                            <img src="{{ asset('images/tempat/15.jpg') }}" alt="Galeri 15" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700">
                        </div>
                        <div class="col-start-2 col-end-4 row-start-2 row-end-3 group relative rounded-3xl overflow-hidden shadow-md">
                            <img src="{{ asset('images/tempat/16.jpg') }}" alt="Galeri 16" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700">
                        </div>
                    </div>
                </div>

                {{-- SLIDE 5 --}}
                <div class="w-full shrink-0 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
                    <div class="grid grid-cols-4 grid-rows-2 gap-3 md:gap-4 h-[260px] sm:h-[380px] md:h-[480px]">
                        <div class="col-start-1 col-end-3 row-start-1 row-end-3 group relative rounded-3xl overflow-hidden shadow-md">
                            <img src="{{ asset('images/tempat/17.jpg') }}" alt="Galeri 17" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/40 to-transparent pointer-events-none"></div>
                        </div>
                        <div class="col-start-3 col-end-4 row-start-1 row-end-2 group relative rounded-3xl overflow-hidden shadow-md">
                            <img src="{{ asset('images/tempat/18.jpg') }}" alt="Galeri 18" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700">
                        </div>
                        <div class="col-start-4 col-end-5 row-start-1 row-end-2 group relative rounded-3xl overflow-hidden shadow-md">
                            <img src="{{ asset('images/tempat/19.jpg') }}" alt="Galeri 19" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700">
                        </div>
                        <div class="col-start-3 col-end-5 row-start-2 row-end-3 group relative rounded-3xl overflow-hidden shadow-md">
                            <img src="{{ asset('images/tempat/20.jpg') }}" alt="Galeri 20" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700">
                        </div>
                    </div>
                </div>

            </div>
        </div>

        {{-- Prev Button --}}
        <button @click="prev()"
                class="absolute left-6 sm:left-10 top-1/2 -translate-y-1/2 z-10 w-11 h-11 bg-white/90 backdrop-blur-sm rounded-full shadow-lg flex items-center justify-center hover:bg-white hover:scale-110 transition-all duration-200">
            <i data-lucide="chevron-left" class="w-5 h-5 text-[var(--c-dk)]"></i>
        </button>

        {{-- Next Button --}}
        <button @click="next()"
                class="absolute right-6 sm:right-10 top-1/2 -translate-y-1/2 z-10 w-11 h-11 bg-white/90 backdrop-blur-sm rounded-full shadow-lg flex items-center justify-center hover:bg-white hover:scale-110 transition-all duration-200">
            <i data-lucide="chevron-right" class="w-5 h-5 text-[var(--c-dk)]"></i>
        </button>

        {{-- Dots --}}
        <div class="flex items-center justify-center gap-2 mt-8">
            <template x-for="i in total" :key="i">
                <button @click="goTo(i - 1)"
                        :class="current === i - 1 ? 'w-8 bg-[var(--c-dk)]' : 'w-2.5 bg-[var(--c-lt)] hover:bg-[var(--c-md)]'"
                        class="h-2.5 rounded-full transition-all duration-300"></button>
            </template>
        </div>

    </div>
</section>


{{-- ===================== LOKASI SECTION ===================== --}}
<section class="py-20 md:py-28 bg-white" id="lokasi">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="text-center mb-12"
             x-data="{ shown: false }" x-intersect.once="shown = true" :class="shown ? 'reveal active' : 'reveal'">
            <div class="flex items-center justify-center gap-3 mb-3">
                <div class="w-8 h-px bg-[var(--c-md)]"></div>
                <span class="text-xs font-bold text-[var(--c-md)] uppercase tracking-[0.2em]">Kunjungi Kami</span>
                <div class="w-8 h-px bg-[var(--c-md)]"></div>
            </div>
            <h2 class="text-3xl md:text-4xl font-extrabold text-[var(--c-dk)] tracking-tight">Temukan Kami</h2>
            <p class="text-[var(--c-md)]/70 text-sm mt-2">Datang langsung dan nikmati suasana hangat Skena Coffee</p>
        </div>

        <div x-data="{ shown: false }" x-intersect.once="shown = true" :class="shown ? 'reveal active' : 'reveal'">
            <div class="w-full rounded-3xl overflow-hidden shadow-xl border border-[var(--c-lt)]/20">
                <iframe
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3068.274795427453!2d119.45021647498227!3d-5.143546694833702!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2dbee30043f47725%3A0x43705439482bcfcc!2sSkena%20Coffee%20(Racing%20Centre)!5e1!3m2!1sid!2sid!4v1779996579867!5m2!1sid!2sid"
                    width="100%"
                    height="460"
                    style="border:0; display:block;"
                    allowfullscreen=""
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"
                    title="Lokasi Skena Coffee">
                </iframe>
            </div>
        </div>

    </div>
</section>


{{-- ===================== JAM OPERASIONAL SECTION ===================== --}}
<section class="py-20 md:py-28 bg-[var(--c-bg)]" id="jam-operasional">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="text-center mb-12"
             x-data="{ shown: false }" x-intersect.once="shown = true" :class="shown ? 'reveal active' : 'reveal'">
            <div class="flex items-center justify-center gap-3 mb-3">
                <div class="w-8 h-px bg-[var(--c-md)]"></div>
                <span class="text-xs font-bold text-[var(--c-md)] uppercase tracking-[0.2em]">Operasional Cafe</span>
                <div class="w-8 h-px bg-[var(--c-md)]"></div>
            </div>
            <h2 class="text-3xl md:text-4xl font-extrabold text-[var(--c-dk)] tracking-tight">Jam Buka Kami</h2>
            <p class="text-[var(--c-md)]/70 text-sm mt-2">Kami hadir setiap hari untuk menemani harimu</p>
        </div>

        <div x-data="{ shown: false }" x-intersect.once="shown = true" :class="shown ? 'reveal active' : 'reveal'"
             class="max-w-2xl mx-auto">

            {{-- Status --}}
            @php
                $now = now()->setTimezone('Asia/Makassar');
                $hour = (int) $now->format('H');
                $minute = (int) $now->format('i');
                $dayOfWeek = (int) $now->format('N');
                $timeInMinutes = $hour * 60 + $minute;
                if ($dayOfWeek === 6) {
                    $isOpen = $timeInMinutes >= (8 * 60) || $timeInMinutes < (2 * 60);
                } else {
                    $isOpen = $timeInMinutes >= (7 * 60) || $timeInMinutes < (1 * 60);
                }
            @endphp

            <div class="flex items-center justify-center gap-3 mb-10">
                @if($isOpen)
                    <div class="flex items-center gap-3 bg-[#DDD3C9]/30 border border-[#DDD3C9] px-5 py-2.5 rounded-full shadow-sm">
                        <span class="relative flex h-2.5 w-2.5">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
                        </span>
                        <span class="text-[var(--c-dk)] font-bold text-sm">Sedang Buka</span>
                    </div>
                @else
                    <div class="flex items-center gap-3 bg-red-50 border border-red-200 px-5 py-2.5 rounded-full shadow-sm">
                        <span class="flex h-2.5 w-2.5 rounded-full bg-red-500"></span>
                        <span class="text-red-700 font-bold text-sm">Sedang Tutup</span>
                    </div>
                @endif
            </div>

            {{-- Hours Cards --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                {{-- Sun-Fri --}}
                <div class="bg-white rounded-3xl p-7 border border-[var(--c-lt)]/20 shadow-sm hover:shadow-md transition-shadow duration-300">
                    <div class="flex items-center gap-2.5 mb-5">
                        <div class="w-9 h-9 rounded-xl bg-amber-50 flex items-center justify-center">
                            <i data-lucide="sun" class="w-4.5 h-4.5 text-amber-500"></i>
                        </div>
                        <span class="text-xs font-bold text-[var(--c-md)] uppercase tracking-wider">Minggu – Jumat</span>
                    </div>
                    <p class="text-[var(--c-dk)] text-4xl font-black tracking-tight">07<span class="text-[var(--c-lt)] mx-2 font-light text-2xl">—</span>01</p>
                    <p class="text-[var(--c-md)]/50 text-xs mt-2 flex items-center gap-1.5">
                        <i data-lucide="clock" class="w-3 h-3"></i>
                        pagi sampai dini hari
                    </p>
                </div>

                {{-- Saturday --}}
                <div class="bg-white rounded-3xl p-7 border border-[var(--c-lt)]/20 shadow-sm hover:shadow-md transition-shadow duration-300">
                    <div class="flex items-center gap-2.5 mb-5">
                        <div class="w-9 h-9 rounded-xl bg-[var(--c-bg)] flex items-center justify-center">
                            <i data-lucide="moon" class="w-4.5 h-4.5 text-[var(--c-md)]"></i>
                        </div>
                        <span class="text-xs font-bold text-[var(--c-md)] uppercase tracking-wider">Sabtu</span>
                    </div>
                    <p class="text-[var(--c-dk)] text-4xl font-black tracking-tight">08<span class="text-[var(--c-lt)] mx-2 font-light text-2xl">—</span>02</p>
                    <p class="text-[var(--c-md)]/50 text-xs mt-2 flex items-center gap-1.5">
                        <i data-lucide="clock" class="w-3 h-3"></i>
                        pagi sampai dini hari
                    </p>
                </div>
            </div>

        </div>
    </div>
</section>

{{-- Bottom padding for mobile nav --}}
<div class="md:hidden h-20"></div>

@endsection
