<!DOCTYPE html>
<html lang="id" x-data="{ theme: 'green' }" :data-theme="theme" class="scroll-smooth">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="description" content="@yield('meta_description', 'Skena Coffee — Ngopi Santai, Pesan Lebih Mudah. Self-order kopi premium dengan pengalaman modern.')">
    <title>@yield('title', 'Skena Coffee')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&family=Playfair+Display:ital,wght@0,700;0,800;1,700&display=swap" rel="stylesheet">

    {{-- Lucide Icons --}}
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>

    {{-- Vite Assets --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Alpine.js Plugins & Core --}}
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/intersect@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    @stack('head')
</head>
<body class="font-sans bg-[var(--c-bg)] text-[var(--c-dk)] antialiased">

{{-- ===================== NAVBAR ===================== --}}
<nav class="sticky top-0 z-50 bg-white/90 backdrop-blur-md border-b border-[var(--c-lt)]/40 shadow-sm"
     x-data="{ scrolled: false, mobileOpen: false }"
     @scroll.window="scrolled = window.scrollY > 20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">

            {{-- Logo --}}
            <a href="{{ route('home') }}" class="flex items-center gap-2.5 group">
                <img src="{{ asset('images/logo.png') }}" alt="Skena Coffee" class="h-8 w-auto object-contain">
                <img src="{{ asset('images/LOGO TEKS - HIJAU.png') }}" alt="Skena Coffee Text" class="h-10 w-auto object-contain">
            </a>



            {{-- Right Actions --}}
            <div class="flex items-center gap-3">
                {{-- Desktop Nav Links --}}
                <div class="hidden md:flex items-center gap-7 mr-2">
                    <a :href="`{{ route('home') }}${$store.cart.tableNumber ? '?table=' + $store.cart.tableNumber : ''}`" class="nav-link pb-1 {{ request()->routeIs('home') ? 'text-[var(--c-dk)] after:w-full!' : '' }}">Beranda</a>
                    <a :href="`{{ route('menu') }}${$store.cart.tableNumber ? '?table=' + $store.cart.tableNumber : ''}`" class="nav-link pb-1 {{ request()->routeIs('menu') ? 'text-[var(--c-dk)] after:w-full!' : '' }}">Menu</a>
                    <a :href="`{{ route('order.status') }}${$store.cart.tableNumber ? '?table=' + $store.cart.tableNumber : ''}`" class="nav-link pb-1 {{ request()->routeIs('order.status') ? 'text-[var(--c-dk)] after:w-full!' : '' }}">Status</a>
                </div>
                {{-- Cart Button --}}
                @unless(request()->routeIs('home'))
                <a :href="`{{ route('cart') }}${$store.cart.tableNumber ? '?table=' + $store.cart.tableNumber : ''}`" id="cart-btn"
                   x-data x-show="$store.cart.tableNumber" x-cloak
                   class="relative flex items-center gap-2 bg-[var(--c-dk)] text-[var(--c-bg)] px-4 py-2 rounded-xl text-sm font-medium hover:bg-[var(--c-md)] transition-all duration-300 shadow-sm">
                    <i data-lucide="shopping-bag" class="w-4 h-4"></i>
                    <span class="hidden sm:inline">Keranjang</span>
                    <span id="cart-count-badge"
                          x-show="$store.cart.count > 0"
                          x-text="$store.cart.count"
                          class="absolute -top-2 -right-2 bg-[var(--c-lt)] text-[var(--c-dk)] text-xs font-bold w-5 h-5 rounded-full flex items-center justify-center">
                    </span>
                </a>
                @endunless

                {{-- Mobile Hamburger --}}
                <button @click="mobileOpen = !mobileOpen"
                        class="md:hidden flex items-center justify-center w-10 h-10 rounded-xl border border-[var(--c-lt)] hover:bg-[var(--c-bg)] transition-colors duration-200"
                        id="hamburger-btn"
                        aria-label="Toggle Menu">
                    <i data-lucide="menu" class="w-5 h-5" x-show="!mobileOpen"></i>
                    <i data-lucide="x" class="w-5 h-5" x-show="mobileOpen" x-cloak></i>
                </button>
            </div>
        </div>

        {{-- Mobile Menu Dropdown --}}
        <div x-show="mobileOpen"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-2"
             x-cloak
             class="md:hidden pb-4 border-t border-[var(--c-lt)]/30 mt-1 pt-3 space-y-1">
            <a :href="`{{ route('home') }}${$store.cart.tableNumber ? '?table=' + $store.cart.tableNumber : ''}`" @click="mobileOpen = false"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-[var(--c-dk)] hover:bg-[var(--c-bg)] transition-colors duration-200">
                <i data-lucide="home" class="w-4 h-4 text-[var(--c-md)]"></i> Beranda
            </a>
            <a :href="`{{ route('menu') }}${$store.cart.tableNumber ? '?table=' + $store.cart.tableNumber : ''}`" @click="mobileOpen = false"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-[var(--c-dk)] hover:bg-[var(--c-bg)] transition-colors duration-200">
                <i data-lucide="coffee" class="w-4 h-4 text-[var(--c-md)]"></i> Menu
            </a>
            <a :href="`{{ route('order.status') }}${$store.cart.tableNumber ? '?table=' + $store.cart.tableNumber : ''}`" @click="mobileOpen = false"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-[var(--c-dk)] hover:bg-[var(--c-bg)] transition-colors duration-200">
                <i data-lucide="receipt" class="w-4 h-4 text-[var(--c-md)]"></i> Status Pesanan
            </a>
        </div>
    </div>
