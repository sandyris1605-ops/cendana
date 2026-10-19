<?php

namespace Tests\Feature;

use App\Models\AssetUnit;
use App\Models\Depreciation;
use App\Models\Item;
use App\Models\User;
use App\Services\PenyusutanService;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * Hitung otomatis penyusutan (service + command + rapel periode tertinggal).
 * Tanpa RefreshDatabase (koneksi MySQL dev) — semua baris uji dibersihkan di tearDown.
 */
class PenyusutanOtomatisTest extends TestCase
{
    private ?User $admin = null;
    private array $unitIds = [];
    private array $itemIds = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::create([
            'name' => 'Tester Susut Otomatis',
            'email' => 'susut-otomatis-tester@example.test',
            'password' => 'secret-password',
            'role' => 'admin',
        ]);
    }

    protected function tearDown(): void
    {
        Depreciation::whereIn('asset_unit_id', $this->unitIds)->delete();
        AssetUnit::whereIn('id', $this->unitIds)->delete();
        Item::whereIn('id', $this->itemIds)->delete();
        User::where('email', 'susut-otomatis-tester@example.test')->delete();
        parent::tearDown();
    }

    private function makeUnit(int $harga = 1500000, int $residu = 0, int $umur = 48, ?string $tglBeli = null): AssetUnit
    {
        $item = Item::create(['nama' => 'Barang Susut Uji ' . uniqid(), 'umur_pakai_bulan' => $umur]);
        $this->itemIds[] = $item->id;
        $u = AssetUnit::create([
            'kode_barcode' => 'SUSUT-' . uniqid(), 'item_id' => $item->id,
            'tanggal_beli' => $tglBeli ?? now()->subMonths(2)->toDateString(),
            'harga_perolehan' => $harga, 'nilai_residu' => $residu, 'nilai_buku' => $harga,
            'kondisi' => 'baik', 'status' => 'gudang',
        ]);
        $this->unitIds[] = $u->id;

        return $u->fresh();
    }

    public function test_susut_bulanan_sesuai_rumus(): void
    {
        // Kasur 1.500.000 / 48 bln = 31.250/bln
        $u = $this->makeUnit(1500000, 0, 48);
        $this->assertSame(31250, app(PenyusutanService::class)->susutBulanan($u));
    }

    public function test_rapel_tiga_periode_tertunda(): void
    {
        $u = $this->makeUnit(1200000, 0, 60, now()->subMonths(5)->toDateString());
        $susut = 20000; // 1.200.000 / 60

        $hasil = app(PenyusutanService::class)
            ->hitungTertinggal(now()->subMonths(3)->format('Y-m'));

        $this->assertGreaterThanOrEqual(3, count($hasil['periode']));
        $this->assertSame((float) $u->harga_perolehan - 3 * $susut, (float) $u->fresh()->nilai_buku);
        $this->assertSame(3, Depreciation::where('asset_unit_id', $u->id)->count());
    }

    public function test_idempotent_dua_kali_jalan(): void
    {
        $u = $this->makeUnit();
        $layanan = app(PenyusutanService::class);
        $layanan->hitungTertinggal();
        $buku1 = (float) $u->fresh()->nilai_buku;
        $layanan->hitungTertinggal();

        $this->assertSame($buku1, (float) $u->fresh()->nilai_buku);
        $this->assertSame(1, Depreciation::where('asset_unit_id', $u->id)
            ->where('periode', now()->format('Y-m'))->count());
    }

    public function test_tidak_turun_di_bawah_residu(): void
    {
        $u = $this->makeUnit(100000, 40000, 12, now()->subMonths(20)->toDateString());
        app(PenyusutanService::class)->hitungTertinggal();

        $this->assertSame(40000.0, (float) $u->fresh()->nilai_buku);
    }

    public function test_unit_dibuang_dilewati(): void
    {
        $u = $this->makeUnit();
        $u->update(['status' => 'dibuang']);
        app(PenyusutanService::class)->hitungTertinggal();

        $this->assertSame(0, Depreciation::where('asset_unit_id', $u->id)->count());
    }

    public function test_command_berjalan(): void
    {
        $this->makeUnit();
        $this->artisan('aset:hitung-penyusutan')->assertSuccessful();
    }

    public function test_halaman_memuat_simulasi(): void
    {
        $res = $this->actingAs($this->admin)->get(route('admin.penyusutan'));
        $res->assertOk();
        $res->assertSee('Simulasi Penyusutan', false);
        $res->assertSee('simCari', false);
        $res->assertSee('simHasil', false);
        $res->assertSee('simTabel', false);
        $res->assertSee('simOutBuku', false);
        $res->assertSee('sim-baris', false);
    }
}
