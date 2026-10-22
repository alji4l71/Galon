<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Services\OrderCsv;
use Illuminate\Console\Command;

class ArchiveOrders extends Command
{
    protected $signature = 'orders:archive-reset
                            {--year= : Arsipkan satu tahun tertentu. Kosong = semua pesanan sebelum tahun berjalan}
                            {--dry-run : Hanya tampilkan yang akan diarsipkan, tanpa menulis atau menghapus}';

    protected $description = 'Ekspor pesanan lama ke CSV (storage/app/arsip) lalu hapus dari database. Data pelanggan dan QR tidak disentuh.';

    public function handle(): int
    {
        $query = Order::query();

        if ($this->option('year')) {
            $year = (int) $this->option('year');
            $query->whereYear('created_at', $year);
            $label = (string) $year;
        } else {
            $cutoff = now()->startOfYear();
            $query->where('created_at', '<', $cutoff);
            $label = 'sebelum-' . $cutoff->year;
        }

        $count = (clone $query)->count();

        if ($count === 0) {
            $this->info('Tidak ada pesanan yang perlu diarsipkan.');

            return self::SUCCESS;
        }

        $dir = storage_path('app/arsip');
        $file = "{$dir}/orders-{$label}.csv";

        // Jangan menimpa arsip yang sudah ada.
        if (is_file($file)) {
            $file = "{$dir}/orders-{$label}-" . now()->format('Ymd-His') . '.csv';
        }

        if ($this->option('dry-run')) {
            $this->info("[dry-run] {$count} pesanan akan diarsipkan ke {$file} lalu dihapus.");

            return self::SUCCESS;
        }

        if (! is_dir($dir) && ! mkdir($dir, 0755, true) && ! is_dir($dir)) {
            $this->error("Folder arsip tidak bisa dibuat: {$dir}");

            return self::FAILURE;
        }

        $handle = fopen($file, 'w');
        $written = OrderCsv::write($handle, (clone $query)->orderBy('id')->cursor());
        fclose($handle);

        // Hapus hanya kalau jumlah baris di arsip sama dengan jumlah di database.
        if ($written !== $count) {
            $this->error("Arsip tidak lengkap ({$written} dari {$count} baris). Data TIDAK dihapus.");

            return self::FAILURE;
        }

        $deleted = (clone $query)->delete();

        $this->info("{$deleted} pesanan diarsipkan ke {$file} dan dihapus dari database.");
        $this->warn('Jangan lupa unduh file arsip itu dan simpan di tempat yang aman.');

        return self::SUCCESS;
    }
}
