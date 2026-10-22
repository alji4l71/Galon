<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', 'Admin') - {{ \App\Models\Setting::get('business_name') }}</title>
    <style>[x-cloak]{display:none !important}</style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 text-slate-800 antialiased">
    @php
        $nav = [
            ['admin.orders.index', 'admin.orders.*', 'Pesanan'],
            ['admin.customers.index', 'admin.customers.*', 'Pelanggan'],
            ['admin.reports.index', 'admin.reports.*', 'Laporan'],
            ['admin.settings.edit', 'admin.settings.*', 'Pengaturan'],
        ];
        $bizName = \App\Models\Setting::get('business_name');
    @endphp

    <header class="bg-sky-700 text-white print:hidden">
        <div class="mx-auto flex max-w-5xl items-center gap-2 px-4 py-3">
            {{-- Tombol buka sidebar (mobile) --}}
            <button type="button" id="sidebarOpen" aria-label="Buka menu" aria-controls="sidebar"
                    class="-ml-2 rounded-lg p-2 hover:bg-white/10 md:hidden">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>

            <a href="{{ route('admin.orders.index') }}" class="font-bold">💧 {{ $bizName }}</a>

            {{-- Menu desktop --}}
            <nav class="ml-4 hidden flex-1 items-center gap-1 text-sm md:flex">
                @foreach ($nav as [$route, $pattern, $label])
                    <a href="{{ route($route) }}"
                       class="rounded-lg px-3 py-1.5 {{ request()->routeIs($pattern) ? 'bg-white/20 font-semibold' : 'hover:bg-white/10' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </nav>

            <form method="POST" action="{{ route('logout') }}" class="ml-auto hidden md:block">
                @csrf
                <button class="rounded-lg px-3 py-1.5 text-sm hover:bg-white/10">Keluar</button>
            </form>
        </div>
    </header>

    {{-- Sidebar mobile --}}
    <div id="sidebarOverlay" class="fixed inset-0 z-40 hidden bg-black/50 md:hidden print:hidden"></div>

    <aside id="sidebar" aria-label="Menu admin"
           class="fixed inset-y-0 left-0 z-50 flex w-64 -translate-x-full flex-col bg-sky-800 text-white transition-transform duration-200 md:hidden print:hidden">
        <div class="flex items-center justify-between px-4 py-4">
            <span class="font-bold">💧 {{ $bizName }}</span>
            <button type="button" id="sidebarClose" aria-label="Tutup menu" class="rounded-lg p-1.5 hover:bg-white/10">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" d="M6 6l12 12M18 6L6 18"/>
                </svg>
            </button>
        </div>

        <nav class="flex-1 space-y-1 px-3">
            @foreach ($nav as [$route, $pattern, $label])
                <a href="{{ route($route) }}"
                   class="block rounded-lg px-3 py-2.5 text-sm {{ request()->routeIs($pattern) ? 'bg-white/20 font-semibold' : 'hover:bg-white/10' }}">
                    {{ $label }}
                </a>
            @endforeach
        </nav>

        <form method="POST" action="{{ route('logout') }}" class="border-t border-white/20 p-3">
            @csrf
            <button class="w-full rounded-lg px-3 py-2.5 text-left text-sm hover:bg-white/10">Keluar</button>
        </form>
    </aside>

    <main class="mx-auto max-w-5xl px-4 py-5">
        @if (session('ok'))
            <div class="mb-4 rounded-xl border border-green-200 bg-green-50 p-3 text-sm text-green-800">{{ session('ok') }}</div>
        @endif

        @if ($errors->any())
            <div class="mb-4 rounded-xl border border-red-200 bg-red-50 p-3 text-sm text-red-700">
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>

    <script>
        (function () {
            var sidebar = document.getElementById('sidebar');
            var overlay = document.getElementById('sidebarOverlay');

            function setOpen(open) {
                sidebar.classList.toggle('-translate-x-full', !open);
                overlay.classList.toggle('hidden', !open);
                document.body.classList.toggle('overflow-hidden', open);
            }

            document.getElementById('sidebarOpen').addEventListener('click', function () { setOpen(true); });
            document.getElementById('sidebarClose').addEventListener('click', function () { setOpen(false); });
            overlay.addEventListener('click', function () { setOpen(false); });
            document.addEventListener('keydown', function (e) { if (e.key === 'Escape') setOpen(false); });

            // Kalau layar dilebarkan ke desktop, pastikan sidebar tertutup.
            window.matchMedia('(min-width: 768px)').addEventListener('change', function (e) {
                if (e.matches) setOpen(false);
            });
        })();
    </script>

    @stack('scripts')
</body>
</html>