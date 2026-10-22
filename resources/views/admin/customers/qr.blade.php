@extends('layouts.admin')

@section('title', 'QR ' . $customer->name)

@section('content')
    <div class="mx-auto max-w-sm">
        <div class="rounded-2xl bg-white p-6 text-center shadow-sm">
            <p class="text-sm text-slate-500">{{ \App\Models\Setting::get('business_name') }}</p>
            <h1 class="text-lg font-bold">{{ $customer->name }}</h1>

            <div data-qr="{{ $customer->order_url }}" class="mx-auto my-4 w-64 max-w-full [&>svg]:h-auto [&>svg]:w-full"></div>

            <p class="text-sm text-slate-600">Scan untuk pesan isi ulang galon</p>
            <p class="mt-2 break-all text-xs text-slate-400">{{ $customer->order_url }}</p>
        </div>

        <div class="mt-4 flex flex-wrap justify-center gap-2 print:hidden">
            <button type="button" onclick="downloadQrPng()" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">⬇️ Unduh PNG</button>
            <button type="button" onclick="window.print()" class="rounded-lg bg-sky-600 px-4 py-2 text-sm font-semibold text-white hover:bg-sky-700">🖨️ Cetak</button>
            <a href="{{ $customer->order_url }}" target="_blank" class="rounded-lg bg-white px-4 py-2 text-sm shadow-sm hover:bg-slate-50">Buka form</a>
            <a href="{{ route('admin.customers.index') }}" class="rounded-lg px-4 py-2 text-sm text-slate-600 hover:bg-slate-200">Kembali</a>
        </div>

        <form method="POST" action="{{ route('admin.customers.token', $customer) }}" class="mt-6 text-center print:hidden"
              onsubmit="return confirm('Buat token baru? QR yang sudah dicetak sebelumnya tidak akan berlaku lagi.')">
            @csrf
            <button class="text-xs text-red-600 underline">Buat token baru (jika QR bocor atau hilang)</button>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('[data-qr]').forEach(function (el) { window.renderQr(el, el.dataset.qr); });
        });

        const QR_CARD = {
            business: @js((string) \App\Models\Setting::get('business_name')),
            name: @js($customer->name),
            caption: 'Scan untuk pesan isi ulang galon',
            filename: @js('qr-' . \Illuminate\Support\Str::slug($customer->name) . '.png'),
        };

        // Teks di tengah canvas; ukuran font dikecilkan otomatis kalau terlalu panjang.
        function drawCenteredText(ctx, text, y, size, weight, color, maxWidth) {
            let s = size;
            ctx.font = weight + ' ' + s + 'px sans-serif';
            while (ctx.measureText(text).width > maxWidth && s > 16) {
                s -= 2;
                ctx.font = weight + ' ' + s + 'px sans-serif';
            }
            ctx.fillStyle = color;
            ctx.textAlign = 'center';
            ctx.fillText(text, ctx.canvas.width / 2, y);
        }

        function downloadQrPng() {
            const svg = document.querySelector('[data-qr] svg');
            if (!svg) { alert('QR belum siap, muat ulang halaman lalu coba lagi.'); return; }

            // Salin SVG supaya tampilan di halaman tidak berubah.
            const clone = svg.cloneNode(true);
            clone.setAttribute('xmlns', 'http://www.w3.org/2000/svg');
            if (!clone.getAttribute('viewBox')) {
                const w = svg.getBoundingClientRect().width || 256;
                clone.setAttribute('viewBox', '0 0 ' + w + ' ' + w);
            }

            const W = 1000, QR = 760, TOP = 190, H = 1080;
            clone.setAttribute('width', QR);
            clone.setAttribute('height', QR);

            const xml = new XMLSerializer().serializeToString(clone);
            const url = URL.createObjectURL(new Blob([xml], { type: 'image/svg+xml;charset=utf-8' }));

            const img = new Image();
            img.onload = function () {
                const canvas = document.createElement('canvas');
                canvas.width = W;
                canvas.height = H;
                const ctx = canvas.getContext('2d');

                ctx.fillStyle = '#ffffff';
                ctx.fillRect(0, 0, W, H);

                drawCenteredText(ctx, QR_CARD.business, 80, 36, 'normal', '#64748b', W - 120);
                drawCenteredText(ctx, QR_CARD.name, 148, 56, 'bold', '#0f172a', W - 120);

                ctx.drawImage(img, (W - QR) / 2, TOP, QR, QR);

                drawCenteredText(ctx, QR_CARD.caption, TOP + QR + 70, 34, 'normal', '#334155', W - 120);

                URL.revokeObjectURL(url);

                canvas.toBlob(function (blob) {
                    const a = document.createElement('a');
                    a.href = URL.createObjectURL(blob);
                    a.download = QR_CARD.filename;
                    document.body.appendChild(a);
                    a.click();
                    a.remove();
                    setTimeout(function () { URL.revokeObjectURL(a.href); }, 1000);
                }, 'image/png');
            };
            img.onerror = function () {
                URL.revokeObjectURL(url);
                alert('Gagal membuat gambar PNG.');
            };
            img.src = url;
        }
    </script>
@endpush