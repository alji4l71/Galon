<?php

return [
    // Akun admin awal, dibuat oleh `php artisan db:seed`.
    // Isi ADMIN_EMAIL dan ADMIN_PASSWORD di file .env sebelum menjalankan seeder.
    'admin_name' => env('ADMIN_NAME', 'Admin'),
    'admin_email' => env('ADMIN_EMAIL'),
    'admin_password' => env('ADMIN_PASSWORD'),
];
