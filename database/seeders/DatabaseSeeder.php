<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Pengaturan awal (tidak menimpa nilai yang sudah ada).
        foreach (Setting::DEFAULTS as $key => $value) {
            Setting::firstOrCreate(['key' => $key], ['value' => $value]);
        }

        $email = config('galon.admin_email');
        $password = config('galon.admin_password');

        if (! $email || ! $password) {
            $this->command?->error('Isi ADMIN_EMAIL dan ADMIN_PASSWORD di file .env, lalu jalankan seeder lagi.');

            return;
        }

        User::updateOrCreate(
            ['email' => $email],
            ['name' => config('galon.admin_name'), 'password' => Hash::make($password)]
        );

        $this->command?->info("Akun admin siap: {$email}");
    }
}
