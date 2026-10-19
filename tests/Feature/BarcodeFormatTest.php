<?php

namespace Tests\Feature;

use App\Models\AssetUnit;
use App\Models\Item;
use App\Models\Purchase;
use App\Models\User;
use Tests\TestCase;

/**
 * Format barcode baru: CND-{KataNamaBarang}-{Tahun}-{urutan 4 digit} per barang.
 * Contoh: CND-Kasur-2026-0001, CND-Kasur-2026-0002, CND-Lemari-2026-0001.
 * Tanpa RefreshDatabase (koneksi MySQL dev) — baris uji dibersihkan di tearDown.
 */
class BarcodeFormatTest extends TestCase
{
    private ?User $admin = null;
    private array $itemIds = [];
    private array $purchaseIds = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::create([
            'name' => 'Tester Barcode Format',
            'email' => 'barcode-format-tester@example.test',
            'password' => 'secret-password',
            'role' => 'admin',
        ]);
    }

    protected function tearDown(): void
    {
        \App\Models\PurchaseItem::whereIn('purchase_id', $this->purchaseIds)->delete();
        AssetUnit::whereIn('purchase_id', $this->purchaseIds)->orWhereIn('item_id', $this->itemIds)->delete();
        Purchase::whereIn('id', $this->purchaseIds)->delete();
        Item::whereIn('id', $this->itemIds)->delete();
        User::where('email', 'barcode-format-tester@example.test')->delete();
        parent::tearDown();
    }

    private function makeItem(string $nama): Item
    {
        $it = Item::create(['nama' => $nama, 'harga_estimasi' => 100000, 'umur_pakai_bulan' => 12]);
        $this->itemIds[] = $it->id;

        return $it;
    }

    /** Beli item (sumber dana perusahaan — tidak menyentuh saldo kas). */
    private function beli(array $rows): void
    {
        $items = [];
        foreach ($rows as [$item, $qty]) {
            $items[] = ['item_id' => $item->id, 'qty' => $qty];
        }
        $res = $this->actingAs($this->admin)->post(route('admin.purchases.store'), [
            'tanggal' => now()->toDateString(),
            'items' => $items,
            'sumber_dana' => 'perusahaan',
        ]);
        $res->assertSessionHas('ok');
        $this->purchaseIds[] = (int) Purchase::orderByDesc('id')->value('id');
    }

    private function kodeBaru(): array
    {
        return AssetUnit::whereIn('purchase_id', $this->purchaseIds)
            ->orderBy('id')->pluck('kode_barcode')->all();
    }

    public function test_format_barcode_kata_pertama_kapital_dan_urut_per_barang(): void
    {
        // Nama huruf kecil + multi kata → kata pertama dikapitalkan: "kasur link" → Kasur.
        $kasur = $this->makeItem('kasur link');
        $this->beli([[$kasur, 2]]);
        $thn = date('Y');

        $this->assertSame(
            ['CND-Kasur-' . $thn . '-0001', 'CND-Kasur-' . $thn . '-0002'],
            $this->kodeBaru()
        );
    }

    public function test_barang_lain_punya_urutan_sendiri(): void
    {
        $lemari = $this->makeItem('Lemari 2 Pintu');
        $this->beli([[$lemari, 1]]);

        $this->assertSame(['CND-Lemari-' . date('Y') . '-0001'], $this->kodeBaru());
    }

    public function test_urutan_lanjut_pada_pembelian_berikutnya(): void
    {
        $kasur = $this->makeItem('Kasur 120x200');
        $this->beli([[$kasur, 2]]);
        $this->beli([[$kasur, 1]]);
        $thn = date('Y');

        $this->assertSame(
            ['CND-Kasur-' . $thn . '-0001', 'CND-Kasur-' . $thn . '-0002', 'CND-Kasur-' . $thn . '-0003'],
            $this->kodeBaru()
        );
    }

    public function test_normalisasi_kata_nama_barang(): void
    {
        $this->assertSame('Kasur', AssetUnit::kataBarcode('kasur link'));
        $this->assertSame('Kasur', AssetUnit::kataBarcode('Kasur 120x200 + Dipan'));
        $this->assertSame('TV', AssetUnit::kataBarcode('TV LED 32 inci'));
        $this->assertSame('Meja', AssetUnit::kataBarcode('  meja + kursi belajar  '));
        $this->assertSame('BRG', AssetUnit::kataBarcode('!!!'));
        $this->assertSame('BRG', AssetUnit::kataBarcode(''));
        $this->assertSame('120x200', AssetUnit::kataBarcode('120x200 dipan'));
    }

    public function test_kode_barcode_lama_tidak_berubah_dan_tidak_mengganggu_urutan(): void
    {
        // Simulasi unit lama (format sebelumnya) — label tercetak tetap valid.
        $it = $this->makeItem('Kasur Baru Uji');
        $unitLama = AssetUnit::create([
            'kode_barcode' => 'CND-' . date('Y') . '-9999',
            'item_id' => $it->id,
            'tanggal_beli' => now()->toDateString(),
            'harga_perolehan' => 100000,
            'nilai_buku' => 100000,
        ]);

        $this->beli([[$it, 1]]);

        // Kode lama tidak disentuh...
        $this->assertDatabaseHas('asset_units', ['id' => $unitLama->id, 'kode_barcode' => 'CND-' . date('Y') . '-9999']);
        // ...dan unit baru tetap mulai urut sendiri (prefix kata berbeda dari format lama).
        $this->assertSame(['CND-Kasur-' . date('Y') . '-0001'], $this->kodeBaru());
        $unitLama->delete();
    }
}
