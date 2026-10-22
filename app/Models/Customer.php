<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Customer extends Model
{
    protected $fillable = ['token', 'name', 'whatsapp', 'address', 'latitude', 'longitude'];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    protected static function booted(): void
    {
        static::creating(function (Customer $customer) {
            if (! $customer->token) {
                $customer->token = static::newToken();
            }
        });
    }

    /** Token acak 16 karakter untuk link QR, tidak bisa ditebak. */
    public static function newToken(): string
    {
        do {
            $token = Str::lower(Str::random(16));
        } while (static::where('token', $token)->exists());

        return $token;
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /** Alamat lengkap yang di-encode ke dalam QR. */
    public function getOrderUrlAttribute(): string
    {
        return route('order.create', $this->token);
    }
}