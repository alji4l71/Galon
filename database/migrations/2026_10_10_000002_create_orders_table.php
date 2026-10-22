<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_code', 20)->unique();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();

            // Data pemesan disalin ke sini supaya riwayat tidak berubah
            // kalau data pelanggan diedit atau dihapus.
            $table->string('name');
            $table->string('whatsapp', 20);
            $table->text('address')->nullable();

            $table->unsignedSmallInteger('qty');
            $table->unsignedInteger('price_per_gallon');
            $table->string('delivery_method', 10);          // cod | pickup
            $table->unsignedTinyInteger('distance_km')->default(0);
            $table->unsignedInteger('delivery_fee')->default(0);
            $table->unsignedInteger('total');

            $table->string('payment_method', 10)->nullable(); // qris | cash
            $table->string('payment_status', 10)->default('pending'); // pending | paid
            $table->string('order_status', 12)->default('baru');

            $table->timestamps();

            $table->index('created_at');
            $table->index('order_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
