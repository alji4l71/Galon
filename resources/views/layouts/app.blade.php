<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Isi Ulang Galon') - {{ \App\Models\Setting::get('business_name') }}</title>
    <style>[x-cloak]{display:none !important}</style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-sky-50 text-slate-800 antialiased">
    <header class="bg-sky-600 text-white">
        <div class="mx-auto max-w-md px-4 py-4">
            <h1 class="text-lg font-bold">💧 {{ \App\Models\Setting::get('business_name') }}</h1>
            <p class="text-sm text-sky-100">Isi ulang air galon</p>
        </div>
    </header>

    <main class="mx-auto max-w-md px-4 py-5">
        @yield('content')
    </main>

    @stack('scripts')
</body>
</html>
