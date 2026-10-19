<?php

namespace Tests\Feature;

use App\Models\Room;
use App\Models\RoomType;
use App\Models\Tenancy;
use App\Models\Tenant;
use App\Models\User;
use Tests\TestCase;

/**
 * Kamar terisi / maintenance tidak boleh bisa dipilih lagi saat Tambah Penghuni.
 * Tanpa RefreshDatabase (koneksi MySQL dev) — semua baris uji dibersihkan di tearDown.
 */
class TenantRoomAvailabilityTest extends TestCase
{
    private ?User $admin = null;
    private array $roomIds = [];
    private array $roomTypeIds = [];
    private array $tenantIds = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::create([
            'name' => 'Tester Room Availability',
            'email' => 'tenant-room-avail-tester@example.test',
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
        User::where('email', 'tenant-room-avail-tester@example.test')->delete();
        parent::tearDown();
    }

    private function makeRoom(string $status = 'tersedia'): Room
    {
        $type = RoomType::create(['nama' => 'Tipe Uji Ketersediaan']);
        $this->roomTypeIds[] = $type->id;
        $room = Room::create(['room_type_id' => $type->id, 'nomor' => 'RA-' . uniqid(), 'status' => $status]);
        $this->roomIds[] = $room->id;

        return $room;
    }

    private function isiKamar(Room $room): Tenant
    {
        $t = Tenant::create(['nama' => 'Penghuni Uji ' . $room->nomor]);
        $this->tenantIds[] = $t->id;
        Tenancy::create(['tenant_id' => $t->id, 'room_id' => $room->id, 'tanggal_masuk' => now()->toDateString(), 'status' => 'aktif']);

        return $t;
    }

    public function test_form_tambah_hanya_menampilkan_kamar_tersedia(): void
    {
        $tersedia = $this->makeRoom('tersedia');
        $terisi = $this->makeRoom('terisi');
        $this->isiKamar($terisi);
        $maintenance = $this->makeRoom('maintenance');

        $this->actingAs($this->admin)->get(route('admin.tenants.create'))
            ->assertOk()
            ->assertSee($tersedia->nomor)
            ->assertDontSee($terisi->nomor)
            ->assertDontSee($maintenance->nomor);
    }

    public function test_store_kamar_terisi_ditolak_tanpa_membuat_penghuni(): void
    {
        $terisi = $this->makeRoom('terisi');
        $this->isiKamar($terisi);

        $this->actingAs($this->admin)->post(route('admin.tenants.store'), [
            'nama' => 'Penghuni Nyasar',
            'room_id' => $terisi->id,
            'tanggal_masuk' => now()->toDateString(),
        ])->assertSessionHasErrors('room_id');

        $this->assertDatabaseMissing('tenants', ['nama' => 'Penghuni Nyasar']);
        $this->assertSame(1, Tenancy::where('room_id', $terisi->id)->count(), 'Tenancy ganda tidak boleh terbentuk.');
        $this->assertSame('terisi', $terisi->fresh()->status);
    }

    public function test_store_kamar_maintenance_ditolak(): void
    {
        $maintenance = $this->makeRoom('maintenance');

        $this->actingAs($this->admin)->post(route('admin.tenants.store'), [
            'nama' => 'Penghuni Nyasar 2',
            'room_id' => $maintenance->id,
            'tanggal_masuk' => now()->toDateString(),
        ])->assertSessionHasErrors('room_id');

        $this->assertDatabaseMissing('tenants', ['nama' => 'Penghuni Nyasar 2']);
        $this->assertSame('maintenance', $maintenance->fresh()->status);
    }

    public function test_store_kamar_tersedia_berhasil_lalu_kamar_hilang_dari_dropdown(): void
    {
        $room = $this->makeRoom('tersedia');

        $this->actingAs($this->admin)->post(route('admin.tenants.store'), [
            'nama' => 'Penghuni Sah',
            'room_id' => $room->id,
            'tanggal_masuk' => now()->toDateString(),
        ])->assertRedirect(route('admin.tenants.index'))->assertSessionHas('ok');

        $this->assertSame('terisi', $room->fresh()->status);
        $this->assertSame(1, Tenancy::where('room_id', $room->id)->where('status', 'aktif')->count());

        // Kamar yang baru terisi tidak lagi muncul di form tambah berikutnya.
        $this->actingAs($this->admin)->get(route('admin.tenants.create'))
            ->assertOk()->assertDontSee($room->nomor);
    }

    public function test_double_submit_kamar_sama_yang_kedua_ditolak(): void
    {
        $room = $this->makeRoom('tersedia');
        $payload = ['room_id' => $room->id, 'tanggal_masuk' => now()->toDateString()];

        $this->actingAs($this->admin)
            ->post(route('admin.tenants.store'), $payload + ['nama' => 'Penghuni Pertama'])
            ->assertRedirect(route('admin.tenants.index'));

        $this->actingAs($this->admin)
            ->post(route('admin.tenants.store'), $payload + ['nama' => 'Penghuni Kedua'])
            ->assertSessionHasErrors('room_id');

        $this->assertDatabaseMissing('tenants', ['nama' => 'Penghuni Kedua']);
        $this->assertSame(1, Tenancy::where('room_id', $room->id)->where('status', 'aktif')->count());
    }

    public function test_form_tanpa_kamar_tersedia_tampil_peringatan_dan_tombol_nonaktif(): void
    {
        // Render view langsung dengan $rooms kosong — tidak bergantung pada
        // data kamar asli di DB dev (kamar dev bisa saja berstatus tersedia).
        $html = view('admin.tenants.form', [
            'tenant' => new Tenant(),
            'rooms' => collect(),
            'errors' => new \Illuminate\Support\ViewErrorBag(),
        ])->render();

        $this->assertStringContainsString('Tidak ada kamar tersedia', $html);
        $this->assertStringContainsString('disabled:cursor-not-allowed" disabled', $html);
    }

    public function test_edit_penghuni_tidak_menampilkan_pilihan_kamar(): void
    {
        $this->makeRoom('tersedia');
        $t = Tenant::create(['nama' => 'Penghuni Edit Uji']);
        $this->tenantIds[] = $t->id;

        // Edit tidak mengubah kamar — bagian penempatan kamar tidak dirender.
        $this->actingAs($this->admin)->get(route('admin.tenants.edit', $t))
            ->assertOk()->assertDontSee('name="room_id"', false);
    }
}
