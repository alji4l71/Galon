@extends('layouts.admin')

@section('title', 'Pengaturan')

@section('content')
    <div class="mx-auto max-w-lg">
        <h1 class="mb-4 text-lg font-bold">Pengaturan</h1>

        <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data"
              class="space-y-5 rounded-xl bg-white p-5 shadow-sm">
            @csrf
            @method('PUT')

            <div>
                <label for="business_name" class="mb-1 block text-sm text-slate-600">Nama usaha</label>
                <input id="business_name" name="business_name" type="text" required maxlength="100"
                       value="{{ old('business_name', $v['business_name']) }}"
                       class="w-full rounded-lg border border-slate-300 px-3 py-2">
            </div>

            <div>
                <label for="admin_whatsapp" class="mb-1 block text-sm text-slate-600">WhatsApp admin</label>
                <input id="admin_whatsapp" name="admin_whatsapp" type="tel" placeholder="08123456789"
                       value="{{ old('admin_whatsapp', $v['admin_whatsapp']) }}"
                       class="w-full rounded-lg border border-slate-300 px-3 py-2">
                <p class="mt-1 text-xs text-slate-500">Dipakai untuk tombol "Hubungi Kami via WhatsApp" di halaman pelanggan.</p>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label for="price_per_gallon" class="mb-1 block text-sm text-slate-600">Harga per galon (Rp)</label>
                    <input id="price_per_gallon" name="price_per_gallon" type="number" min="0" required
                           value="{{ old('price_per_gallon', $v['price_per_gallon']) }}"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2">
                </div>
                <div>
                    <label for="fee_per_km" class="mb-1 block text-sm text-slate-600">Ongkir per km (Rp)</label>
                    <input id="fee_per_km" name="fee_per_km" type="number" min="0" required
                           value="{{ old('fee_per_km', $v['fee_per_km']) }}"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2">
                </div>
            </div>

            <div>
                <label for="max_qty" class="mb-1 block text-sm text-slate-600">Batas galon per pesanan</label>
                <input id="max_qty" name="max_qty" type="number" min="1" max="500" required
                       value="{{ old('max_qty', $v['max_qty']) }}"
                       class="w-full rounded-lg border border-slate-300 px-3 py-2">
            </div>

            {{-- Aturan jarak --}}
            <fieldset class="space-y-3 rounded-lg border border-slate-200 p-4">
                <legend class="px-1 text-sm font-semibold text-slate-700">Aturan jarak antar (COD)</legend>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="free_km" class="mb-1 block text-sm text-slate-600">Jarak bebas syarat (km)</label>
                        <input id="free_km" name="free_km" type="number" min="0" required
                               value="{{ old('free_km', $v['free_km']) }}"
                               class="w-full rounded-lg border border-slate-300 px-3 py-2">
                    </div>
                    <div>
                        <label for="min_qty_far" class="mb-1 block text-sm text-slate-600">Minimal galon di atasnya</label>
                        <input id="min_qty_far" name="min_qty_far" type="number" min="1" required
                               value="{{ old('min_qty_far', $v['min_qty_far']) }}"
                               class="w-full rounded-lg border border-slate-300 px-3 py-2">
                    </div>
                </div>
                <p class="text-xs text-slate-500">
                    Contoh: 2 dan 5 berarti jarak sampai 2 km boleh pesan berapa pun, di atas 2 km minimal 5 galon.
                    Jarak dibulatkan ke atas (2,1 km dihitung 3 km).
                </p>

                <div>
                    <label for="max_km" class="mb-1 block text-sm text-slate-600">Jarak maksimal (km)</label>
                    <input id="max_km" name="max_km" type="number" min="1" placeholder="Kosongkan = tanpa batas"
                           value="{{ old('max_km', $v['max_km']) }}"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2">
                    <p class="mt-1 text-xs text-slate-500">Pengaman dari pin yang salah atau iseng. Kosongkan kalau tidak ingin ada batas.</p>
                </div>
            </fieldset>

            {{-- Lokasi depot --}}
            <fieldset class="space-y-3 rounded-lg border border-slate-200 p-4">
                <legend class="px-1 text-sm font-semibold text-slate-700">Lokasi depot</legend>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="depot_lat" class="mb-1 block text-sm text-slate-600">Latitude</label>
                        <input id="depot_lat" name="depot_lat" type="number" step="any" required
                               value="{{ old('depot_lat', $v['depot_lat']) }}"
                               class="w-full rounded-lg border border-slate-300 px-3 py-2">
                    </div>
                    <div>
                        <label for="depot_lng" class="mb-1 block text-sm text-slate-600">Longitude</label>
                        <input id="depot_lng" name="depot_lng" type="number" step="any" required
                               value="{{ old('depot_lng', $v['depot_lng']) }}"
                               class="w-full rounded-lg border border-slate-300 px-3 py-2">
                    </div>
                </div>
                <p class="text-xs text-slate-500">
                    Cara mengambil titik: buka Google Maps, klik kanan di lokasi depot, lalu klik angka koordinat paling atas untuk menyalin.
                    Angka pertama adalah latitude, angka kedua longitude.
                    <a href="https://www.google.com/maps/search/?api=1&query={{ old('depot_lat', $v['depot_lat']) }},{{ old('depot_lng', $v['depot_lng']) }}"
                       target="_blank" rel="noopener" class="text-sky-700 underline">Lihat titik saat ini di Google Maps</a>
                </p>
            </fieldset>

            <div>
                <label for="qris" class="mb-1 block text-sm text-slate-600">Gambar QRIS</label>
                @if ($qrisUrl)
                    <img src="{{ $qrisUrl }}" alt="QRIS saat ini" class="mb-2 w-40 rounded-lg border border-slate-200">
                @else
                    <p class="mb-2 rounded-lg bg-amber-50 p-2 text-xs text-amber-700">Belum ada QRIS. Pelanggan hanya bisa memilih Cash.</p>
                @endif
                <input id="qris" name="qris" type="file" accept="image/png,image/jpeg,image/webp"
                       class="block w-full text-sm file:mr-3 file:rounded-lg file:border-0 file:bg-sky-100 file:px-3 file:py-2 file:text-sky-700">
                <p class="mt-1 text-xs text-slate-500">JPG, PNG, atau WEBP, maksimal 2 MB. Kosongkan kalau tidak ingin mengganti.</p>
            </div>

            <button class="rounded-lg bg-sky-600 px-5 py-2 font-semibold text-white hover:bg-sky-700">Simpan Pengaturan</button>
        </form>

        <p class="mt-3 text-xs text-slate-500">
            Perubahan harga hanya berlaku untuk pesanan baru. Pesanan lama tetap memakai harga saat dipesan.
        </p>
    </div>
@endsection