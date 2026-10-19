<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Building;
use App\Models\Category;
use App\Models\RoomType;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SettingController extends Controller
{
    public function index()
    {
        $types = RoomType::withCount('rooms')->orderBy('nama')->get();
        $buildings = Building::withCount('rooms')->orderBy('nama')->get();
        $categories = Category::withCount('items')->orderBy('nama')->get();
        $umum = Setting::pluck('value', 'key')->all();
        $backups = collect(glob($this->dirBackup() . '/*.sql'))
            ->map(fn ($p) => ['nama' => basename($p), 'ukuran' => (int) filesize($p), 'waktu' => (int) filemtime($p)])
            ->sortByDesc('waktu')->values();
        return view('admin.pengaturan.index', compact('types', 'buildings', 'categories', 'umum', 'backups'));
    }

    // ---------- Tipe Kamar ----------
    public function simpanTipe(Request $r)
    {
        $r->validate([
            'nama' => 'required|unique:room_types,nama',
            'ukuran' => 'nullable|string|max:50',
        ]);
        RoomType::create($r->only('nama', 'ukuran'));
        return back()->with('ok', 'Tipe kamar ditambah.');
    }

    public function updateTipe(Request $r, RoomType $tipe)
    {
        $r->validate([
            'nama' => 'required|unique:room_types,nama,'.$tipe->id,
            'ukuran' => 'nullable|string|max:50',
        ]);
        $tipe->update($r->only('nama', 'ukuran'));
        return back()->with('ok', 'Tipe kamar diupdate.');
    }

    public function hapusTipe(RoomType $tipe)
    {
        if ($tipe->rooms()->count() > 0) {
            return back()->withErrors(['hapus' => 'Tidak dapat dihapus — masih dipakai oleh '.$tipe->rooms()->count().' kamar.']);
        }
        $tipe->delete();
        return back()->with('ok', 'Tipe kamar dihapus.');
    }

    // ---------- Gedung ----------
    public function simpanGedung(Request $r)
    {
        $r->validate([
            'nama' => 'required|unique:buildings,nama',
            'alamat' => 'nullable|string|max:255',
            'keterangan' => 'nullable|string|max:255',
        ]);
        Building::create($r->only('nama', 'alamat', 'keterangan'));
        return back()->with('ok', 'Gedung ditambah.');
    }

    public function updateGedung(Request $r, Building $gedung)
    {
        $r->validate([
            'nama' => 'required|unique:buildings,nama,'.$gedung->id,
            'alamat' => 'nullable|string|max:255',
            'keterangan' => 'nullable|string|max:255',
        ]);
        $gedung->update($r->only('nama', 'alamat', 'keterangan'));
        return back()->with('ok', 'Gedung diupdate.');
    }

    public function hapusGedung(Building $gedung)
    {
        if ($gedung->rooms()->count() > 0) {
            return back()->withErrors(['hapus' => 'Tidak dapat dihapus — masih memuat '.$gedung->rooms()->count().' kamar.']);
        }
        $gedung->delete();
        return back()->with('ok', 'Gedung dihapus.');
    }

    // ---------- Kategori Barang ----------
    public function simpanKategori(Request $r)
    {
        $r->validate([
            'nama' => 'required|unique:categories,nama',
            'deskripsi' => 'nullable|string|max:255',
        ]);
        Category::create($r->only('nama', 'deskripsi'));
        return back()->with('ok', 'Kategori ditambah.');
    }

    public function updateKategori(Request $r, Category $category)
    {
        $r->validate([
            'nama' => 'required|unique:categories,nama,'.$category->id,
            'deskripsi' => 'nullable|string|max:255',
        ]);
        $category->update($r->only('nama', 'deskripsi'));
        return back()->with('ok', 'Kategori diupdate.');
    }

    public function hapusKategori(Category $category)
    {
        if ($category->items()->count() > 0) {
            return back()->withErrors(['hapus' => 'Tidak dapat dihapus — masih dipakai oleh '.$category->items()->count().' barang.']);
        }
        $category->delete();
        return back()->with('ok', 'Kategori dihapus.');
    }

    // ---------- Pengaturan Umum ----------
    public function simpanUmum(Request $r)
    {
        $r->validate([
            'nama' => 'required|string|max:100',
            'alamat' => 'nullable|string|max:255',
            'telepon' => 'nullable|string|max:30',
            'wa' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:100',
        ]);
        foreach ($r->only('nama', 'alamat', 'telepon', 'wa', 'email') as $k => $v) {
            Setting::set($k, $v);
        }
        return back()->with('ok', 'Pengaturan umum disimpan.');
    }


    // ---------- Backup Database (khusus role admin) ----------
    protected function dirBackup(): string
    {
        $dir = storage_path('app/backups');
        if (!is_dir($dir)) @mkdir($dir, 0755, true);
        return $dir;
    }

    protected function wajibAdmin(): void
    {
        abort_unless(auth()->check() && auth()->user()->role === 'admin', 403, 'Backup database khusus admin.');
    }

    public function backupDatabase()
    {
        $this->wajibAdmin();
        $path = $this->dirBackup() . '/backup-cendana-' . date('Ymd-His') . '-' . substr(uniqid(), -4) . '.sql';
        $pdo = DB::getPdo();
        $fh = fopen($path, 'w');
        fwrite($fh, "-- Backup database Cendana (" . DB::getDatabaseName() . ")\n-- Dibuat: " . date('Y-m-d H:i:s') . " via panel Pengaturan\nSET FOREIGN_KEY_CHECKS=0;\n\n");
        foreach (DB::select('SHOW TABLES') as $t) {
            $tn = array_values((array) $t)[0];
            $ddl = array_values((array) DB::select("SHOW CREATE TABLE `{$tn}`")[0])[1];
            fwrite($fh, "DROP TABLE IF EXISTS `{$tn}`;\n{$ddl};\n\n");
            $offset = 0;
            $batch = 300;
            while (true) {
                $rows = DB::select("SELECT * FROM `{$tn}` LIMIT {$batch} OFFSET {$offset}");
                if (!$rows) break;
                foreach (array_chunk($rows, 50) as $chunk) {
                    $kolom = array_map(fn ($k) => "`{$k}`", array_keys((array) $chunk[0]));
                    $vals = [];
                    foreach ($chunk as $row) {
                        $vals[] = '(' . implode(', ', array_map(fn ($v) => $v === null ? 'NULL' : $pdo->quote((string) $v), (array) $row)) . ')';
                    }
                    fwrite($fh, "INSERT INTO `{$tn}` (" . implode(', ', $kolom) . ") VALUES\n" . implode(",\n", $vals) . ";\n");
                }
                if (count($rows) < $batch) break;
                $offset += $batch;
            }
            fwrite($fh, "\n");
        }
        fwrite($fh, "SET FOREIGN_KEY_CHECKS=1;\n");
        fclose($fh);
        return response()->download($path);
    }

    public function downloadBackup(string $file)
    {
        $this->wajibAdmin();
        $path = $this->dirBackup() . '/' . $file;
        abort_unless(is_file($path), 404);
        return response()->download($path);
    }

    public function hapusBackup(string $file)
    {
        $this->wajibAdmin();
        $path = $this->dirBackup() . '/' . $file;
        if (is_file($path)) unlink($path);
        return back()->with('ok', 'File backup dihapus.');
    }
}
