<?php

namespace Tests\Feature;

use App\Models\AssetUnit;
use App\Models\Item;
use App\Models\Room;
use App\Models\RoomAsset;
use App\Models\RoomType;
use App\Models\User;
use Tests\TestCase;

/**
 * Cetak barcode berdasarkan kamar — filter room_id di halaman cetak.
 * Tanpa RefreshDatabase (koneksi MySQL dev) — semua baris uji dibersihkan di tearDown.
 */
class BarcodeCetakPerKamarTest extends TestCase
{
    private ?User $admin = null;
    private array $roomIds = [];
    private array $roomTypeIds = [];
    private array $itemIds = [];
    private array $assetIds = [];
    private array $placementIds = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::create([
            'name' => 'Tester Barcode Kamar',
            'email' => 'barcode-kamar-tester@example.test',
            'password' => 'secret-password',
            'role' => 'admin',
        ]);
    }

    protected function tearDown(): void
    {
        RoomAsset::whereIn('id', $this->placementIds)->delete();
        AssetUnit::whereIn('id', $this->assetIds)->delete();
        Item::whereIn('id', $this->itemIds)->delete();
        Room::whereIn('id', $this->roomIds)->delete();
        RoomType::whereIn('id', $this->roomTypeIds)->delete();
        User::where('email', 'barcode-kamar-tester@example.test')->delete();
        parent::tearDown();
    }

    private function makeRoom(): Room
    {
        $type = RoomType::create(['nama' => 'Tipe Uji Barcode ' . uniqid()]);
        $this->roomTypeIds[] = $type->id;
        $room = Room::create(['room_type_id' => $type->id, 'nomor' => 'BC-' . uniqid(), 'status' => 'terisi']);
        $this->roomIds[] = $room->id;

        return $room;
    }

    /** Buat unit barang & pasang aktif ke sebuah kamar. */
    private function pasangBarang(Room $room, string $prefix): AssetUnit
    {
        $item = Item::create(['nama' => 'Barang Uji Barcode ' . $prefix . ' ' . uniqid(), 'satuan' => 'unit']);
        $this->itemIds[] = $item->id;
        $unit = AssetUnit::create([
            'item_id' => $item->id,
            'kode_barcode' => 'BCK-' . $prefix . '-' . uniqid(),
            'status' => 'terpasang',
            'kondisi' => 'baik',
        ]);
        $this->assetIds[] = $unit->id;
        $place = RoomAsset::create(['room_id' => $room->id, 'asset_unit_id' => $unit->id, 'status' => 'aktif']);
        $this->placementIds[] = $place->id;

        return $unit;
    }

    /** Buat unit barang di gudang (belum terpasang di kamar mana pun). */
    private function barangGudang(): AssetUnit
    {
        $item = Item::create(['nama' => 'Barang Gudang Uji Barcode ' . uniqid(), 'satuan' => 'unit']);
        $this->itemIds[] = $item->id;
        $unit = AssetUnit::create([
            'item_id' => $item->id,
            'kode_barcode' => 'BCK-GDG-' . uniqid(),
            'status' => 'gudang',
            'kondisi' => 'baik',
        ]);
        $this->assetIds[] = $unit->id;

        return $unit;
    }

    public function test_guest_diarahkan_ke_login(): void
    {
        $room = $this->makeRoom();

        $this->get(route('admin.barcode.cetak', ['room_id' => $room->id]))
            ->assertRedirect('/login');
    }

    public function test_cetak_per_kamar_hanya_barang_kamar_itu(): void
    {
        $kamarA = $this->makeRoom();
        $kamarB = $this->makeRoom();
        $unitA = $this->pasangBarang($kamarA, 'A');
        $this->pasangBarang($kamarA, 'A2');
        $unitB = $this->pasangBarang($kamarB, 'B');
        $this->barangGudang();

        $res = $this->actingAs($this->admin)
            ->get(route('admin.barcode.cetak', ['room_id' => $kamarA->id]));
        $res->assertOk();

        // Barcode kamar A tampil; kamar B & gudang tidak ikut.
        $res->assertSee($unitA->kode_barcode, false);
        $res->assertDontSee($unitB->kode_barcode, false);
        $res->assertSee('BARCODE KAMAR ' . strtoupper($kamarA->nomor), false);
        // Barcode harus berupa gambar PNG (bukan <div> berlatar) agar ikut tercetak.
        $res->assertSee('src="data:image/png;base64,', false);
    }

    public function test_cetak_tanpa_filter_tetap_berjalan(): void
    {
        $this->barangGudang();

        $res = $this->actingAs($this->admin)->get(route('admin.barcode.cetak'));
        $res->assertOk();
        // Tanpa room_id, judul generik (bukan "BARCODE KAMAR ...").
        $res->assertDontSee('BARCODE KAMAR ', false);
    }

    public function test_cetak_berdasarkan_ids_tetap_berjalan(): void
    {
        $unit = $this->barangGudang();

        $res = $this->actingAs($this->admin)
            ->get(route('admin.barcode.cetak', ['ids' => $unit->id]));
        $res->assertOk();
        $res->assertSee($unit->kode_barcode, false);
    }

    public function test_detail_kamar_punya_tombol_cetak_barcode(): void
    {
        $room = $this->makeRoom();
        $this->pasangBarang($room, 'D');

        $res = $this->actingAs($this->admin)->get(route('admin.rooms.show', $room));
        $res->assertOk();
        $res->assertSee('href="' . route('admin.barcode.cetak', ['room_id' => $room->id]) . '"', false);
    }
}
