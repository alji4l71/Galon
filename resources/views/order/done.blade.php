@extends('layouts.app')

@section('title', 'Pesanan Diterima')

@section('content')
    <div class="space-y-4">
        <section class="rounded-2xl bg-white p-6 text-center shadow-sm">
            @if ($order->order_status === 'dibatalkan')
                <p class="text-4xl">❌</p>
                <h2 class="mt-3 text-lg font-semibold">Pesanan dibatalkan</h2>
            @else
                <p class="text-4xl">✅</p>
                <h2 class="mt-3 text-lg font-semibold">Pesanan diterima</h2>
                <p class="mt-1 text-sm text-slate-600">Terima kasih, {{ $order->name }}. Pesanan Anda sedang kami proses.</p>
            @endif

            <p class="mt-3 inline-block rounded-full bg-slate-100 px-3 py-1 font-mono text-sm">{{ $order->order_code }}</p>
        </section>

        <section class="space-y-2 rounded-2xl bg-white p-4 text-sm shadow-sm">
            <div class="flex justify-between"><span class="text-slate-600">Status</span><span class="font-medium">{{ $order->status_label }}</span></div>
            <div class="flex justify-between"><span class="text-slate-600">Jumlah</span><span>{{ $order->qty }} galon</span></div>
            <div class="flex justify-between"><span class="text-slate-600">Pengambilan</span><span>{{ $order->delivery_label }}</span></div>
            @if ($order->delivery_method === 'cod')
                <div class="flex justify-between"><span class="text-slate-600">Ongkir ({{ $order->distance_km }} km)</span><span>{{ $order->fee_rp }}</span></div>
                <div class="flex justify-between gap-4"><span class="shrink-0 text-slate-600">Alamat</span><span class="text-right">{{ $order->address }}</span></div>
                @if ($order->maps_url)
                    <div class="flex justify-between">
                        <span class="text-slate-600">Lokasi</span>
                        <a href="{{ $order->maps_url }}" target="_blank" rel="noopener" class="text-sky-700 underline">Lihat di peta</a>
                    </div>
                @endif
            @endif
            <div class="flex justify-between"><span class="text-slate-600">Pembayaran</span><span>{{ $order->method_label }} · {{ $order->payment_label }}</span></div>
            <div class="flex justify-between border-t border-slate-200 pt-2 text-base font-bold">
                <span>Total</span><span class="text-sky-700">{{ $order->total_rp }}</span>
            </div>
        </section>

        @if ($order->needsPaymentChoice())
            <a href="{{ route('payment.show', $order) }}"
               class="block w-full rounded-xl bg-sky-600 py-3 text-center font-semibold text-white shadow active:bg-sky-700">
                Pilih metode pembayaran
            </a>
        @endif

        @if ($order->payment_status === 'pending' && $order->payment_method === 'qris')
            <p class="rounded-xl bg-amber-50 p-3 text-sm text-amber-700">
                Pembayaran QRIS Anda sedang menunggu pengecekan dari kami.
            </p>
        @endif

        @if ($waUrl)
            <a href="{{ $waUrl }}" target="_blank" rel="noopener"
               class="block w-full rounded-xl bg-green-600 py-3 text-center font-semibold text-white shadow active:bg-green-700">
                Hubungi Kami via WhatsApp
            </a>
        @endif

        @if ($order->customer)
            <a href="{{ route('order.create', $order->customer->token) }}"
               class="block text-center text-sm text-sky-700 underline">Pesan lagi</a>
        @endif

        <p class="text-center text-xs text-slate-400">
            Mau lihat pesanan ini lagi? Scan QR Anda kapan saja, pesanan akan muncul di halaman pemesanan.
        </p>
    </div>
@endsection