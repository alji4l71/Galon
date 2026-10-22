@extends('layouts.app')

@section('title', 'Pembayaran')

@section('content')
    <div class="space-y-4" x-data="{ method: @js(old('payment_method')) }">

        {{-- Ringkasan pesanan --}}
        <section class="space-y-1 rounded-2xl bg-white p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <h2 class="font-semibold">Pesanan Anda</h2>
                <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-mono">{{ $order->order_code }}</span>
            </div>

            <div class="flex justify-between text-sm">
                <span class="text-slate-600">{{ $order->qty }} galon × {{ $order->price_rp }}</span>
                <span>{{ $order->subtotal_rp }}</span>
            </div>

            @if ($order->delivery_method === 'cod')
                <div class="flex justify-between text-sm">
                    <span class="text-slate-600">Ongkir {{ $order->distance_km }} km</span>
                    <span>{{ $order->fee_rp }}</span>
                </div>
            @else
                <div class="flex justify-between text-sm">
                    <span class="text-slate-600">Ambil di tempat</span>
                    <span>Rp 0</span>
                </div>
            @endif

            <div class="flex items-center justify-between border-t border-slate-200 pt-2">
                <span class="font-semibold">Total</span>
                <span class="text-xl font-bold text-sky-700">{{ $order->total_rp }}</span>
            </div>
        </section>

        <form method="POST" action="{{ route('payment.store', $order) }}" class="space-y-4">
            @csrf
            <input type="hidden" name="payment_method" x-bind:value="method">

            @error('payment_method')
                <div class="rounded-xl border border-red-200 bg-red-50 p-3 text-sm text-red-700">{{ $message }}</div>
            @enderror

            {{-- Pilihan metode --}}
            <section class="space-y-3 rounded-2xl bg-white p-4 shadow-sm">
                <h2 class="font-semibold">Pilih Pembayaran</h2>

                <div class="grid grid-cols-2 gap-2">
                    <button type="button" x-on:click="method = 'qris'"
                            class="rounded-xl border-2 p-3 text-center text-sm"
                            x-bind:class="method === 'qris' ? 'border-sky-600 bg-sky-50 font-semibold text-sky-700' : 'border-slate-200'">
                        📲 QRIS
                    </button>
                    <button type="button" x-on:click="method = 'cash'"
                            class="rounded-xl border-2 p-3 text-center text-sm"
                            x-bind:class="method === 'cash' ? 'border-sky-600 bg-sky-50 font-semibold text-sky-700' : 'border-slate-200'">
                        💵 Cash
                    </button>
                </div>

                {{-- QRIS --}}
                <div x-show="method === 'qris'" x-cloak class="text-center">
                    @if ($qrisUrl)
                        <img src="{{ $qrisUrl }}" alt="QRIS" class="mx-auto w-64 max-w-full rounded-lg border border-slate-200">
                        <p class="mt-3 text-sm text-slate-600">
                            Scan QRIS di atas, bayar sebesar
                            <span class="font-bold text-sky-700">{{ $order->total_rp }}</span>,
                            lalu tekan tombol di bawah.
                        </p>
                    @else
                        <p class="rounded-lg bg-amber-50 p-3 text-sm text-amber-700">
                            QRIS belum tersedia saat ini. Silakan pilih Cash.
                        </p>
                    @endif
                </div>

                {{-- Cash --}}
                <div x-show="method === 'cash'" x-cloak class="rounded-lg bg-slate-50 p-3 text-sm text-slate-600">
                    @if ($order->delivery_method === 'cod')
                        Siapkan uang tunai sebesar <span class="font-bold text-sky-700">{{ $order->total_rp }}</span>
                        dan bayar langsung saat galon diantar.
                    @else
                        Bayar tunai sebesar <span class="font-bold text-sky-700">{{ $order->total_rp }}</span>
                        saat Anda mengambil galon di tempat.
                    @endif
                </div>
            </section>

            <button type="submit"
                    x-bind:disabled="!method || (method === 'qris' && {{ $qrisUrl ? 'false' : 'true' }})"
                    class="w-full rounded-xl bg-sky-600 py-3 text-base font-semibold text-white shadow active:bg-sky-700 disabled:cursor-not-allowed disabled:opacity-40">
                <span x-text="method === 'qris' ? 'Saya Sudah Bayar' : (method === 'cash' ? 'Konfirmasi Pesanan' : 'Pilih metode pembayaran')"></span>
            </button>
        </form>
    </div>
@endsection
