<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderCsv;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        [$month, $start, $end] = $this->period($request);

        $days = Order::query()
            ->where('order_status', '!=', 'dibatalkan')
            ->whereBetween('created_at', [$start, $end])
            ->select(
                DB::raw('DATE(created_at) as day'),
                DB::raw('COUNT(*) as orders'),
                DB::raw('SUM(qty) as gallons'),
                DB::raw('SUM(total) as revenue'),
                DB::raw("SUM(CASE WHEN payment_status = 'paid' THEN total ELSE 0 END) as paid")
            )
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('day')
            ->get();

        $totals = [
            'orders' => (int) $days->sum('orders'),
            'gallons' => (int) $days->sum('gallons'),
            'revenue' => (int) $days->sum('revenue'),
            'paid' => (int) $days->sum('paid'),
        ];
        $totals['unpaid'] = $totals['revenue'] - $totals['paid'];

        $cancelled = Order::where('order_status', 'dibatalkan')
            ->whereBetween('created_at', [$start, $end])
            ->count();

        return view('admin.reports.index', compact('month', 'days', 'totals', 'cancelled'));
    }

    public function export(Request $request)
    {
        [$month, $start, $end] = $this->period($request);

        $orders = Order::query()
            ->whereBetween('created_at', [$start, $end])
            ->orderBy('created_at');

        return response()->streamDownload(function () use ($orders) {
            $handle = fopen('php://output', 'w');
            OrderCsv::write($handle, $orders->cursor());
            fclose($handle);
        }, "pesanan-{$month}.csv", ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /** @return array{0:string,1:Carbon,2:Carbon} */
    private function period(Request $request): array
    {
        $input = (string) $request->query('month', now()->format('Y-m'));

        try {
            $start = Carbon::createFromFormat('!Y-m', $input)->startOfMonth();
        } catch (\Throwable) {
            $start = now()->startOfMonth();
        }

        return [$start->format('Y-m'), $start, $start->copy()->endOfMonth()];
    }
}
