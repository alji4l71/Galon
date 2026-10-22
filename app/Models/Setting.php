<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $primaryKey = 'key';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['key', 'value'];

    public const DEFAULTS = [
        'business_name' => 'Depot Air Isi Ulang',
        'admin_whatsapp' => '',
        'price_per_gallon' => '6000',
        'fee_per_km' => '1000',
        'distances' => '1,2,3,4',
        'max_qty' => '20',
        'qris_path' => '',
    ];

    private static ?array $cache = null;

    public static function values(): array
    {
        if (self::$cache === null) {
            $stored = static::query()->pluck('value', 'key')->all();
            self::$cache = array_merge(
                self::DEFAULTS,
                array_filter($stored, fn ($v) => $v !== null)
            );
        }

        return self::$cache;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return self::values()[$key] ?? $default;
    }

    public static function put(string $key, ?string $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
        self::$cache = null;
    }

    /** URL gambar QRIS atau null kalau belum diunggah. */
    public static function qrisUrl(): ?string
    {
        $path = (string) self::get('qris_path', '');

        return $path !== '' ? asset($path) : null;
    }
}
