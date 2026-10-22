<?php

namespace App\Services;

use App\Models\Order;

final class OrderCsv
{
    public const HEADER = [
        'Kode', 'Tanggal', 'Nama', 'WhatsApp', 'Alamat', 'Jumlah Galon', 'Harga per Galon',
        'Pengambilan', 'Jarak (km)', 'Ongkir', 'Total', 'Metode Bayar', 'Status Bayar', 'Status Pesanan',
    ];

    /**
     * Tulis pesanan ke resource file CSV dan kembalikan jumlah baris data.
     *
     * @param  resource  $handle
     * @param  iterable<Order>  $orders
     */
    public static function write($handle, iterable $orders, bool $withBom = true): int
    {
        if ($withBom) {
            fwrite($handle, "\xEF\xBB\xBF"); // agar Excel membaca UTF-8 dengan benar
        }

        fputcsv($handle, self::HEADER);

        $count = 0;
        foreach ($orders as $order) {
            fputcsv($handle, [
                $order->order_code,
                $order->created_at?->format('Y-m-d H:i:s'),
                $order->name,
                $order->whatsapp,
                $order->address,
                $order->qty,
                $order->price_per_gallon,
                Order::DELIVERY[$order->delivery_method] ?? $order->delivery_method,
                $order->distance_km,
                $order->delivery_fee,
                $order->total,
                Order::PAYMENT_METHODS[$order->payment_method] ?? '',
                Order::PAYMENT_STATUSES[$order->payment_status] ?? $order->payment_status,
                Order::STATUSES[$order->order_status] ?? $order->order_status,
            ]);
            $count++;
        }

        return $count;
    }
}
