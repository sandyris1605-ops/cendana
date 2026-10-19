<?php

namespace Tests\Feature;

use App\Models\AssetUnit;
use App\Models\Item;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Room;
use App\Models\RoomAsset;
use App\Models\RoomType;
use App\Models\User;
use Tests\TestCase;

/**
 * Hapus PO hanya bila semua unit barangnya masih di gudang.
 * Unit yang sudah terpasang di kamar menghalangi penghapusan.
 * Tanpa RefreshDatabase (koneksi MySQL dev) — baris uji dibersihkan di tearDown.
 */
class PurchaseDeleteProtectionTest extends TestCase
{
    private ?User $admin = null;
    private array $itemIds = [];
    private array $purchaseIds = [];
    private array $roomIds = [];
    private array $roomTypeIds = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::create([
            'name' => 'Tester Purchase Delete',
            'email' => 'purchase-del-tester@example.test',
            'password' => 'secret-password',
            'role' => 'admin',
        ]);
    }

    protected function tearDown(): void
    {
        $unitIds = AssetUnit::whereIn('purchase_id', $this->purchaseIds)->pluck('id');
        RoomAsset::whereIn('asset_unit_id', $unitIds)->delete();
        AssetUnit::whereIn('id', $unitIds)->delete();
        PurchaseItem::whereIn('purchase_id', $this->purchaseIds)->delete();
        Purchase::whereIn('id', $this->purchaseIds)->delete();
        Item::whereIn('id', $this->itemIds)->delete();
        Room::whereIn('id', $this->roomIds)->delete();
        RoomType::whereIn('id', $this->roomTypeIds)->delete();
        User::where('email', 'purchase-del-tester@example.test')->delete();
        parent::tearDown();
    }

    private function makeItem(string $nama): Item
    {
        $it = Item::create(['nama' => $nama, 'harga_estimasi' => 100000, 'umur_pakai_bulan' => 12]);
        $this->itemIds[] = $it->id;

        return $it;
    }

    private function makeRoom(): Room
    {
        $type = RoomType::create(['nama' => 'Tipe Uji Hapus PO']);
        $this->roomTypeIds[] = $type->id;
        $room = Room::create(['room_type_id' => $type->id, 'nomor' => 'PD-' . uniqid(), 'status' => 'terisi']);
        $this->roomIds[] = $room->id;

        return $room;
    }

    /** Beli 1 unit (perusahaan — tidak menyentuh saldo kas) → return [purchase, unit]. */
    private function beliSatu(Item $it): array
    {
        $this->actingAs($this->admin)->post(route('admin.purchases.store'), [
            'tanggal' => now()->toDateString(),
            'items' => [['item_id' => $it->id, 'qty' => 1]],
            'sumber_dana' => 'perusahaan',
        ])->assertSessionHas('ok');

        $purchase = Purchase::orderByDesc('id')->first();
        $this->purchaseIds[] = $purchase->id;
        $unit = AssetUnit::where('purchase_id', $purchase->id)->firstOrFail();

        return [$purchase, $unit];
    }

    /** Pasang unit ke kamar (seperti flow Gudang/Aset). */
    private function pasang(AssetUnit $unit, Room $room): void
    {
        RoomAsset::create([
            'room_id' => $room->id,
            'asset_unit_id' => $unit->id,
            'tanggal_pasang' => now()->toDateString(),
            'status' => 'aktif',
        ]);
        $unit->update(['status' => 'terpasang']);
    }

    private function lepas(AssetUnit $unit): void
    {
        RoomAsset::where('asset_unit_id', $unit->id)->where('status', 'aktif')
            ->update(['status' => 'dilepas', 'tanggal_lepas' => now()->toDateString()]);
        $unit->update(['status' => 'gudang']);
    }

    public function test_hapus_po_semua_unit_masih_gudang_berhasil(): void
    {
        [$p, $unit] = $this->beliSatu($this->makeItem('Kasur Uji PO'));

        $this->actingAs($this->admin)->delete(route('admin.purchases.destroy', $p))
            ->assertSessionHas('ok');

        $this->assertDatabaseMissing('purchases', ['id' => $p->id]);
        $this->assertDatabaseMissing('asset_units', ['id' => $unit->id]);
    }

    public function test_hapus_po_ditolak_bila_ada_unit_terpasang_di_kamar(): void
    {
        [$p, $unit] = $this->beliSatu($this->makeItem('Kasur Uji PO Terpasang'));
        $kamar = $this->makeRoom();
        $this->pasang($unit, $kamar);

        $this->actingAs($this->admin)->delete(route('admin.purchases.destroy', $p))
            ->assertSessionHasErrors('kunci');

        // Semuanya masih utuh: PO, unit, dan penempatan di kamar.
        $this->assertDatabaseHas('purchases', ['id' => $p->id]);
        $this->assertDatabaseHas('asset_units', ['id' => $unit->id, 'status' => 'terpasang']);
        $this->assertSame(1, RoomAsset::where('asset_unit_id', $unit->id)->where('status', 'aktif')->count());
    }

    public function test_hapus_po_campuran_gudang_dan_terpasang_ditolak_total(): void
    {
        $it = $this->makeItem('Lemari Uji PO');
        [$p, $unit1] = $this->beliSatu($it);
        // Unit kedua dalam PO yang sama: beli lagi item sama → PO baru; gabung manual:
        [, $unit2] = $this->beliSatu($it);
        // Pindahkan unit2 ke PO pertama agar benar-benar satu PO (2 unit).
        $unit2->update(['purchase_id' => $p->id]);
        $kamar = $this->makeRoom();
        $this->pasang($unit2, $kamar);

        $this->actingAs($this->admin)->delete(route('admin.purchases.destroy', $p))
            ->assertSessionHasErrors('kunci');

        // Tidak ada satu pun yang terhapus — termasuk unit yang masih gudang.
        $this->assertDatabaseHas('purchases', ['id' => $p->id]);
        $this->assertDatabaseHas('asset_units', ['id' => $unit1->id]);
        $this->assertDatabaseHas('asset_units', ['id' => $unit2->id]);
    }

    public function test_setelah_dilepas_ke_gudang_po_bisa_dihapus(): void
    {
        [$p, $unit] = $this->beliSatu($this->makeItem('AC Uji PO'));
        $kamar = $this->makeRoom();
        $this->pasang($unit, $kamar);

        // Masih terpasang → ditolak.
        $this->actingAs($this->admin)->delete(route('admin.purchases.destroy', $p))
            ->assertSessionHasErrors('kunci');

        // Lepas ke gudang → kini boleh dihapus.
        $this->lepas($unit);
        $this->actingAs($this->admin)->delete(route('admin.purchases.destroy', $p))
            ->assertSessionHas('ok');

        $this->assertDatabaseMissing('purchases', ['id' => $p->id]);
        $this->assertDatabaseMissing('asset_units', ['id' => $unit->id]);
    }
}
