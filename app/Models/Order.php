<?php

namespace App\Models;

use App\Services\Pricing;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    public const STATUSES = [
        'baru' => 'Baru',
        'diproses' => 'Diproses',
        'diantar' => 'Diantar',
        'selesai' => 'Selesai',
        'dibatalkan' => 'Dibatalkan',
    ];

    public const PAYMENT_STATUSES = [
        'pending' => 'Belum Lunas',
        'paid' => 'Lunas',
    ];

    public const PAYMENT_METHODS = [
        'qris' => 'QRIS',
        'cash' => 'Cash',
    ];

    public const DELIVERY = [
        'cod' => 'COD (diantar)',
        'pickup' => 'Ambil di tempat',
    ];

    protected $fillable = [
        'order_code', 'customer_id', 'name', 'whatsapp', 'address', 'latitude', 'longitude',
        'qty', 'price_per_gallon', 'delivery_method', 'distance_km', 'delivery_fee', 'total',
        'payment_method', 'payment_status', 'order_status',
    ];

    protected $casts = [
        'qty' => 'integer',
        'price_per_gallon' => 'integer',
        'distance_km' => 'integer',
        'delivery_fee' => 'integer',
        'total' => 'integer',
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    /** Link publik memakai kode pesanan, bukan id berurutan. */
    public function getRouteKeyName(): string
    {
        return 'order_code';
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /** Pesanan yang belum selesai atau dibatalkan. */
    public function isActive(): bool
    {
        return ! in_array($this->order_status, ['selesai', 'dibatalkan'], true);
    }

    /** Sudah dibuat tapi pelanggan belum memilih QRIS/Cash. */
    public function needsPaymentChoice(): bool
    {
        return $this->isActive()
            && $this->payment_status === 'pending'
            && empty($this->payment_method);
    }

    /** Kode unik, huruf yang mirip (0/O, 1/I) dihindari supaya mudah dibaca. */
    public static function generateCode(): string
    {
        $alphabet = str_split('ABCDEFGHJKLMNPQRSTUVWXYZ23456789');

        do {
            $code = 'GLN-';
            for ($i = 0; $i < 8; $i++) {
                $code .= $alphabet[random_int(0, count($alphabet) - 1)];
            }
        } while (static::where('order_code', $code)->exists());

        return $code;
    }

    public function getSubtotalAttribute(): int
    {
        return $this->qty * $this->price_per_gallon;
    }

    public function getSubtotalRpAttribute(): string
    {
        return Pricing::rp($this->subtotal);
    }

    public function getPriceRpAttribute(): string
    {
        return Pricing::rp($this->price_per_gallon);
    }

    public function getFeeRpAttribute(): string
    {
        return Pricing::rp($this->delivery_fee);
    }

    public function getTotalRpAttribute(): string
    {
        return Pricing::rp($this->total);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->order_status] ?? $this->order_status;
    }

    public function getPaymentLabelAttribute(): string
    {
        return self::PAYMENT_STATUSES[$this->payment_status] ?? $this->payment_status;
    }

    public function getMethodLabelAttribute(): string
    {
        return self::PAYMENT_METHODS[$this->payment_method] ?? 'Belum dipilih';
    }

    public function getDeliveryLabelAttribute(): string
    {
        return self::DELIVERY[$this->delivery_method] ?? $this->delivery_method;
    }

    public function getWaLinkAttribute(): string
    {
        return 'https://wa.me/' . $this->whatsapp;
    }

    /** Link Google Maps ke titik pelanggan, null kalau pelanggan tidak menandai lokasi. */
    public function getMapsUrlAttribute(): ?string
    {
        if ($this->latitude === null || $this->longitude === null) {
            return null;
        }

        return 'https://www.google.com/maps/search/?api=1&query=' . $this->latitude . ',' . $this->longitude;
    }
}