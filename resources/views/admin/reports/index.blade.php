@extends('layouts.admin')

@section('title', 'Laporan')

@section('content')
    <div class="mb-4 flex flex-wrap items-end gap-3">
        <h1 class="mr-auto text-lg font-bold">Laporan Bulanan</h1>

        <form method="GET" action="{{ route('admin.reports.index') }}" class="flex items-end gap-2">
            <div>
                <label class="mb-1 block text-xs text-slate-500">Bulan</label>
                <input type="month" name="month" value="{{ $month }}" class="rounded-lg border border-slate-300 px-2 py-1.5 text-sm">
            </div>
            <button class="rounded-lg bg-sky-600 px-4 py-1.5 text-sm font-semibold text-white hover:bg-sky-700">Tampilkan</button>
        </form>

        <a href="{{ route('admin.reports.export', ['month' => $month]) }}"
           class="rounded-lg bg-white px-4 py-1.5 text-sm shadow-sm hover:bg-slate-50">⬇️ Ekspor CSV</a>
    </div>

    <div class="mb-4 grid grid-cols-2 gap-3 md:grid-cols-4">
        <div class="rounded-xl bg-white p-3 shadow-sm">
            <p class="text-xs text-slate-500">Pesanan</p>
            <p class="text-xl font-bold">{{ $totals['orders'] }}</p>
            @if ($cancelled > 0)
                <p class="text-xs text-slate-400">{{ $cancelled }} dibatalkan (tidak dihitung)</p>
            @endif
        </div>
        <div class="rounded-xl bg-white p-3 shadow-sm">
            <p class="text-xs text-slate-500">Galon terjual</p>
            <p class="text-xl font-bold">{{ $totals['gallons'] }}</p>
        </div>
        <div class="rounded-xl bg-white p-3 shadow-sm">
            <p class="text-xs text-slate-500">Sudah lunas</p>
            <p class="text-xl font-bold text-green-700">{{ \App\Services\Pricing::rp($totals['paid']) }}</p>
        </div>
        <div class="rounded-xl bg-white p-3 shadow-sm">
            <p class="text-xs text-slate-500">Belum lunas</p>
            <p class="text-xl font-bold text-amber-600">{{ \App\Services\Pricing::rp($totals['unpaid']) }}</p>
        </div>
    </div>

    <div class="overflow-x-auto rounded-xl bg-white shadow-sm">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-slate-200 bg-slate-50 text-xs uppercase text-slate-500">
                <tr>
                    <th class="px-3 py-2">Tanggal</th>
                    <th class="px-3 py-2 text-right">Pesanan</th>
                    <th class="px-3 py-2 text-right">Galon</th>
                    <th class="px-3 py-2 text-right">Omzet</th>
                    <th class="px-3 py-2 text-right">Lunas</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($days as $d)
                    <tr>
                        <td class="px-3 py-2">{{ \Carbon\Carbon::parse($d->day)->format('d M Y') }}</td>
                        <td class="px-3 py-2 text-right">{{ $d->orders }}</td>
                        <td class="px-3 py-2 text-right">{{ $d->gallons }}</td>
                        <td class="px-3 py-2 text-right">{{ \App\Services\Pricing::rp($d->revenue) }}</td>
                        <td class="px-3 py-2 text-right text-green-700">{{ \App\Services\Pricing::rp($d->paid) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-3 py-8 text-center text-slate-500">Belum ada pesanan di bulan ini.</td></tr>
                @endforelse
            </tbody>
            @if ($days->isNotEmpty())
                <tfoot class="border-t-2 border-slate-200 bg-slate-50 font-semibold">
                    <tr>
                        <td class="px-3 py-2">Total</td>
                        <td class="px-3 py-2 text-right">{{ $totals['orders'] }}</td>
                        <td class="px-3 py-2 text-right">{{ $totals['gallons'] }}</td>
                        <td class="px-3 py-2 text-right">{{ \App\Services\Pricing::rp($totals['revenue']) }}</td>
                        <td class="px-3 py-2 text-right text-green-700">{{ \App\Services\Pricing::rp($totals['paid']) }}</td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>

    <p class="mt-3 text-xs text-slate-500">
        Tip: ekspor CSV tiap akhir tahun sebelum arsip otomatis berjalan pada 1 Januari.
    </p>
@endsection
