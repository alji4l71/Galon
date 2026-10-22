<?php

namespace App\Services;

use App\Models\Setting;

final class Pricing
{
    /**
     * Hitung total pesanan. Fungsi murni (tanpa database) supaya mudah diuji.
     * Ongkir hanya berlaku untuk COD: jarak (km) x tarif per km.
     *
     * @return array{gallon_total:int, distance_km:int, delivery_fee:int, total:int}
     */
    public static function compute(int $qty, string $method, int $km, int $pricePerGallon, int $feePerKm): array
    {
        $qty = max(0, $qty);
        $km = $method === 'cod' ? max(0, $km) : 0;

        $gallonTotal = $qty * $pricePerGallon;
        $fee = $km * $feePerKm;

        return [
            'gallon_total' => $gallonTotal,
            'distance_km' => $km,
            'delivery_fee' => $fee,
            'total' => $gallonTotal + $fee,
        ];
    }

    /**
     * Minimal galon untuk jarak tertentu (km sudah dibulatkan ke atas).
     * Jarak di atas "free_km" wajib memesan minimal "min_qty_far" galon.
     */
    public static function minQty(int $km, array $s): int
    {
        if ($km > $s['free_km']) {
            return min($s['min_qty_far'], $s['max_qty']);
        }

        return 1;
    }

    /** Pengaturan yang sedang berlaku (dari tabel settings). */
    public static function settings(): array
    {
        $maxKm = (int) Setting::get('max_km', 0);

        return [
            'price' => (int) Setting::get('price_per_gallon', 6000),
            'fee_per_km' => (int) Setting::get('fee_per_km', 1000),
            'max_qty' => max(1, (int) Setting::get('max_qty', 20)),

            // Aturan jarak: di atas free_km, pesanan minimal min_qty_far galon.
            'free_km' => max(0, (int) Setting::get('free_km', 2)),
            'min_qty_far' => max(1, (int) Setting::get('min_qty_far', 5)),
            // null = tanpa batas jarak.
            'max_km' => $maxKm > 0 ? $maxKm : null,

            // Lokasi depot, titik awal penghitungan jarak.
            'depot_lat' => (float) Setting::get('depot_lat', -6.944283),
            'depot_lng' => (float) Setting::get('depot_lng', 113.569385),
        ];
    }

    public static function rp(int|float $amount): string
    {
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }
}