</nav>

{{-- ===================== MAIN CONTENT ===================== --}}
<main>
    @yield('content')
</main>

{{-- ===================== FOOTER ===================== --}}
@unless(request()->routeIs('admin.*'))
<footer class="bg-[var(--c-dk)] text-[var(--c-bg)] mt-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-10">
            {{-- Brand --}}
            <div>
                <div class="flex items-center gap-2.5 mb-4">
                    <div class="w-9 h-9 rounded-xl bg-white/90 flex items-center justify-center p-1.5 shadow-sm">
                        <img src="{{ asset('images/logo.png') }}" alt="Logo" class="w-full h-full object-contain">
                    </div>
                    <span class="text-lg font-bold text-[var(--c-bg)]">Skena Coffee</span>
                </div>
                <p class="text-[var(--c-lt)] text-sm leading-relaxed">
                    Ngopi santai, pesan lebih mudah. Kopi premium dengan pengalaman self-order modern yang nyaman.
                </p>
                <div class="flex gap-3 mt-5">
                    <a href="https://www.instagram.com/skenacoffee.id/" target="_blank" class="w-8 h-8 rounded-lg bg-[var(--c-bg)]/10 flex items-center justify-center hover:bg-[var(--c-lt)]/20 transition-colors">
                        <svg class="w-4 h-4 text-[var(--c-lt)]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="20" x="2" y="2" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" x2="17.51" y1="6.5" y2="6.5"/></svg>
                    </a>
                    <a href="https://api.whatsapp.com/send/?phone=6282395955955&text&type=phone_number&app_absent=0" target="_blank" class="w-8 h-8 rounded-lg bg-[var(--c-bg)]/10 flex items-center justify-center hover:bg-[var(--c-lt)]/20 transition-colors">
                        <i data-lucide="phone" class="w-4 h-4 text-[var(--c-lt)]"></i>
                    </a>
                </div>
            </div>

            {{-- Quick Links --}}
            <div>
                <h3 class="font-semibold text-[var(--c-bg)] mb-4 text-sm uppercase tracking-wider">Menu</h3>
                <ul class="space-y-2.5">
                    @foreach(['Hot Coffee', 'Cold Coffee', 'Non Coffee', 'Food', 'Appetizer'] as $cat)
                    <li>
                        <a href="{{ route('menu') }}" class="text-[var(--c-lt)] text-sm hover:text-[var(--c-bg)] transition-colors duration-200 flex items-center gap-2">
                            <i data-lucide="chevron-right" class="w-3 h-3"></i>{{ $cat }}
                        </a>
                    </li>
                    @endforeach
                </ul>
            </div>

            {{-- Contact --}}
            <div>
                <h3 class="font-semibold text-[var(--c-bg)] mb-4 text-sm uppercase tracking-wider">Kontak</h3>
                <ul class="space-y-3">
                    <li class="flex items-start gap-3">
                        <i data-lucide="map-pin" class="w-4 h-4 text-[var(--c-lt)] mt-0.5 shrink-0"></i>
                        <span class="text-[var(--c-lt)] text-sm">
                            <span class="font-bold block mb-1">Skena Coffee (Racing Centre)</span>
                            VF43+H43, Jl. Racing Centre, Karampuang, Kec. Panakkukang, Kota Makassar, Sulawesi Selatan 90231
                        </span>
                    </li>
                    <li class="flex items-center gap-3">
                        <i data-lucide="clock" class="w-4 h-4 text-[var(--c-lt)] shrink-0"></i>
                        <span class="text-[var(--c-lt)] text-sm">08.00 – 22.00 WIB</span>
                    </li>
                    <li>
                        <a href="https://www.instagram.com/skenacoffee.id/" target="_blank" class="flex items-center gap-3 group">
                            <svg class="w-4 h-4 text-[var(--c-lt)] shrink-0 group-hover:text-[var(--c-bg)] transition-colors" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="20" x="2" y="2" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" x2="17.51" y1="6.5" y2="6.5"/></svg>
                            <span class="text-[var(--c-lt)] text-sm group-hover:text-[var(--c-bg)] transition-colors">@skenacoffee.id</span>
                        </a>
                    </li>
                    <li>
                        <a href="https://api.whatsapp.com/send/?phone=6282395955955&text&type=phone_number&app_absent=0" target="_blank" class="flex items-center gap-3 group">
                            <i data-lucide="phone" class="w-4 h-4 text-[var(--c-lt)] shrink-0 group-hover:text-[var(--c-bg)] transition-colors"></i>
                            <span class="text-[var(--c-lt)] text-sm group-hover:text-[var(--c-bg)] transition-colors">+62 823-9595-5955 (WhatsApp)</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <div class="border-t border-[var(--c-bg)]/10 mt-10 pt-6 flex flex-col sm:flex-row items-center justify-between gap-3">
            <p class="text-[var(--c-lt)] text-xs">© {{ date('Y') }} Skena Coffee. All rights reserved.</p>
            <p class="text-[var(--c-lt)] text-xs flex items-center gap-1">Made with coffee & love by Skena Team</p>
        </div>
    </div>
