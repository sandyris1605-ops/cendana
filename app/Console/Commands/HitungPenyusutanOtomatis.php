<?php

namespace App\Console\Commands;

use App\Services\PenyusutanService;
use Illuminate\Console\Command;

/**
 * Hitung otomatis penyusutan tiap awal bulan (termasuk rapel
 * periode yang terlewat saat server mati). Dijadwalkan di Kernel.
 */
class HitungPenyusutanOtomatis extends Command
{
    protected $signature = 'aset:hitung-penyusutan {--sampai= : Periode Y-m terakhir (default bulan berjalan)}';
    protected $description = 'Hitung penyusutan garis lurus semua unit (rapel periode tertinggal)';

    public function handle(PenyusutanService $layanan): int
    {
        $hasil = $layanan->hitungTertinggal($this->option('sampai'));
        $daftar = $hasil['periode'] ? implode(', ', $hasil['periode']) : 'tidak ada periode baru';
        $this->info("Penyusutan dihitung untuk {$hasil['unit']} unit — periode: {$daftar}.");

        return self::SUCCESS;
    }
}
