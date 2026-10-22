@extends('layouts.admin')

@section('title', $customer->exists ? 'Edit Pelanggan' : 'Pelanggan Baru')

@section('content')
    <div class="mx-auto max-w-lg">
        <h1 class="mb-4 text-lg font-bold">{{ $customer->exists ? 'Edit Pelanggan' : 'Pelanggan Baru' }}</h1>

        <form method="POST"
              action="{{ $customer->exists ? route('admin.customers.update', $customer) : route('admin.customers.store') }}"
              class="space-y-4 rounded-xl bg-white p-5 shadow-sm">
            @csrf
            @if ($customer->exists)
                @method('PUT')
            @endif

            <div>
                <label for="name" class="mb-1 block text-sm text-slate-600">Nama</label>
                <input id="name" name="name" type="text" required maxlength="100"
                       value="{{ old('name', $customer->name) }}"
                       class="w-full rounded-lg border border-slate-300 px-3 py-2">
            </div>

            <div>
                <label for="whatsapp" class="mb-1 block text-sm text-slate-600">Nomor WhatsApp</label>
                <input id="whatsapp" name="whatsapp" type="tel" placeholder="08123456789"
                       value="{{ old('whatsapp', \App\Support\Phone::local($customer->whatsapp)) }}"
                       class="w-full rounded-lg border border-slate-300 px-3 py-2">
            </div>

            <div>
                <label for="address" class="mb-1 block text-sm text-slate-600">Alamat</label>
                <textarea id="address" name="address" rows="3" maxlength="255"
                          class="w-full rounded-lg border border-slate-300 px-3 py-2">{{ old('address', $customer->address) }}</textarea>
            </div>

            <p class="text-xs text-slate-500">Data ini akan terisi otomatis di form pemesanan saat pelanggan scan QR.</p>

            <div class="flex gap-2">
                <button class="rounded-lg bg-sky-600 px-5 py-2 font-semibold text-white hover:bg-sky-700">Simpan</button>
                <a href="{{ route('admin.customers.index') }}" class="rounded-lg px-5 py-2 text-slate-600 hover:bg-slate-100">Batal</a>
            </div>
        </form>
    </div>
@endsection
