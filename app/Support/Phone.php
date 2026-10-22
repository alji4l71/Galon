<?php

namespace App\Support;

/**
 * Normalisasi nomor WhatsApp Indonesia ke format 628xxxxxxxxx
 * (format yang dipakai link wa.me).
 */
final class Phone
{
    public static function normalize(?string $value): string
    {
        $digits = preg_replace('/\D+/', '', (string) $value);

        if ($digits === '') {
            return '';
        }

        if (str_starts_with($digits, '0')) {
            return '62' . substr($digits, 1);
        }

        if (str_starts_with($digits, '8')) {
            return '62' . $digits;
        }

        return $digits;
    }

    /** Bentuk lokal untuk ditampilkan di form, misalnya 08123456789. */
    public static function local(?string $value): string
    {
        $normalized = self::normalize($value);

        return str_starts_with($normalized, '62') ? '0' . substr($normalized, 2) : $normalized;
    }
}
