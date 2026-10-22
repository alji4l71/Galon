<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Support\Phone;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $customers = Customer::query()
            ->withCount('orders')
            ->when($q !== '', fn ($qr) => $qr->where(function ($w) use ($q) {
                $w->where('name', 'like', "%{$q}%")
                    ->orWhere('whatsapp', 'like', "%{$q}%")
                    ->orWhere('address', 'like', "%{$q}%");
            }))
            ->orderBy('name')
            ->paginate(30)
            ->withQueryString();

        return view('admin.customers.index', compact('customers', 'q'));
    }

    public function create()
    {
        return view('admin.customers.form', ['customer' => new Customer()]);
    }

    public function store(Request $request)
    {
        $customer = Customer::create($this->validated($request));

        return redirect()
            ->route('admin.customers.qr', $customer)
            ->with('ok', 'Pelanggan ditambahkan. QR code-nya sudah siap dicetak.');
    }

    public function edit(Customer $customer)
    {
        return view('admin.customers.form', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        $customer->update($this->validated($request));

        return redirect()->route('admin.customers.index')->with('ok', 'Data pelanggan diperbarui.');
    }

    public function destroy(Customer $customer)
    {
        // Riwayat pesanan tetap ada karena data pemesan disalin ke tabel orders.
        $customer->delete();

        return redirect()->route('admin.customers.index')->with('ok', 'Pelanggan dihapus.');
    }

    /** Satu QR besar untuk satu pelanggan. */
    public function qr(Customer $customer)
    {
        return view('admin.customers.qr', compact('customer'));
    }

    /** Halaman cetak banyak QR sekaligus. Tanpa ?ids[] berarti semua pelanggan. */
    public function print(Request $request)
    {
        $ids = array_filter((array) $request->query('ids', []), 'is_numeric');

        $customers = Customer::query()
            ->when($ids, fn ($qr) => $qr->whereIn('id', $ids))
            ->orderBy('name')
            ->get();

        return view('admin.customers.print', compact('customers'));
    }

    /** Buat token baru, misalnya kalau QR lama bocor. QR lama langsung tidak berlaku. */
    public function regenerateToken(Customer $customer)
    {
        $customer->update(['token' => Customer::newToken()]);

        return redirect()
            ->route('admin.customers.qr', $customer)
            ->with('ok', 'Token baru dibuat. QR yang lama sudah tidak berlaku, cetak ulang QR ini.');
    }

    private function validated(Request $request): array
    {
        $request->merge(['whatsapp' => Phone::normalize($request->input('whatsapp')) ?: null]);

        return $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'whatsapp' => ['nullable', 'regex:/^628[0-9]{7,12}$/'],
            'address' => ['nullable', 'string', 'max:255'],
        ], [
            'name.required' => 'Nama wajib diisi.',
            'whatsapp.regex' => 'Nomor WhatsApp tidak valid. Contoh: 08123456789.',
        ]);
    }
}
