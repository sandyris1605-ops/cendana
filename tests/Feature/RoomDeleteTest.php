<?php

namespace Tests\Feature;

use App\Models\AssetUnit;
use App\Models\Item;
use App\Models\Maintenance;
use App\Models\Room;
use App\Models\RoomAsset;
use App\Models\RoomType;
use App\Models\Tenancy;
use App\Models\Tenant;
use App\Models\User;
use Tests\TestCase;

/**
 * Hapus kamar hanya untuk status "tersedia" (tanpa penghuni aktif & tanpa barang terpasang).
 * Tanpa RefreshDatabase (koneksi MySQL dev) — semua baris uji dibersihkan di tearDown.
 */
class RoomDeleteTest extends TestCase
{
    private ?User $admin = null;
    private array $roomIds = [];
    private array $roomTypeIds = [];
    private array $tenantIds = [];
    private array $tenancyIds = [];
    private array $assetIds = [];
    private array $placementIds = [];
    private array $maintenanceIds = [];
    private array $itemIds = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::create([
            'name' => 'Tester Room Delete',
            'email' => 'room-delete-tester@example.test',
            'password' => 'secret-password',
            'role' => 'admin',
        ]);
    }

    protected function tearDown(): void
    {
        Maintenance::whereIn('id', $this->maintenanceIds)->delete();
        RoomAsset::whereIn('id', $this->placementIds)->delete();
        AssetUnit::whereIn('id', $this->assetIds)->delete();
        Item::whereIn('id', $this->itemIds)->delete();
        Tenancy::whereIn('id', $this->tenancyIds)->delete();
        Tenant::whereIn('id', $this->tenantIds)->delete();
        Room::whereIn('id', $this->roomIds)->delete();
        RoomType::whereIn('id', $this->roomTypeIds)->delete();
        User::where('email', 'room-delete-tester@example.test')->delete();
        parent::tearDown();
    }

    private function makeRoom(string $status = 'tersedia'): Room
    {
        $type = RoomType::create(['nama' => 'Tipe Uji Hapus Kamar ' . uniqid()]);
        $this->roomTypeIds[] = $type->id;
        $room = Room::create(['room_type_id' => $type->id, 'nomor' => 'RD-' . uniqid(), 'status' => $status]);
        $this->roomIds[] = $room->id;

        return $room->fresh();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $room = $this->makeRoom();

        $this->delete(route('admin.rooms.destroy', $room))->assertRedirect('/login');
        $this->assertDatabaseHas('rooms', ['id' => $room->id]);
    }

    public function test_kamar_tersedia_bisa_dihapus_beserta_riwayat(): void
    {
        $room = $this->makeRoom('tersedia');
        $tenant = Tenant::create(['nama' => 'Penghuni Lama RD']);
        $this->tenantIds[] = $tenant->id;
        $tenancy = Tenancy::create([
            'tenant_id' => $tenant->id, 'room_id' => $room->id,
            'tanggal_masuk' => now()->subMonths(3)->toDateString(),
            'tanggal_keluar' => now()->subMonth()->toDateString(), 'status' => 'selesai',
        ]);
        // riwayat lama tidak perlu dilacak manual — ikut terhapus via cascade
        $maint = Maintenance::create([
            'room_id' => $room->id, 'judul' => 'Servis Uji Hapus',
            'tanggal' => now()->toDateString(), 'status' => 'selesai',
        ]);
        $this->maintenanceIds[] = $maint->id;

        $this->actingAs($this->admin)
            ->delete(route('admin.rooms.destroy', $room))
            ->assertSessionHas('ok');

        $this->assertDatabaseMissing('rooms', ['id' => $room->id]);
        $this->assertDatabaseMissing('tenancies', ['id' => $tenancy->id]);
        // maintenance ikut NULL (nullOnDelete), bukan ikut terhapus
        $this->assertDatabaseHas('maintenances', ['id' => $maint->id, 'room_id' => null]);
        $this->roomIds = array_filter($this->roomIds, fn ($id) => $id !== $room->id);
    }

    public function test_kamar_terisi_dan_maintenance_ditolak(): void
    {
        foreach (['terisi', 'maintenance'] as $status) {
            $room = $this->makeRoom($status);

            $this->actingAs($this->admin)
                ->from(route('admin.rooms.index'))
                ->delete(route('admin.rooms.destroy', $room))
                ->assertSessionHasErrors('kunci');

            $this->assertDatabaseHas('rooms', ['id' => $room->id]);
        }
    }

    public function test_kamar_tersedia_tapi_masih_dihuni_ditolak(): void
    {
        $room = $this->makeRoom('tersedia');
        $tenant = Tenant::create(['nama' => 'Penghuni Aktif RD']);
        $this->tenantIds[] = $tenant->id;
        $tenancy = Tenancy::create([
            'tenant_id' => $tenant->id, 'room_id' => $room->id,
            'tanggal_masuk' => now()->toDateString(), 'status' => 'aktif',
        ]);
        $this->tenancyIds[] = $tenancy->id;

        $this->actingAs($this->admin)
            ->from(route('admin.rooms.index'))
            ->delete(route('admin.rooms.destroy', $room))
            ->assertSessionHasErrors('kunci');

        $this->assertDatabaseHas('rooms', ['id' => $room->id]);
    }

    public function test_kamar_tersedia_tapi_ada_barang_terpasang_ditolak(): void
    {
        $room = $this->makeRoom('tersedia');
        $item = Item::create(['nama' => 'Barang Uji Hapus Kamar ' . uniqid(), 'satuan' => 'unit']);
        $this->itemIds[] = $item->id;
        $unit = AssetUnit::create([
            'item_id' => $item->id, 'kode_barcode' => 'RD-' . uniqid(),
            'status' => 'terpasang', 'kondisi' => 'baik',
        ]);
        $this->assetIds[] = $unit->id;
        $place = RoomAsset::create(['room_id' => $room->id, 'asset_unit_id' => $unit->id, 'status' => 'aktif']);
        $this->placementIds[] = $place->id;

        $this->actingAs($this->admin)
            ->from(route('admin.rooms.index'))
            ->delete(route('admin.rooms.destroy', $room))
            ->assertSessionHasErrors('kunci');

        $this->assertDatabaseHas('rooms', ['id' => $room->id]);
        $this->assertDatabaseHas('room_assets', ['id' => $place->id]);
    }

    public function test_index_tombol_hapus_hanya_untuk_tersedia(): void
    {
        $bebas = $this->makeRoom('tersedia');
        $isi = $this->makeRoom('terisi');
        $servis = $this->makeRoom('maintenance');

        // paginate(20) + orderBy nomor: paksa nomor terkecil agar selalu di halaman 1
        $bebas->update(['nomor' => 'AA-RD-BEBAS']);
        $isi->update(['nomor' => 'AA-RD-ISI']);
        $servis->update(['nomor' => 'AA-RD-SERVIS']);

        $res = $this->actingAs($this->admin)->get(route('admin.rooms.index'));
        $res->assertOk();
        $res->assertSee('action="' . route('admin.rooms.destroy', $bebas) . '"', false);
        $res->assertDontSee('action="' . route('admin.rooms.destroy', $isi) . '"', false);
        $res->assertDontSee('action="' . route('admin.rooms.destroy', $servis) . '"', false);
        // nomor kamar + ikon adalah link ke halaman detail
        $res->assertSee('href="' . route('admin.rooms.show', $bebas) . '"', false);
        $res->assertSee('fa-bed', false);
    }

    public function test_form_tolak_status_booking(): void
    {
        $room = $this->makeRoom('tersedia');

        $this->actingAs($this->admin)
            ->from(route('admin.rooms.edit', $room))
            ->put(route('admin.rooms.update', $room), [
                'nomor' => $room->nomor, 'room_type_id' => $room->room_type_id, 'status' => 'booking',
            ])
            ->assertSessionHasErrors('status');

        $this->assertDatabaseHas('rooms', ['id' => $room->id, 'status' => 'tersedia']);
    }

    public function test_halaman_detail_tampil_modern(): void
    {
        $room = $this->makeRoom('terisi');
        $tenant = Tenant::create(['nama' => 'Penghuni Detail Modern']);
        $this->tenantIds[] = $tenant->id;
        $tenancy = Tenancy::create([
            'tenant_id' => $tenant->id, 'room_id' => $room->id,
            'tanggal_masuk' => now()->subMonth()->toDateString(), 'status' => 'aktif',
        ]);
        $this->tenancyIds[] = $tenancy->id;

        $res = $this->actingAs($this->admin)->get(route('admin.rooms.show', $room));
        $res->assertOk();
        $res->assertSee('Kamar ' . $room->nomor, false);
        $res->assertSee('Penghuni Detail Modern', false);
        $res->assertSee('Daftar Kamar', false);
        $res->assertSee('Barang Aktif', false);
        $res->assertSee('Riwayat Hunian', false);
    }
}
