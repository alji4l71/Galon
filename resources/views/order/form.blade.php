@extends('layouts.app')

@section('title', 'Pesan Isi Ulang Galon')

@section('content')
    <div class="space-y-4">

        {{-- Pesanan aktif: supaya pelanggan yang menutup halaman bisa kembali lewat scan QR --}}
        @if ($activeOrder)
            <a href="{{ $activeOrder->needsPaymentChoice() ? route('payment.show', $activeOrder) : route('order.done', $activeOrder) }}"
                class="block rounded-2xl  bg-white p-4 shadow-sm">
                <div class="flex items-center justify-between gap-2">
                    <span class="font-semibold">Pesanan {{ $activeOrder->order_code }}</span>
                    <span class="rounded-full bg-sky-100 px-2 py-0.5 text-xs font-medium text-sky-700">
                        {{ $activeOrder->status_label }}
                    </span>
                </div>
                <p class="mt-1 text-sm text-slate-600">
                    {{ $activeOrder->qty }} galon · {{ $activeOrder->total_rp }}
                </p>
                <p class="mt-2 text-sm font-semibold text-sky-700">
                    {{ $activeOrder->needsPaymentChoice() ? 'Lanjutkan pembayaran →' : 'Lihat status pesanan →' }}
                </p>
            </a>
        @endif

        <form method="POST"
                action="{{ route('order.store', $customer->token) }}"
                x-data="orderForm(@js($config))"
                class="space-y-4">
            @csrf

            @if ($errors->any())
                <div class="rounded-xl border border-red-200 bg-red-50 p-3 text-sm text-red-700">
                    <p class="font-semibold">Mohon periksa lagi:</p>
                    <ul class="mt-1 list-disc pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Data pemesan --}}
            <section class="space-y-3 rounded-2xl bg-white p-4 shadow-sm">
                <h2 class="font-semibold">Data Pemesan</h2>

                <div>
                    <label for="name" class="mb-1 block text-sm text-slate-600">Nama</label>
                    <input id="name" name="name" type="text" required maxlength="100"
                           value="{{ old('name', $customer->name) }}"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500">
                </div>

                <div>
                    <label for="whatsapp" class="mb-1 block text-sm text-slate-600">Nomor WhatsApp</label>
                    <input id="whatsapp" name="whatsapp" type="tel" inputmode="tel" required
                           placeholder="08123456789"
                           value="{{ old('whatsapp', $whatsappLocal) }}"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500">
                </div>

                <div>
                    <label for="address" class="mb-1 block text-sm text-slate-600">
                        Alamat <span class="text-slate-400" x-show="method === 'pickup'">(boleh dikosongkan)</span>
                    </label>
                    <textarea id="address" name="address" rows="2" maxlength="255"
                              placeholder="Contoh: rumah cat hijau, gang 3, samping warung"
                              class="w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500">{{ old('address', $customer->address) }}</textarea>
                </div>
            </section>

            {{-- Jumlah galon --}}
            <section class="rounded-2xl bg-white p-4 shadow-sm">
                <div class="flex items-center justify-between">
                    <h2 class="font-semibold">Jumlah Galon</h2>
                    <span class="text-sm text-slate-500">
                        {{ \App\Services\Pricing::rp($s['price']) }} / galon
                    </span>
                </div>

                <div class="mt-3 flex items-center justify-center gap-3">
                    <button type="button" x-on:click="dec()"
                            class="h-11 w-11 rounded-full bg-sky-100 text-2xl font-bold text-sky-700 active:bg-sky-200"
                            aria-label="Kurangi">−</button>

                    <input name="qty" type="number" inputmode="numeric" min="1" max="{{ $s['max_qty'] }}"
                           x-model.number="qty"
                           class="w-20 rounded-lg border border-slate-300 py-2 text-center text-xl font-semibold focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500">

                    <button type="button" x-on:click="inc()"
                            class="h-11 w-11 rounded-full bg-sky-600 text-2xl font-bold text-white active:bg-sky-700"
                            aria-label="Tambah">+</button>
                </div>
                <p class="mt-2 text-center text-xs text-slate-400">Maksimal {{ $s['max_qty'] }} galon per pesanan</p>

                {{-- Syarat minimal galon untuk jarak jauh --}}
                <p x-show="farRule" x-cloak
                   class="mt-2 text-center text-xs"
                   x-bind:class="qtyTooLow ? 'font-semibold text-red-600' : 'text-slate-500'">
                    Jarak ± <span x-text="km"></span> km: minimal <span x-text="minQty"></span> galon.
                </p>
            </section>

            {{-- Cara pengambilan --}}
            <section class="space-y-3 rounded-2xl bg-white p-4 shadow-sm">
                <h2 class="font-semibold">Cara Pengambilan</h2>

                <div class="grid grid-cols-2 gap-2">
                    <label class="cursor-pointer rounded-xl border-2 p-3 text-center text-sm"
                           x-bind:class="method === 'cod' ? 'border-sky-600 bg-sky-50 font-semibold text-sky-700' : 'border-slate-200'">
                        <input type="radio" name="delivery_method" value="cod" x-model="method" class="sr-only">
                        🛵 COD<br><span class="text-xs font-normal text-slate-500">Diantar ke lokasi Anda</span>
                    </label>
                    <label class="cursor-pointer rounded-xl border-2 p-3 text-center text-sm"
                           x-bind:class="method === 'pickup' ? 'border-sky-600 bg-sky-50 font-semibold text-sky-700' : 'border-slate-200'">
                        <input type="radio" name="delivery_method" value="pickup" x-model="method" class="sr-only">
                        🏪 Ambil di tempat<br><span class="text-xs font-normal text-slate-500">Tanpa ongkir</span>
                    </label>
                </div>
            </section>

            {{-- Lokasi pengantaran (peta). Ongkir dihitung otomatis dari titik ini. --}}
            <section x-show="method === 'cod'" x-cloak class="space-y-3 rounded-2xl bg-white p-4 shadow-sm">
                <div class="flex items-center justify-between">
                    <h2 class="font-semibold">Lokasi Pengantaran</h2>
                    <span class="text-xs font-medium text-red-600">Wajib untuk COD</span>
                </div>
                <p class="text-sm text-slate-600">
                    Tandai titik rumah Anda di peta. Ongkir dihitung otomatis dari jarak ke depot kami.
                </p>
                <p class="text-xs text-slate-500">
                    Jarak di atas {{ $s['free_km'] }} km: minimal pesan {{ min($s['min_qty_far'], $s['max_qty']) }} galon.
                </p>

                <button type="button" x-on:click="useMyLocation()" x-bind:disabled="geoLoading"
                        class="w-full rounded-xl border border-sky-600 bg-sky-50 py-2.5 text-sm font-semibold text-sky-700 active:bg-sky-100 disabled:opacity-60">
                    <span x-text="geoLoading ? 'Mencari lokasi…' : '📍 Gunakan lokasi saya'"></span>
                </button>

                <div x-ref="map" class="relative z-0 h-64 w-full overflow-hidden rounded-xl border border-slate-200"></div>

                <p x-show="geoMsg" x-text="geoMsg" class="text-xs text-amber-700"></p>

                <div x-show="lat !== null" class="space-y-1 text-xs text-slate-500">
                    <p>
                        ✅ Pin terpasang. Geser pin atau ketuk peta untuk mengubah posisi.
                        <button type="button" x-on:click="clearPin()" class="ml-1 text-red-600 underline">Hapus pin</button>
                    </p>
                    <p class="text-sm font-semibold text-sky-700">
                        Jarak ke depot ± <span x-text="km"></span> km
                        <span class="font-normal text-slate-400">(garis lurus)</span>
                    </p>
                    <p x-show="tooFar" class="text-sm font-semibold text-red-600">
                        Di luar jangkauan antar (maksimal <span x-text="maxKm"></span> km). Silakan pilih ambil di tempat.
                    </p>
                </div>
                <p x-show="lat === null" class="text-xs text-slate-400">
                    Belum ada pin. Ketuk tombol di atas, atau ketuk langsung di peta.
                </p>

                <input type="hidden" name="latitude" x-bind:value="lat !== null ? lat.toFixed(7) : ''">
                <input type="hidden" name="longitude" x-bind:value="lng !== null ? lng.toFixed(7) : ''">
            </section>

            {{-- Ringkasan --}}
            <section class="space-y-2 rounded-2xl bg-white p-4 shadow-sm">
                <h2 class="font-semibold">Ringkasan</h2>

                <div class="flex justify-between text-sm">
                    <span class="text-slate-600"><span x-text="qty || 0"></span> galon × <span x-text="rp(price)"></span></span>
                    <span x-text="rp(gallonTotal)"></span>
                </div>

                <div class="flex justify-between text-sm" x-show="method === 'cod'" x-cloak>
                    <span class="text-slate-600">
                        Ongkir <span x-show="lat !== null">(<span x-text="km"></span> km)</span>
                    </span>
                    <span x-show="lat !== null" x-text="rp(fee)"></span>
                    <span x-show="lat === null" class="text-xs text-amber-600">Tandai lokasi dulu</span>
                </div>

                <div class="flex items-center justify-between border-t border-slate-200 pt-2">
                    <span class="font-semibold">Total Pembayaran</span>
                    <span class="text-xl font-bold text-sky-700" x-text="rp(total)"></span>
                </div>
            </section>

            <button type="submit" x-bind:disabled="!canSubmit"
                    class="w-full rounded-xl bg-sky-600 py-3 text-base font-semibold text-white shadow active:bg-sky-700 disabled:cursor-not-allowed disabled:opacity-50">
                <span x-text="submitLabel"></span>
            </button>
        </form>

        {{-- Riwayat pesanan --}}
        @if ($orders->isNotEmpty())
            <section class="rounded-2xl bg-white p-4 shadow-sm">
                <h2 class="mb-2 font-semibold">Pesanan Terakhir</h2>
                <div class="divide-y divide-slate-100">
                    @foreach ($orders as $o)
                        <a href="{{ route('order.done', $o) }}" class="flex items-center justify-between py-2 text-sm">
                            <span>
                                <span class="font-mono">{{ $o->order_code }}</span><br>
                                <span class="text-xs text-slate-500">
                                    {{ $o->created_at->format('d M Y, H:i') }} · {{ $o->qty }} galon
                                </span>
                            </span>
                            <span class="text-right">
                                {{ $o->total_rp }}<br>
                                <span class="text-xs text-slate-500">{{ $o->status_label }}</span>
                            </span>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif
    </div>
