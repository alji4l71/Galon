<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PaymentController extends Controller
{
    /** Pilih metode bayar: QRIS (menampilkan gambar QRIS) atau Cash. */
    public function show(Order $order)
    {
        if ($order->payment_status === 'paid' || $order->order_status === 'dibatalkan') {
            return redirect()->route('order.done', $order);
        }

        return view('order.payment', [
            'order' => $order,
            'qrisUrl' => Setting::qrisUrl(),
        ]);
    }

    public function store(Request $request, Order $order)
    {
        if ($order->payment_status === 'paid' || $order->order_status === 'dibatalkan') {
            return redirect()->route('order.done', $order);
        }

        $data = $request->validate([
            'payment_method' => ['required', Rule::in(array_keys(Order::PAYMENT_METHODS))],
        ], [
            'payment_method.required' => 'Pilih metode pembayaran dulu.',
            'payment_method.in' => 'Metode pembayaran tidak valid.',
        ]);

        if ($data['payment_method'] === 'qris' && ! Setting::qrisUrl()) {
            return back()->withErrors(['payment_method' => 'QRIS belum tersedia, silakan pilih Cash.']);
        }

        // Status bayar tetap "pending" sampai admin memeriksa dan menandai lunas
        // (QRIS statis tidak bisa mendeteksi pembayaran otomatis).
        $order->update(['payment_method' => $data['payment_method']]);

        return redirect()->route('order.done', $order);
    }

    public function done(Order $order)
    {
        $adminWa = (string) Setting::get('admin_whatsapp', '');

        $waText = sprintf(
            'Halo, saya %s. Pesanan %s: %d galon, total %s, pembayaran %s.',
            $order->name,
            $order->order_code,
            $order->qty,
            $order->total_rp,
            $order->method_label,
        );

        return view('order.done', [
            'order' => $order,
            'waUrl' => $adminWa !== '' ? 'https://wa.me/' . $adminWa . '?text=' . rawurlencode($waText) : null,
        ]);
    }
}
