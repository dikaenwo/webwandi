<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login — Skena Coffee</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans bg-[var(--c-bg)] flex items-center justify-center min-h-screen p-4" data-theme="brown">

    <div class="w-full max-w-md bg-white rounded-[2rem] shadow-2xl p-8 sm:p-10 border border-[var(--c-lt)]/30">
        
        {{-- Logo & Header --}}
        <div class="text-center mb-10">
            <div class="w-16 h-16 rounded-2xl flex items-center justify-center p-1.5 mx-auto mb-5">
                <img src="{{ asset('images/logo.png') }}" alt="Logo" class="w-full h-full object-contain">
            </div>
            <h1 class="text-2xl font-extrabold text-[var(--c-dk)]">Admin Panel</h1>
            <p class="text-[var(--c-md)]/70 text-sm mt-1.5">Skena Coffee Management</p>
        </div>

        {{-- Error Message --}}
        @if ($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-xl mb-6 text-sm flex items-start gap-3">
                <i data-lucide="alert-circle" class="w-5 h-5 shrink-0 mt-0.5"></i>
                <div>
                    @foreach ($errors->all() as $error)
                        <p class="font-medium">{{ $error }}</p>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Login Form --}}
        <form action="{{ route('admin.login') }}" method="POST" class="space-y-5">
            @csrf
            
            <div>
                <label class="block text-sm font-bold text-[var(--c-dk)] mb-2">Email Address</label>
                <div class="relative">
                    <i data-lucide="mail" class="w-5 h-5 text-[var(--c-md)]/50 absolute left-4 top-1/2 -translate-y-1/2"></i>
                    <input type="email" name="email" value="{{ old('email') }}" required autofocus
                           class="w-full bg-[var(--c-bg)]/50 border border-[var(--c-lt)]/50 rounded-xl px-12 py-3.5 text-sm text-[var(--c-dk)] focus:outline-none focus:border-[var(--c-dk)] focus:ring-1 focus:ring-[var(--c-dk)] transition-colors"
                           placeholder="admin@skenacoffee.id">
                </div>
            </div>

            <div>
                <label class="block text-sm font-bold text-[var(--c-dk)] mb-2">Password</label>
                <div class="relative">
                    <i data-lucide="lock" class="w-5 h-5 text-[var(--c-md)]/50 absolute left-4 top-1/2 -translate-y-1/2"></i>
                    <input type="password" name="password" required
                           class="w-full bg-[var(--c-bg)]/50 border border-[var(--c-lt)]/50 rounded-xl px-12 py-3.5 text-sm text-[var(--c-dk)] focus:outline-none focus:border-[var(--c-dk)] focus:ring-1 focus:ring-[var(--c-dk)] transition-colors"
                           placeholder="••••••••">
                </div>
            </div>

            <div class="flex items-center justify-between pt-2">
                <label class="flex items-center gap-2 cursor-pointer group">
                    <div class="relative flex items-center justify-center">
                        <input type="checkbox" name="remember" class="peer appearance-none w-5 h-5 border border-[var(--c-lt)] rounded-md checked:bg-[var(--c-dk)] checked:border-[var(--c-dk)] transition-colors cursor-pointer">
                        <i data-lucide="check" class="w-3 h-3 text-white absolute opacity-0 peer-checked:opacity-100 pointer-events-none transition-opacity"></i>
                    </div>
                    <span class="text-sm text-[var(--c-md)] group-hover:text-[var(--c-dk)] transition-colors">Ingat Saya</span>
                </label>
            </div>

            <button type="submit" class="w-full bg-[var(--c-dk)] text-[var(--c-bg)] py-4 rounded-xl font-bold hover:bg-[var(--c-md)] transition-colors duration-300 mt-4 flex items-center justify-center gap-2 group">
                Masuk ke Dashboard
                <i data-lucide="arrow-right" class="w-4 h-4 group-hover:translate-x-1 transition-transform"></i>
            </button>
        </form>

        <div class="mt-8 text-center">
            <a href="{{ route('home') }}" class="text-sm text-[var(--c-md)]/60 hover:text-[var(--c-dk)] transition-colors inline-flex items-center gap-1.5">
                <i data-lucide="arrow-left" class="w-3 h-3"></i> Kembali ke Website
            </a>
        </div>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
