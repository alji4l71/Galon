<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Cetak QR Pelanggan</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-white p-4 text-slate-800 antialiased">
    <div class="mb-4 flex items-center gap-3 print:hidden">
        <button onclick="window.print()" class="rounded-lg bg-sky-600 px-4 py-2 text-sm font-semibold text-white">🖨️ Cetak</button>
        <span class="text-sm text-slate-500">{{ $customers->count() }} QR code</span>
    </div>

    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
        @foreach ($customers as $c)
            <div class="break-inside-avoid rounded-lg border border-slate-300 p-3 text-center">
                <p class="text-xs text-slate-500">{{ \App\Models\Setting::get('business_name') }}</p>
                <p class="truncate text-sm font-bold">{{ $c->name }}</p>
                <div data-qr="{{ $c->order_url }}" class="mx-auto my-2 w-36 max-w-full [&>svg]:h-auto [&>svg]:w-full"></div>
                <p class="text-xs text-slate-600">Scan untuk pesan galon</p>
            </div>
        @endforeach
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('[data-qr]').forEach(function (el) { window.renderQr(el, el.dataset.qr); });
        });
    </script>
</body>
</html>
