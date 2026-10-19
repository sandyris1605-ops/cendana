<?php

namespace Tests\Feature;

use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\User;
use Tests\TestCase;

/**
 * Hapus supplier hanya bila belum punya riwayat pembelian.
 * Tanpa RefreshDatabase (koneksi MySQL dev) — semua baris uji dibersihkan di tearDown.
 */
class SupplierDeleteTest extends TestCase
{
    private ?User $admin = null;
    private array $supplierIds = [];
    private array $purchaseIds = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::create([
            'name' => 'Tester Supplier Delete',
            'email' => 'supplier-delete-tester@example.test',
            'password' => 'secret-password',
            'role' => 'admin',
        ]);
    }

    protected function tearDown(): void
    {
        Purchase::whereIn('id', $this->purchaseIds)->delete();
        Supplier::whereIn('id', $this->supplierIds)->delete();
        User::where('email', 'supplier-delete-tester@example.test')->delete();
        parent::tearDown();
    }

    private function makeSupplier(string $nama): Supplier
    {
        $s = Supplier::create(['nama' => $nama, 'telepon' => '0812000000']);
        $this->supplierIds[] = $s->id;

        return $s;
    }

    private function beli(Supplier $s): Purchase
    {
        $p = Purchase::create([
            'nomor' => 'PO-TEST-SUP-' . uniqid(),
            'supplier_id' => $s->id,
            'tanggal' => now()->toDateString(),
            'total' => 100000,
            'status' => 'selesai',
            'sumber_dana' => 'kas',
        ]);
        $this->purchaseIds[] = $p->id;

        return $p;
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $s = $this->makeSupplier('Supplier Uji Guest');

        $this->delete(route('admin.suppliers.destroy', $s))->assertRedirect('/login');
        $this->assertDatabaseHas('suppliers', ['id' => $s->id]);
    }

    public function test_supplier_tanpa_riwayat_bisa_dihapus(): void
    {
        $s = $this->makeSupplier('Supplier Tanpa Riwayat');

        $this->actingAs($this->admin)
            ->delete(route('admin.suppliers.destroy', $s))
            ->assertRedirect(route('admin.suppliers.index'))
            ->assertSessionHas('ok');

        $this->assertDatabaseMissing('suppliers', ['id' => $s->id]);
    }

    public function test_supplier_dengan_riwayat_pembelian_tidak_bisa_dihapus(): void
    {
        $s = $this->makeSupplier('Supplier Berriwayat');
        $this->beli($s);

        $this->actingAs($this->admin)
            ->from(route('admin.suppliers.index'))
            ->delete(route('admin.suppliers.destroy', $s))
            ->assertSessionHasErrors('hapus');

        $this->assertDatabaseHas('suppliers', ['id' => $s->id]);
    }

    public function test_index_tidak_lagi_punya_tombol_hapus(): void
    {
        $bebas = $this->makeSupplier('Supplier Bebas Uji');
        $berkaitan = $this->makeSupplier('Supplier Terkunci Uji');
        $this->beli($berkaitan);

        // paginate(20) + orderBy nama: paksa nama terkecil agar selalu di halaman 1
        $bebas->update(['nama' => 'AAA-SUP-BEBAS']);
        $berkaitan->update(['nama' => 'BBB-SUP-TERKUNCI']);

        $res = $this->actingAs($this->admin)->get(route('admin.suppliers.index'));
        $res->assertOk();
        // Kolom Aksi dihapus: daftar tidak lagi memuat form hapus supplier manapun.
        $res->assertDontSee('action="' . route('admin.suppliers.destroy', $bebas) . '"', false);
        $res->assertDontSee('action="' . route('admin.suppliers.destroy', $berkaitan) . '"', false);
        // Klik nama tetap memicu modal edit.
        $res->assertSee('bukaEdit(' . $bebas->id . ')', false);
    }

    public function test_hapus_dari_halaman_detail_diarahkan_ke_index_bukan_404(): void
    {
        $s = $this->makeSupplier('Supplier Hapus Dari Detail');

        // Hapus DARI halaman detail -> controller mengarahkan ke index (bukan back()).
        $this->actingAs($this->admin)
            ->from(route('admin.suppliers.show', $s))
            ->delete(route('admin.suppliers.destroy', $s))
            ->assertRedirect(route('admin.suppliers.index'));

        $this->assertDatabaseMissing('suppliers', ['id' => $s->id]);
    }

    public function test_no_pajak_tersimpan_saat_tambah(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.suppliers.store'), [
                'nama' => 'Supplier Berpajak Baru',
                'telepon' => '0812000000',
                'no_pajak' => '01.234.567.8-901.000',
            ])
            ->assertRedirect(route('admin.suppliers.index'))
            ->assertSessionHas('ok');

        $this->assertDatabaseHas('suppliers', [
            'nama' => 'Supplier Berpajak Baru',
            'no_pajak' => '01.234.567.8-901.000',
        ]);
    }

    public function test_no_pajak_tersimpan_saat_update(): void
    {
        $s = $this->makeSupplier('Supplier Update Pajak');

        $this->actingAs($this->admin)
            ->put(route('admin.suppliers.update', $s), [
                'nama' => $s->nama,
                'no_pajak' => '09.876.543.2-109.000',
            ])
            ->assertRedirect(route('admin.suppliers.index'))
            ->assertSessionHas('ok');

        $this->assertDatabaseHas('suppliers', [
            'id' => $s->id,
            'no_pajak' => '09.876.543.2-109.000',
        ]);
    }

    public function test_index_pakai_modal_bukan_link_edit(): void
    {
        $s = $this->makeSupplier('Supplier Modal Uji');
        $s->update(['nama' => 'AAA-SUP-MODAL']);

        $res = $this->actingAs($this->admin)->get(route('admin.suppliers.index'));
        $res->assertOk();
        // Nama & tombol aksi memicu modal edit (bukan link ke halaman edit terpisah).
        $res->assertSee('bukaEdit(' . $s->id . ')', false);
        $res->assertSee('id="modalSupplier"', false);
        // Tidak lagi menautkan ke halaman edit terpisah pada baris daftar.
        $res->assertDontSee('href="' . route('admin.suppliers.edit', $s) . '"', false);
    }
}

