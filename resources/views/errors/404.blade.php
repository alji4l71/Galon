@extends('layouts.app')

@section('title', 'Halaman tidak ditemukan')

@section('content')
    <div class="rounded-2xl bg-white p-6 text-center shadow-sm">
        <p class="text-4xl">🔍</p>
        <h2 class="mt-3 text-lg font-semibold">Link tidak ditemukan</h2>
        <p class="mt-2 text-sm text-slate-600">
            QR code atau link ini tidak valid. Silakan hubungi kami untuk mendapatkan QR code yang baru.
        </p>
    </div>
@endsection