</footer>
@endunless

{{-- ===================== TOAST NOTIFICATION ===================== --}}
<div x-data
     x-show="$store.toast.visible"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 translate-y-4"
     x-transition:enter-end="opacity-100 translate-y-0"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100 translate-y-0"
     x-transition:leave-end="opacity-0 translate-y-4"
     x-cloak
     class="fixed bottom-6 left-1/2 -translate-x-1/2 z-[9999] bg-[var(--c-dk)] text-[var(--c-bg)] px-5 py-3 rounded-2xl shadow-xl flex items-center gap-3 text-sm font-medium min-w-[220px] justify-center">
    <i data-lucide="check-circle" class="w-4 h-4 text-[var(--c-lt)] shrink-0"></i>
    <span x-text="$store.toast.message"></span>
</div>


<script>
    // Initialize Lucide icons after DOM is ready
    document.addEventListener('DOMContentLoaded', () => {
        lucide.createIcons();
    });

    // Re-init Lucide after Alpine updates DOM
    document.addEventListener('alpine:initialized', () => {
        lucide.createIcons();
    });

    // Mutation observer to handle dynamically added icons
    const observer = new MutationObserver(() => {
        // Disconnect to prevent infinite loops if lucide modifies DOM
        observer.disconnect();
        lucide.createIcons();
        // Reconnect
        observer.observe(document.body, { childList: true, subtree: true });
    });
    observer.observe(document.body, { childList: true, subtree: true });
</script>

@stack('scripts')
</body>
</html>
