@extends('layouts.admin')

@section('title', 'Pelanggan')

@section('content')
    <div class="mb-4 flex flex-wrap items-center gap-2">
        <h1 class="mr-auto text-lg font-bold">Pelanggan</h1>

        <form method="GET" action="{{ route('admin.customers.index') }}" class="flex gap-2">
            <input type="search" name="q" value="{{ $q }}" placeholder="Cari nama / WA / alamat"
                   class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm">
            <button class="rounded-lg bg-white px-3 py-1.5 text-sm shadow-sm hover:bg-slate-50">Cari</button>
        </form>

        {{-- Cetak QR: kosong = semua pelanggan --}}
        <form id="printForm" method="GET" action="{{ route('admin.customers.print') }}" target="_blank">
            <button class="rounded-lg bg-white px-3 py-1.5 text-sm shadow-sm hover:bg-slate-50">🖨️ Cetak QR (terpilih / semua)</button>
        </form>

        <a href="{{ route('admin.customers.create') }}"
           class="rounded-lg bg-sky-600 px-4 py-1.5 text-sm font-semibold text-white hover:bg-sky-700">+ Pelanggan</a>
    </div>

    <div class="overflow-x-auto rounded-xl bg-white shadow-sm">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-slate-200 bg-slate-50 text-xs uppercase text-slate-500">
                <tr>
                    <th class="px-3 py-2"><input type="checkbox" onchange="toggleAll(this)" aria-label="Pilih semua"></th>
                    <th class="px-3 py-2">Nama</th>
                    <th class="px-3 py-2">WhatsApp</th>
                    <th class="px-3 py-2">Alamat</th>
                    <th class="px-3 py-2 text-center">Pesanan</th>
                    <th class="px-3 py-2 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($customers as $c)
                    <tr>
                        <td class="px-3 py-2">
                            <input type="checkbox" class="js-pick" name="ids[]" value="{{ $c->id }}" form="printForm">
                        </td>
                        <td class="px-3 py-2 font-medium">{{ $c->name }}</td>
                        <td class="px-3 py-2">{{ $c->whatsapp ?: '-' }}</td>
                        <td class="max-w-xs truncate px-3 py-2 text-slate-600">{{ $c->address ?: '-' }}</td>
                        <td class="px-3 py-2 text-center">{{ $c->orders_count }}</td>
                        <td class="whitespace-nowrap px-3 py-2 text-right">
                            <a href="{{ route('admin.customers.qr', $c) }}" class="text-sky-700 underline">QR</a>
                            <a href="{{ route('admin.customers.edit', $c) }}" class="ml-3 text-slate-600 underline">Edit</a>
                            <form method="POST" action="{{ route('admin.customers.token', $c) }}" class="ml-3 inline"
                                  onsubmit="return confirm(@js('Reset QR milik ' . $c->name . '? QR lama yang sudah dicetak tidak akan berlaku lagi.'))">
                                @csrf
                                <button class="text-amber-600 underline">Reset QR</button>
                            </form>
                            <form method="POST" action="{{ route('admin.customers.destroy', $c) }}" class="ml-3 inline"
                                  onsubmit="return confirm(@js('Hapus pelanggan ' . $c->name . '? QR-nya tidak akan berlaku lagi.'))">
                                @csrf
                                @method('DELETE')
                                <button class="text-red-600 underline">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-3 py-8 text-center text-slate-500">Belum ada pelanggan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $customers->links() }}</div>
@endsection

@push('scripts')
    <script>
        function toggleAll(source) {
            document.querySelectorAll('input.js-pick').forEach(function (box) { box.checked = source.checked; });
        }
    </script>
@endpush