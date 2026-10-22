<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        // Default: pesanan hari ini. ?date=all untuk semua tanggal.
        $date = $request->query('date', now()->toDateString());
        $status = (string) $request->query('status', '');
        $q = trim((string) $request->query('q', ''));

        $query = Order::query()
            ->when($date !== 'all' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date), fn ($qr) => $qr->whereDate('created_at', $date))
            ->when(array_key_exists($status, Order::STATUSES), fn ($qr) => $qr->where('order_status', $status))
            ->when($q !== '', fn ($qr) => $qr->where(function ($w) use ($q) {
                $w->where('name', 'like', "%{$q}%")
                    ->orWhere('whatsapp', 'like', "%{$q}%")
                    ->orWhere('order_code', 'like', "%{$q}%");
            }));

        // Ringkasan dari hasil filter, pesanan dibatalkan tidak dihitung.
        $active = (clone $query)->where('order_status', '!=', 'dibatalkan');
        $stats = [
            'orders' => (clone $active)->count(),
            'gallons' => (int) (clone $active)->sum('qty'),
            'paid' => (int) (clone $active)->where('payment_status', 'paid')->sum('total'),
            'unpaid' => (int) (clone $active)->where('payment_status', 'pending')->sum('total'),
        ];

        $orders = $query->latest()->paginate(20)->withQueryString();

        return view('admin.orders.index', [
            'orders' => $orders,
            'stats' => $stats,
            'date' => $date,
            'dateInput' => $date === 'all' ? '' : $date,
            'status' => $status,
            'q' => $q,
            'statuses' => Order::STATUSES,
            'paymentStatuses' => Order::PAYMENT_STATUSES,
        ]);
    }

    public function update(Request $request, Order $order)
    {
        $data = $request->validate([
            'order_status' => ['required', Rule::in(array_keys(Order::STATUSES))],
            'payment_status' => ['required', Rule::in(array_keys(Order::PAYMENT_STATUSES))],
        ]);

        $order->update($data);

        return back()->with('ok', "Pesanan {$order->order_code} diperbarui.");
    }

    public function destroy(Order $order)
    {
        $code = $order->order_code;
        $order->delete();

        return back()->with('ok', "Pesanan {$code} dihapus.");
    }
}
