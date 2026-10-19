<?php

namespace Tests\Feature;

use App\Models\Room;
use App\Models\RoomType;
use App\Models\Tenancy;
use App\Models\Tenant;
use App\Models\User;
use Tests\TestCase;

/**
 * Hapus penghuni hanya untuk status "Tidak menghuni" (tanpa tenancy aktif).
 * Tanpa RefreshDatabase (koneksi MySQL dev) — semua baris uji dibersihkan di tearDown.
 */
class TenantDeleteTest extends TestCase
{
    private ?User $admin = null;
    private array $roomIds = [];
    private array $roomTypeIds = [];
    private array $tenantIds = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::create([
            'name' => 'Tester Tenant Delete',
            'email' => 'tenant-delete-tester@example.test',
            'password' => 'secret-password',
            'role' => 'admin',
        ]);
    }

    protected function tearDown(): void
    {
        Tenancy::whereIn('tenant_id', $this->tenantIds)->delete();
        Tenant::whereIn('id', $this->tenantIds)->delete();
        Room::whereIn('id', $this->roomIds)->delete();
        RoomType::whereIn('id', $this->roomTypeIds)->delete();
        User::where('email', 'tenant-delete-tester@example.test')->delete();
        parent::tearDown();
    }

    private function makeRoom(): Room
    {
        $type = RoomType::create(['nama' => 'Tipe Uji Hapus Penghuni']);
        $this->roomTypeIds[] = $type->id;
        $room = Room::create(['room_type_id' => $type->id, 'nomor' => 'TD-' . uniqid(), 'status' => 'tersedia']);
        $this->roomIds[] = $room->id;

        return $room;
    }

    private function makeTenant(string $nama): Tenant
    {
        $t = Tenant::create(['nama' => $nama, 'telepon' => '0812000000']);
        $this->tenantIds[] = $t->id;

        return $t;
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $t = $this->makeTenant('Penghuni Uji Guest');

        $this->delete(route('admin.tenants.destroy', $t))->assertRedirect('/login');
        $this->assertDatabaseHas('tenants', ['id' => $t->id]);
    }

    public function test_penghuni_tidak_menghuni_bisa_dihapus(): void
    {
        $t = $this->makeTenant('Penghuni Sudah Keluar');
        Tenancy::create([
            'tenant_id' => $t->id,
            'room_id' => $this->makeRoom()->id,
            'tanggal_masuk' => now()->subMonths(2)->toDateString(),
            'tanggal_keluar' => now()->subDay()->toDateString(),
            'status' => 'selesai',
        ]);

        $this->actingAs($this->admin)
            ->delete(route('admin.tenants.destroy', $t))
            ->assertSessionHas('ok');

        $this->assertDatabaseMissing('tenants', ['id' => $t->id]);
        $this->assertDatabaseMissing('tenancies', ['tenant_id' => $t->id]);
    }

    public function test_penghuni_masih_menghuni_tidak_bisa_dihapus(): void
    {
        $t = $this->makeTenant('Penghuni Masih Aktif');
        Tenancy::create([
            'tenant_id' => $t->id,
            'room_id' => $this->makeRoom()->id,
            'tanggal_masuk' => now()->subMonth()->toDateString(),
            'status' => 'aktif',
        ]);

        $this->actingAs($this->admin)
            ->from(route('admin.tenants.index'))
            ->delete(route('admin.tenants.destroy', $t))
            ->assertSessionHasErrors('kunci');

        $this->assertDatabaseHas('tenants', ['id' => $t->id]);
        $this->assertDatabaseHas('tenancies', ['tenant_id' => $t->id, 'status' => 'aktif']);
    }

    public function test_index_tombol_hapus_hanya_untuk_tidak_menghuni(): void
    {
        $tanpaKamar = $this->makeTenant('Penghuni Tanpa Kamar');
        $berkamar = $this->makeTenant('Penghuni Berkamar');
        Tenancy::create([
            'tenant_id' => $berkamar->id,
            'room_id' => $this->makeRoom()->id,
            'tanggal_masuk' => now()->toDateString(),
            'status' => 'aktif',
        ]);

        $res = $this->actingAs($this->admin)->get(route('admin.tenants.index'));
        $res->assertOk();
        $res->assertSee('action="' . route('admin.tenants.destroy', $tanpaKamar) . '"', false);
        $res->assertDontSee('action="' . route('admin.tenants.destroy', $berkamar) . '"', false);
    }

    public function test_hapus_dari_halaman_detail_diarahkan_ke_index_bukan_404(): void
    {
        $t = $this->makeTenant('Penghuni Hapus Dari Detail');

        // Hapus DARI halaman detail -> harus redirect ke index (bukan back() yang menyebabkan 404).
        $this->actingAs($this->admin)
            ->from(route('admin.tenants.show', $t))
            ->delete(route('admin.tenants.destroy', $t))
            ->assertRedirect(route('admin.tenants.index'));

        $this->assertDatabaseMissing('tenants', ['id' => $t->id]);
    }
}
