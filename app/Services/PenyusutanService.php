<?php

namespace App\Services;

use App\Models\AssetUnit;
use App\Models\Depreciation;
use Illuminate\Support\Carbon;

/**
 * Hitung penyusutan garis lurus per bulan — dipakai tombol manual
 * (AssetController) maupun scheduler (command aset:hitung-penyusutan).
 *
 * Rumus per unit per bulan:
 *   susut = round((harga_perolehan - nilai_residu) / umur_pakai_bulan)
 *   nilai_buku_baru = max(nilai_residu, nilai_buku - susut)
 */
class PenyusutanService
{
    public function susutBulanan(AssetUnit $unit): int
    {
        $umur = max(1, (int) optional($unit->item)->umur_pakai_bulan);
        return (int) round(((float) $unit->harga_perolehan - (float) $unit->nilai_residu) / $umur);
    }

    /** Hitung 1 periode Y-m untuk 1 unit (idempotent). Return nilai buku baru. */
    public function hitungUnitPeriode(AssetUnit $unit, string $periode): float
    {
        $susut = $this->susutBulanan($unit);
        $baru = max((float) $unit->nilai_residu, (float) $unit->nilai_buku - $susut);
        Depreciation::updateOrCreate(
            ['asset_unit_id' => $unit->id, 'periode' => $periode],
            ['nilai_penyusutan' => $susut, 'nilai_buku_akhir' => $baru]
        );
        $unit->update(['nilai_buku' => $baru]);

        return $baru;
    }

    /**
     * Hitung semua periode yang tertinggal (rapel) s/d $sampai (default bulan berjalan).
     * Unit baru mulai dari bulan tanggal_beli. Return ['unit' => n, 'periode' => [list]].
     */
    public function hitungTertinggal(?string $sampai = null): array
    {
        $sampai = $sampai ?? Carbon::now()->format('Y-m');
        $unitTerhitung = 0;
        $periodeTerhitung = [];

        foreach (AssetUnit::with('item')->where('status', '!=', 'dibuang')->get() as $u) {
            $mulai = $u->tanggal_beli
                ? Carbon::parse($u->tanggal_beli)->format('Y-m')
                : Carbon::parse($u->created_at)->format('Y-m');
            $periodeTerakhir = Depreciation::where('asset_unit_id', $u->id)->max('periode');
            $cursor = $periodeTerakhir
                ? Carbon::createFromFormat('Y-m', $periodeTerakhir)->addMonth()->format('Y-m')
                : $mulai;
            $dihitung = false;
            while ($cursor <= $sampai) {
                // Jangan susutkan bulan sebelum unit dibeli.
                if ($cursor >= $mulai) {
                    $this->hitungUnitPeriode($u->fresh(), $cursor);
                    $periodeTerhitung[$cursor] = true;
                    $dihitung = true;
                }
                $cursor = Carbon::createFromFormat('Y-m', $cursor)->addMonth()->format('Y-m');
            }
            if ($dihitung) $unitTerhitung++;
        }

        $daftar = array_keys($periodeTerhitung);
        sort($daftar);

        return ['unit' => $unitTerhitung, 'periode' => $daftar];
    }
}
