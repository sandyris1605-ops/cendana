<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\User;
use Tests\TestCase;

/**
 * Alur hapus karyawan: nonaktifkan dulu, baru boleh hapus.
 * Tanpa RefreshDatabase (koneksi MySQL dev) — baris uji dibersihkan di tearDown.
 */
class EmployeeDeactivationDeleteTest extends TestCase
{
    private ?User $admin = null;
    private array $employeeIds = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::create([
            'name' => 'Tester Employee Delete',
            'email' => 'employee-del-tester@example.test',
            'password' => 'secret-password',
            'role' => 'admin',
        ]);
    }

    protected function tearDown(): void
    {
        Employee::whereIn('id', $this->employeeIds)->delete();
        User::where('email', 'employee-del-tester@example.test')->delete();
        parent::tearDown();
    }

    private function makeEmployee(string $nama, string $status = 'aktif'): Employee
    {
        $e = Employee::create(['nama' => $nama, 'status' => $status]);
        $this->employeeIds[] = $e->id;

        return $e;
    }

    public function test_destroy_karyawan_aktif_ditolak(): void
    {
        $e = $this->makeEmployee('Karyawan Aktif Uji Hapus');

        $this->actingAs($this->admin)->delete(route('admin.employees.destroy', $e))
            ->assertSessionHasErrors('kunci');

        $this->assertDatabaseHas('employees', ['id' => $e->id]);
        $this->assertSame('aktif', $e->fresh()->status);
    }

    public function test_destroy_karyawan_nonaktif_berhasil(): void
    {
        $e = $this->makeEmployee('Karyawan Nonaktif Uji Hapus', 'nonaktif');

        $this->actingAs($this->admin)->delete(route('admin.employees.destroy', $e))
            ->assertSessionHas('ok');

        $this->assertDatabaseMissing('employees', ['id' => $e->id]);
    }

    public function test_update_status_lewat_form_mengubah_status(): void
    {
        $e = $this->makeEmployee('Karyawan Uji Nonaktif');

        $this->actingAs($this->admin)
            ->put(route('admin.employees.update', $e), ['status' => 'nonaktif'])
            ->assertRedirect(route('admin.employees.index'));

        $this->assertSame('nonaktif', $e->fresh()->status);
    }

    public function test_index_tombol_hapus_hanya_untuk_nonaktif(): void
    {
        $aktif = $this->makeEmployee('ZZK Aktif Uji Tombol', 'aktif');
        $nonaktif = $this->makeEmployee('ZZK Nonaktif Uji Tombol', 'nonaktif');

        $res = $this->actingAs($this->admin)->get(route('admin.employees.index'));
        $res->assertOk();
        // Karyawan nonaktif punya tombol/form hapus...
        $res->assertSee('action="' . route('admin.employees.destroy', $nonaktif) . '"', false);
        // ...karyawan aktif tidak.
        $res->assertDontSee('action="' . route('admin.employees.destroy', $aktif) . '"', false);
        // Badge status juga tampil.
        $res->assertSee('Nonaktif', false);
    }

    public function test_form_edit_menampilkan_pilihan_status(): void
    {
        $e = $this->makeEmployee('Karyawan Uji Form Status');

        $this->actingAs($this->admin)->get(route('admin.employees.edit', $e))
            ->assertOk()
            ->assertSee('name="status"', false)
            ->assertSee('Nonaktif', false);
    }
}
