<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * Fitur Backup Database di menu Pengaturan (khusus role admin).
 * File disimpan di storage/app/backups/*.sql. Tanpa RefreshDatabase
 * (koneksi MySQL dev) — file uji dibersihkan di tearDown.
 */
class BackupDatabaseTest extends TestCase
{
    private ?User $admin = null;
    private ?User $staff = null;
    private array $fileUji = [];

    private function dirBackup(): string
    {
        $dir = storage_path('app/backups');
        if (!is_dir($dir)) @mkdir($dir, 0755, true);
        return $dir;
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::create([
            'name' => 'Tester Backup Admin',
            'email' => 'backup-admin-tester@example.test',
            'password' => 'secret-password',
            'role' => 'admin',
        ]);
        $this->staff = User::create([
            'name' => 'Tester Backup Staff',
            'email' => 'backup-staff-tester@example.test',
            'password' => 'secret-password',
            'role' => 'staff',
        ]);
    }

    protected function tearDown(): void
    {
        foreach ($this->fileUji as $path) {
            if (is_file($path)) @unlink($path);
        }
        User::whereIn('email', ['backup-admin-tester@example.test', 'backup-staff-tester@example.test'])->delete();
        parent::tearDown();
    }

    private function buatFileDummy(): string
    {
        $path = $this->dirBackup() . '/backup-cendana-dummy-' . uniqid() . '.sql';
        file_put_contents($path, "-- dummy\n");
        $this->fileUji[] = $path;

        return $path;
    }

    public function test_guest_diredirect_ke_login(): void
    {
        $this->post(route('admin.pengaturan.backup'))->assertRedirect('/login');
    }

    public function test_staff_ditolak_403(): void
    {
        $this->actingAs($this->staff)
            ->post(route('admin.pengaturan.backup'))
            ->assertStatus(403);
    }

    public function test_admin_backup_menghasilkan_file_dan_terdownload(): void
    {
        $res = $this->actingAs($this->admin)->post(route('admin.pengaturan.backup'));
        $res->assertOk();
        $res->assertHeader('content-disposition');

        $file = $res->baseResponse->getFile();
        $this->assertNotNull($file);
        $this->fileUji[] = $file->getPathname();

        $this->assertFileExists($file->getPathname());
        $isi = (string) file_get_contents($file->getPathname());
        $this->assertStringContainsString('SET FOREIGN_KEY_CHECKS=0;', $isi);
        $this->assertStringContainsString('CREATE TABLE', $isi);
        $this->assertStringContainsString('SET FOREIGN_KEY_CHECKS=1;', $isi);
    }

    public function test_download_file_tidak_ada_404(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.pengaturan.backup.download', 'backup-cendana-tidak-ada.sql'))
            ->assertNotFound();
    }

    public function test_download_path_traversal_ditolak(): void
    {
        $this->actingAs($this->admin)
            ->get('/admin/pengaturan/backup/..%2F..%2F..%2F.env')
            ->assertNotFound();
    }

    public function test_hapus_file_backup(): void
    {
        $path = $this->buatFileDummy();

        $this->actingAs($this->admin)
            ->delete(route('admin.pengaturan.backup.hapus', basename($path)))
            ->assertSessionHas('ok');

        $this->assertFileDoesNotExist($path);
    }

    public function test_pengaturan_menampilkan_tombol_backup_untuk_admin(): void
    {
        $res = $this->actingAs($this->admin)->get(route('admin.pengaturan'));
        $res->assertOk();
        $res->assertSee('Backup Sekarang', false);
        $res->assertSee(route('admin.pengaturan.backup'), false);
    }
}