@endsection

@push('scripts')
    <script>
        // Kalkulasi langsung di browser. Total resmi dihitung ulang di server.
        function orderForm(c) {
            // Objek Leaflet disimpan di luar state Alpine supaya tidak dibungkus Proxy.
            let map = null;
            let marker = null;

            // Jarak garis lurus (km), rumus Haversine. Sama dengan di server.
            function haversine(lat1, lng1, lat2, lng2) {
                const rad = (d) => d * Math.PI / 180;
                const dLat = rad(lat2 - lat1);
                const dLng = rad(lng2 - lng1);
                const a = Math.sin(dLat / 2) ** 2
                    + Math.cos(rad(lat1)) * Math.cos(rad(lat2)) * Math.sin(dLng / 2) ** 2;
                return 6371 * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
            }

            return {
                price: c.price,
                perKm: c.perKm,
                maxQty: c.maxQty,
                freeKm: c.freeKm,
                minQtyFar: c.minQtyFar,
                maxKm: c.maxKm, // null = tanpa batas
                qty: c.old.qty ?? 1,
                method: c.old.method ?? 'cod',

                lat: c.map.lat,
                lng: c.map.lng,
                geoLoading: false,
                geoMsg: '',

                init() {
                    this.$watch('method', (m) => {
                        if (m === 'cod') this.$nextTick(() => this.mountMap());
                    });
                    if (this.method === 'cod') this.$nextTick(() => this.mountMap());
                },

                // Jarak dibulatkan ke atas, minimal 1 km.
                get km() {
                    if (this.lat === null || this.lng === null) return 0;
                    const d = haversine(c.map.depot[0], c.map.depot[1], this.lat, this.lng);
                    return Math.max(1, Math.ceil(d));
                },
                get tooFar() {
                    return this.method === 'cod' && this.lat !== null
                        && this.maxKm !== null && this.km > this.maxKm;
                },

                // Syarat minimal galon untuk jarak jauh.
                get farRule() { return this.method === 'cod' && this.lat !== null && this.km > this.freeKm; },
                get minQty() { return this.farRule ? this.minQtyFar : 1; },
                get qtyTooLow() { return (Number(this.qty) || 0) < this.minQty; },

                get gallonTotal() { return (Number(this.qty) || 0) * this.price; },
                get fee() { return this.method === 'cod' && this.lat !== null ? this.km * this.perKm : 0; },
                get total() { return this.gallonTotal + this.fee; },

                get canSubmit() {
                    if (this.method !== 'cod') return true;
                    return this.lat !== null && !this.tooFar && !this.qtyTooLow;
                },
                get submitLabel() {
                    if (this.method === 'cod' && this.lat === null) return 'Tandai lokasi dulu';
                    if (this.tooFar) return 'Lokasi di luar jangkauan';
                    if (this.method === 'cod' && this.qtyTooLow) return 'Minimal ' + this.minQty + ' galon untuk jarak ini';
                    return 'Lanjut ke Pembayaran';
                },

                rp(n) { return 'Rp ' + new Intl.NumberFormat('id-ID').format(n); },
                inc() { this.qty = Math.min((Number(this.qty) || 0) + 1, this.maxQty); },
                // Tombol − tidak menurunkan jumlah di bawah minimal yang berlaku.
                dec() {
                    const q = Number(this.qty) || 1;
                    if (q > this.minQty) this.qty = q - 1;
                },

                // ---- Peta ----
                mountMap() {
                    if (!window.L || !this.$refs.map) return;

                    // Peta sempat tersembunyi (mode ambil di tempat), hitung ulang ukurannya.
                    if (map) {
                        setTimeout(() => map.invalidateSize(), 50);
                        return;
                    }

                    const hasPin = this.lat !== null && this.lng !== null;
                    map = L.map(this.$refs.map).setView(
                        hasPin ? [this.lat, this.lng] : c.map.depot,
                        hasPin ? 16 : 14
                    );

                    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19,
                        attribution: '&copy; OpenStreetMap',
                    }).addTo(map);

                    // Penanda depot (lingkaran biru) supaya pelanggan tahu posisi relatif.
                    L.circleMarker(c.map.depot, {
                        radius: 8, color: '#0369a1', fillColor: '#0ea5e9', fillOpacity: 0.9,
                    }).addTo(map).bindTooltip('Depot', { permanent: false });

                    map.on('click', (e) => this.setPin(e.latlng.lat, e.latlng.lng, false));

                    if (hasPin) this.setPin(this.lat, this.lng, false);

                    setTimeout(() => map.invalidateSize(), 200);
                },

                setPin(lat, lng, zoomIn) {
                    this.lat = lat;
                    this.lng = lng;
                    this.geoMsg = '';

                    if (!marker) {
                        marker = L.marker([lat, lng], { draggable: true }).addTo(map);
                        marker.on('dragend', () => {
                            const p = marker.getLatLng();
                            this.lat = p.lat;
                            this.lng = p.lng;
                        });
                    } else {
                        marker.setLatLng([lat, lng]);
                    }

                    if (zoomIn) map.setView([lat, lng], Math.max(map.getZoom(), 16));
                },

                clearPin() {
                    if (marker) { map.removeLayer(marker); marker = null; }
                    this.lat = null;
                    this.lng = null;
                },

                useMyLocation() {
                    this.geoMsg = '';

                    if (!window.isSecureContext || !navigator.geolocation) {
                        this.geoMsg = 'Lokasi otomatis tidak tersedia di browser ini. Ketuk peta untuk menandai manual.';
                        return;
                    }

                    this.mountMap();
                    this.geoLoading = true;

                    navigator.geolocation.getCurrentPosition(
                        (pos) => {
                            this.geoLoading = false;
                            this.setPin(pos.coords.latitude, pos.coords.longitude, true);
                        },
                        (err) => {
                            this.geoLoading = false;
                            this.geoMsg = err.code === 1
                                ? 'Izin lokasi ditolak. Ketuk peta untuk menandai manual, atau izinkan lokasi di pengaturan browser.'
                                : 'Lokasi belum ketemu. Coba lagi, atau ketuk peta untuk menandai manual.';
                        },
                        { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 }
                    );
                },
            };
        }
    </script>
@endpush