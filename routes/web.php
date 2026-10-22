<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Publik (pelanggan)
|--------------------------------------------------------------------------
*/
Route::view('/', 'home')->name('home');

Route::get('/pesan/{token}', [OrderController::class, 'create'])->name('order.create');
Route::post('/pesan/{token}', [OrderController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('order.store');

Route::get('/bayar/{order}', [PaymentController::class, 'show'])->name('payment.show');
Route::post('/bayar/{order}', [PaymentController::class, 'store'])
    ->middleware('throttle:20,1')
    ->name('payment.store');
Route::get('/selesai/{order}', [PaymentController::class, 'done'])->name('order.done');

/*
|--------------------------------------------------------------------------
| Admin
|--------------------------------------------------------------------------
| Route login harus bernama "login" supaya pengguna yang belum masuk
| otomatis diarahkan ke sini oleh middleware auth.
*/
Route::get('/admin/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/admin/login', [AuthController::class, 'login'])->middleware('throttle:6,1');
Route::post('/admin/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    Route::redirect('/', '/admin/pesanan');

    // Pesanan
    Route::get('pesanan', [AdminOrderController::class, 'index'])->name('orders.index');
    Route::patch('pesanan/{order}', [AdminOrderController::class, 'update'])->name('orders.update');
    Route::delete('pesanan/{order}', [AdminOrderController::class, 'destroy'])->name('orders.destroy');

    // Pelanggan + QR
    Route::get('pelanggan', [CustomerController::class, 'index'])->name('customers.index');
    Route::get('pelanggan/baru', [CustomerController::class, 'create'])->name('customers.create');
    Route::post('pelanggan', [CustomerController::class, 'store'])->name('customers.store');
    Route::get('pelanggan/cetak', [CustomerController::class, 'print'])->name('customers.print');
    Route::get('pelanggan/{customer}/edit', [CustomerController::class, 'edit'])->name('customers.edit');
    Route::put('pelanggan/{customer}', [CustomerController::class, 'update'])->name('customers.update');
    Route::delete('pelanggan/{customer}', [CustomerController::class, 'destroy'])->name('customers.destroy');
    Route::get('pelanggan/{customer}/qr', [CustomerController::class, 'qr'])->name('customers.qr');
    Route::post('pelanggan/{customer}/token', [CustomerController::class, 'regenerateToken'])->name('customers.token');

    // Laporan
    Route::get('laporan', [ReportController::class, 'index'])->name('reports.index');
    Route::get('laporan/ekspor', [ReportController::class, 'export'])->name('reports.export');

    // Pengaturan
    Route::get('pengaturan', [SettingController::class, 'edit'])->name('settings.edit');
    Route::put('pengaturan', [SettingController::class, 'update'])->name('settings.update');
});
