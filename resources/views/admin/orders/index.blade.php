@extends('layouts.admin')

@section('title', 'Pesanan')

@section('content')
    {{-- Filter --}}
    <form method="GET" action="{{ route('admin.orders.index') }}" class="mb-4 flex flex-wrap items-end gap-2 rounded-xl bg-white p-3 shadow-sm">
        <div>
            <label class="mb-1 block text-xs text-slate-500">Tanggal</label>
            <input type="date" name="date" value="{{ $dateInput }}"
                   class="rounded-lg border border-slate-300 px-2 py-1.5 text-sm">
        </div>
        <div>
            <label class="mb-1 block text-xs text-slate-500">Status</label>
            <select name="status" class="rounded-lg border border-slate-300 bg-white px-2 py-1.5 text-sm">
                <option value="">Semua</option>
                @foreach ($statuses as $key => $label)
                    <option value="{{ $key }}" @selected($status === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="min-w-40 flex-1">
            <label class="mb-1 block text-xs text-slate-500">Cari</label>
            <input type="search" name="q" value="{{ $q }}" placeholder="Nama / WA / kode"
                   class="w-full rounded-lg border border-slate-300 px-2 py-1.5 text-sm">
        </div>
        <button class="rounded-lg bg-sky-600 px-4 py-1.5 text-sm font-semibold text-white hover:bg-sky-700">Tampilkan</button>
        <a href="{{ route('admin.orders.index', ['date' => 'all']) }}" class="px-2 py-1.5 text-sm text-sky-700 underline">Semua tanggal</a>
        <a href="{{ route('admin.orders.index') }}" class="px-2 py-1.5 text-sm text-sky-700 underline">Hari ini</a>
    </form>

    {{-- Ringkasan --}}
    <div class="mb-4 grid grid-cols-2 gap-3 md:grid-cols-4">
        <div class="rounded-xl bg-white p-3 shadow-sm">
            <p class="text-xs text-slate-500">Pesanan</p>
            <p class="text-xl font-bold">{{ $stats['orders'] }}</p>
        </div>
        <div class="rounded-xl bg-white p-3 shadow-sm">
            <p class="text-xs text-slate-500">Total galon</p>
            <p class="text-xl font-bold">{{ $stats['gallons'] }}</p>
        </div>
        <div class="rounded-xl bg-white p-3 shadow-sm">
            <p class="text-xs text-slate-500">Sudah lunas</p>
            <p class="text-xl font-bold text-green-700">{{ \App\Services\Pricing::rp($stats['paid']) }}</p>
        </div>
        <div class="rounded-xl bg-white p-3 shadow-sm">
            <p class="text-xs text-slate-500">Belum lunas</p>
            <p class="text-xl font-bold text-amber-600">{{ \App\Services\Pricing::rp($stats['unpaid']) }}</p>
        </div>
    </div>

    {{-- Daftar pesanan --}}
    <div class="space-y-3">
        @forelse ($orders as $order)
            @php
                $badge = match ($order->order_status) {
                    'baru' => 'bg-sky-100 text-sky-700',
                    'diproses' => 'bg-amber-100 text-amber-700',
                    'diantar' => 'bg-violet-100 text-violet-700',
                    'selesai' => 'bg-green-100 text-green-700',
                    default => 'bg-slate-200 text-slate-600',
                };
            @endphp

            <div class="rounded-xl bg-white p-4 shadow-sm">
                <div class="flex flex-wrap items-start justify-between gap-2">
                    <div>
                        <p class="font-semibold">{{ $order->name }}</p>
                        <p class="text-xs text-slate-500">
                            <span class="font-mono">{{ $order->order_code }}</span> · {{ $order->created_at->format('d M Y H:i') }}
                        </p>
                    </div>
                    <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $badge }}">{{ $order->status_label }}</span>
                </div>

                <div class="mt-3 grid gap-x-6 gap-y-1 text-sm md:grid-cols-2">
                    <p>
                        <span class="text-slate-500">WA:</span>
                        <a href="{{ $order->wa_link }}" target="_blank" rel="noopener" class="text-green-700 underline">{{ $order->whatsapp }}</a>
                    </p>
                    <p><span class="text-slate-500">Pesanan:</span> {{ $order->qty }} galon × {{ $order->price_rp }}</p>
                    <p class="md:col-span-2">
                        <span class="text-slate-500">{{ $order->delivery_label }}</span>
                        @if ($order->delivery_method === 'cod')
                            · {{ $order->distance_km }} km (ongkir {{ $order->fee_rp }}) · {{ $order->address }}
                        @endif
                    </p>
                    @if ($order->maps_url)
                        <p class="md:col-span-2">
                            <a href="{{ $order->maps_url }}" target="_blank" rel="noopener"
                               class="inline-flex items-center gap-1 rounded-lg bg-sky-50 px-3 py-1.5 font-medium text-sky-700 hover:bg-sky-100">
                                📍 Buka di Google Maps
                            </a>
                        </p>
                    @endif
                    <p>
                        <span class="text-slate-500">Bayar:</span> {{ $order->method_label }} ·
                        <span class="{{ $order->payment_status === 'paid' ? 'font-semibold text-green-700' : 'text-amber-600' }}">{{ $order->payment_label }}</span>
                    </p>
                    <p class="font-bold">Total {{ $order->total_rp }}</p>
                </div>

                <div class="mt-3 flex flex-wrap items-center gap-2 border-t border-slate-100 pt-3">
                    <form method="POST" action="{{ route('admin.orders.update', $order) }}" class="flex flex-wrap items-center gap-2">
                        @csrf
                        @method('PATCH')
                        <select name="order_status" class="rounded-lg border border-slate-300 bg-white px-2 py-1.5 text-sm">
                            @foreach ($statuses as $key => $label)
                                <option value="{{ $key }}" @selected($order->order_status === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <select name="payment_status" class="rounded-lg border border-slate-300 bg-white px-2 py-1.5 text-sm">
                            @foreach ($paymentStatuses as $key => $label)
                                <option value="{{ $key }}" @selected($order->payment_status === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <button class="rounded-lg bg-sky-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-sky-700">Simpan</button>
                    </form>

                    <form method="POST" action="{{ route('admin.orders.destroy', $order) }}" class="ml-auto"
                          onsubmit="return confirm('Hapus pesanan {{ $order->order_code }}?')">
                        @csrf
                        @method('DELETE')
                        <button class="rounded-lg px-3 py-1.5 text-sm text-red-600 hover:bg-red-50">Hapus</button>
                    </form>
                </div>
            </div>
        @empty
            <div class="rounded-xl bg-white p-8 text-center text-slate-500 shadow-sm">Belum ada pesanan untuk filter ini.</div>
        @endforelse
    </div>

    <div class="mt-4">{{ $orders->links() }}</div>
@endsection