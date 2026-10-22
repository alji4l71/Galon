<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Order;
use App\Services\Pricing;
use App\Support\Phone;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    /** Jarak garis lurus (km) antara dua titik, rumus Haversine. */
    private static function distanceKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $r = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return $r * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    /** Halaman form yang dibuka lewat scan QR: /pesan/{token}. */
    public function create(string $token)
    {
        $customer = Customer::where('token', $token)->firstOrFail();
        $s = Pricing::settings();

        // Pin awal: input sebelumnya (kalau validasi gagal), kalau tidak ada pakai lokasi tersimpan.
        $pinLat = old('latitude', $customer->latitude);
        $pinLng = old('longitude', $customer->longitude);

        // Data untuk kalkulasi langsung di browser (Alpine.js).
        // Total resmi tetap dihitung ulang di server saat form dikirim.
        $config = [
            'price' => $s['price'],
            'perKm' => $s['fee_per_km'],
            'maxQty' => $s['max_qty'],
            'freeKm' => $s['free_km'],
            'minQtyFar' => min($s['min_qty_far'], $s['max_qty']),
            'maxKm' => $s['max_km'],
            'old' => [
                'qty' => old('qty') !== null ? (int) old('qty') : null,
                'method' => old('delivery_method'),
            ],
            'map' => [
                'depot' => [$s['depot_lat'], $s['depot_lng']],
                'lat' => is_numeric($pinLat) ? (float) $pinLat : null,
                'lng' => is_numeric($pinLng) ? (float) $pinLng : null,
            ],
        ];

        $orders = $customer->orders()->latest()->limit(5)->get();
        $activeOrder = $orders->first(fn ($o) => $o->isActive());

        return view('order.form', [
            'customer' => $customer,
            's' => $s,
            'config' => $config,
            'whatsappLocal' => Phone::local($customer->whatsapp),
            'orders' => $orders,
            'activeOrder' => $activeOrder,
        ]);
    }

    public function store(Request $request, string $token)
    {
        $customer = Customer::where('token', $token)->firstOrFail();
        $s = Pricing::settings();

        $request->merge(['whatsapp' => Phone::normalize($request->input('whatsapp'))]);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'whatsapp' => ['required', 'regex:/^628[0-9]{7,12}$/'],
            'address' => ['required_if:delivery_method,cod', 'nullable', 'string', 'max:255'],
            'latitude' => ['required_if:delivery_method,cod', 'nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['required_with:latitude', 'nullable', 'numeric', 'between:-180,180'],
            'qty' => ['required', 'integer', 'min:1', 'max:' . $s['max_qty']],
            'delivery_method' => ['required', Rule::in(array_keys(Order::DELIVERY))],
        ], [
            'required' => ':attribute wajib diisi.',
            'address.required_if' => 'Alamat wajib diisi untuk pesanan COD.',
            'latitude.required_if' => 'Tandai lokasi pengantaran di peta dulu supaya ongkir bisa dihitung.',
            'latitude.numeric' => 'Titik lokasi tidak valid.',
            'latitude.between' => 'Titik lokasi tidak valid.',
            'longitude.required_with' => 'Titik lokasi tidak valid.',
            'longitude.numeric' => 'Titik lokasi tidak valid.',
            'longitude.between' => 'Titik lokasi tidak valid.',
            'whatsapp.regex' => 'Nomor WhatsApp tidak valid. Contoh: 08123456789.',
            'qty.min' => 'Minimal 1 galon.',
            'qty.max' => 'Maksimal :max galon per pesanan.',
            'qty.integer' => 'Jumlah galon harus berupa angka.',
            'delivery_method.in' => 'Pilih COD atau ambil di tempat.',
        ], [
            'name' => 'Nama',
            'whatsapp' => 'Nomor WhatsApp',
            'address' => 'Alamat',
            'qty' => 'Jumlah galon',
            'delivery_method' => 'Cara pengambilan',
        ]);

        $isCod = $data['delivery_method'] === 'cod';
        $qty = (int) $data['qty'];
        $lat = $isCod ? round((float) $data['latitude'], 7) : null;
        $lng = $isCod ? round((float) $data['longitude'], 7) : null;

        // Jarak dihitung di server dari pin pelanggan ke depot, dibulatkan ke atas.
        $km = 0;
        if ($isCod) {
            $km = max(1, (int) ceil(self::distanceKm($s['depot_lat'], $s['depot_lng'], $lat, $lng)));

            if ($s['max_km'] !== null && $km > $s['max_km']) {
                throw ValidationException::withMessages([
                    'latitude' => "Lokasi Anda sekitar {$km} km dari depot, di luar jangkauan antar kami (maksimal {$s['max_km']} km). Silakan pilih ambil di tempat.",
                ]);
            }

            $minQty = Pricing::minQty($km, $s);
            if ($qty < $minQty) {
                throw ValidationException::withMessages([
                    'qty' => "Jarak Anda sekitar {$km} km. Untuk jarak di atas {$s['free_km']} km, minimal pesan {$minQty} galon.",
                ]);
            }
        }

        // Hitung ulang di server, angka dari browser tidak dipercaya.
        $calc = Pricing::compute(
            $qty,
            $data['delivery_method'],
            $km,
            $s['price'],
            $s['fee_per_km'],
        );

        $order = Order::create([
            'order_code' => Order::generateCode(),
            'customer_id' => $customer->id,
            'name' => $data['name'],
            'whatsapp' => $data['whatsapp'],
            'address' => $data['address'] ?? null,
            'latitude' => $lat,
            'longitude' => $lng,
            'qty' => $qty,
            'price_per_gallon' => $s['price'],
            'delivery_method' => $data['delivery_method'],
            'distance_km' => $calc['distance_km'],
            'delivery_fee' => $calc['delivery_fee'],
            'total' => $calc['total'],
            'payment_status' => 'pending',
            'order_status' => 'baru',
        ]);

        // Simpan lokasi ke profil pelanggan supaya pesanan berikutnya langsung terisi.
        if ($isCod) {
            $customer->update(['latitude' => $lat, 'longitude' => $lng]);
        }

        return redirect()->route('payment.show', $order);
    }
}