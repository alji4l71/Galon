<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\Pricing;
use App\Support\Phone;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function edit()
    {
        $values = Setting::values();
        $values['admin_whatsapp'] = Phone::local($values['admin_whatsapp'] ?? '');

        // Nilai aturan jarak dan depot diambil lengkap dengan nilai bawaan.
        $s = Pricing::settings();
        $values['free_km'] = $s['free_km'];
        $values['min_qty_far'] = $s['min_qty_far'];
        $values['max_km'] = $s['max_km'] ?? '';
        $values['depot_lat'] = $s['depot_lat'];
        $values['depot_lng'] = $s['depot_lng'];

        return view('admin.settings', [
            'v' => $values,
            'qrisUrl' => Setting::qrisUrl(),
        ]);
    }

    public function update(Request $request)
    {
        $request->merge(['admin_whatsapp' => Phone::normalize($request->input('admin_whatsapp')) ?: null]);

        $data = $request->validate([
            'business_name' => ['required', 'string', 'max:100'],
            'admin_whatsapp' => ['nullable', 'regex:/^628[0-9]{7,12}$/'],
            'price_per_gallon' => ['required', 'integer', 'min:0', 'max:10000000'],
            'fee_per_km' => ['required', 'integer', 'min:0', 'max:10000000'],
            'max_qty' => ['required', 'integer', 'min:1', 'max:500'],
            'free_km' => ['required', 'integer', 'min:0', 'max:1000'],
            'min_qty_far' => ['required', 'integer', 'min:1', 'lte:max_qty'],
            'max_km' => ['nullable', 'integer', 'min:1', 'max:10000', 'gte:free_km'],
            'depot_lat' => ['required', 'numeric', 'between:-90,90'],
            'depot_lng' => ['required', 'numeric', 'between:-180,180'],
            'qris' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'required' => ':attribute wajib diisi.',
            'integer' => ':attribute harus berupa angka bulat.',
            'numeric' => ':attribute harus berupa angka.',
            'admin_whatsapp.regex' => 'Nomor WhatsApp tidak valid. Contoh: 08123456789.',
            'min_qty_far.lte' => 'Minimal galon tidak boleh lebih besar dari batas galon per pesanan.',
            'max_km.gte' => 'Jarak maksimal tidak boleh lebih kecil dari jarak bebas syarat.',
            'depot_lat.between' => 'Latitude depot harus antara -90 dan 90.',
            'depot_lng.between' => 'Longitude depot harus antara -180 dan 180.',
            'qris.image' => 'File QRIS harus berupa gambar.',
            'qris.mimes' => 'Gambar QRIS harus berformat JPG, PNG, atau WEBP.',
            'qris.max' => 'Ukuran gambar QRIS maksimal 2 MB.',
        ], [
            'business_name' => 'Nama usaha',
            'price_per_gallon' => 'Harga per galon',
            'fee_per_km' => 'Tarif per km',
            'max_qty' => 'Batas galon',
            'free_km' => 'Jarak bebas syarat',
            'min_qty_far' => 'Minimal galon',
            'max_km' => 'Jarak maksimal',
            'depot_lat' => 'Latitude depot',
            'depot_lng' => 'Longitude depot',
        ]);

        Setting::put('business_name', $data['business_name']);
        Setting::put('admin_whatsapp', $data['admin_whatsapp'] ?? '');
        Setting::put('price_per_gallon', (string) $data['price_per_gallon']);
        Setting::put('fee_per_km', (string) $data['fee_per_km']);
        Setting::put('max_qty', (string) $data['max_qty']);
        Setting::put('free_km', (string) $data['free_km']);
        Setting::put('min_qty_far', (string) $data['min_qty_far']);
        Setting::put('max_km', (string) ($data['max_km'] ?? ''));
        Setting::put('depot_lat', (string) $data['depot_lat']);
        Setting::put('depot_lng', (string) $data['depot_lng']);

        if ($request->hasFile('qris')) {
            $file = $request->file('qris');
            $dir = public_path('uploads');

            if (! is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            $name = 'qris-' . time() . '.' . $file->extension();
            $file->move($dir, $name);

            $old = (string) Setting::get('qris_path', '');
            if ($old !== '' && is_file(public_path($old))) {
                @unlink(public_path($old));
            }

            Setting::put('qris_path', 'uploads/' . $name);
        }

        return redirect()->route('admin.settings.edit')->with('ok', 'Pengaturan disimpan.');
    }
}