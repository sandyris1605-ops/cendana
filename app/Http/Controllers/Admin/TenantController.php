<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TenantController extends Controller
{
    public function index(Request $r) {
        $q = \App\Models\Tenant::with('activeTenancy.room');
        if ($r->search) $q->where(function ($w) use ($r) {
            $w->where('nama','like','%'.$r->search.'%')->orWhere('nik','like','%'.$r->search.'%')->orWhere('telepon','like','%'.$r->search.'%');
        });
        $data = $q->orderBy('nama')->paginate(20)->withQueryString();
        $stat = [
            'total' => \App\Models\Tenant::count(),
            'menghuni' => \App\Models\Tenant::whereHas('activeTenancy')->count(),
        ];
        $stat['kosong'] = $stat['total'] - $stat['menghuni'];
        $stat['anggota'] = \App\Models\AnggotaKeluarga::count();
        $stat['jiwa'] = $stat['total'] + $stat['anggota'];
        return view('admin.tenants.index', ['tenants'=>$data,'stat'=>$stat]);
    }
    public function create() {
        return view('admin.tenants.form', ['tenant'=>new \App\Models\Tenant(),'rooms'=>$this->kamarKosong()]);
    }

    /** Hanya kamar berstatus 'tersedia' (kosong) yang boleh dipilih saat tambah penghuni. */
    protected function kamarKosong() {
        return \App\Models\Room::with('roomType')->where('status','tersedia')->orderBy('nomor')->get();
    }

    public function store(Request $r) {
        $r->validate([
            'nama' => 'required',
            'room_id' => 'required|exists:rooms,id',
            'tanggal_masuk' => 'required|date',
            'anggota' => 'nullable|array',
            'anggota.*.nama' => 'required_with:anggota.*.hubungan,anggota.*.umur|nullable|string|max:100',
            'anggota.*.hubungan' => 'nullable|string|max:50',
            'anggota.*.umur' => 'nullable|integer|min:0|max:120',
        ]);
        // Kamar wajib masih kosong — cek di dalam transaksi + lock agar
        // dua admin submit bersamaan tidak bisa mengisi kamar yang sama.
        \DB::beginTransaction();
        try {
            $room = \App\Models\Room::with('roomType')->whereKey($r->room_id)->lockForUpdate()->firstOrFail();
            $kamarTerpakai = $room->status !== 'tersedia' || $room->activeTenancy()->exists();
            if ($kamarTerpakai) {
                \DB::rollBack();
                return back()->withInput()->withErrors([
                    'room_id' => 'Kamar '.$room->nomor.' tidak bisa dipakai'.($room->status !== 'tersedia' ? ' (status: '.$room->status.')' : ' — sudah ditempati penghuni lain').' — pilih kamar lain.',
                ]);
            }
            $t = \App\Models\Tenant::create($r->only('nama','nik','telepon','alamat_asal','pekerjaan'));
            $this->simpanAnggota($t, $r->input('anggota', []));
            \App\Models\Tenancy::create(['tenant_id'=>$t->id,'room_id'=>$room->id,'tanggal_masuk'=>$r->tanggal_masuk,'status'=>'aktif']);
            $room->update(['status'=>'terisi']);
            \DB::commit();
        } catch (\Throwable $e) {
            \DB::rollBack();
            throw $e;
        }
        $jmlAnggota = $t->anggota()->count();
        return redirect()->route('admin.tenants.index')->with('ok','Penghuni + penempatan dibuat'.($jmlAnggota ? " (+{$jmlAnggota} anggota keluarga)" : '').'.');
    }

    /** Simpan daftar anggota keluarga; baris tanpa nama dilewati. */
    protected function simpanAnggota(\App\Models\Tenant $t, array $anggota): void
    {
        foreach ($anggota as $a) {
            $nama = trim($a['nama'] ?? '');
            if ($nama === '') continue;
            $t->anggota()->create([
                'nama' => $nama,
                'hubungan' => $a['hubungan'] ?? 'lainnya',
                'umur' => ($a['umur'] ?? null) !== '' && ($a['umur'] ?? null) !== null ? (int) $a['umur'] : null,
            ]);
        }
    }

    public function show(\App\Models\Tenant $tenant) {
        $tenant->load(['tenancies.room','activeTenancy.room','anggota']);
        return view('admin.tenants.show', compact('tenant'));
    }
    public function edit(\App\Models\Tenant $tenant) {
        $tenant->load('anggota');
        // Bagian pilih kamar hanya untuk tambah — edit tidak mengubah kamar.
        return view('admin.tenants.form', ['tenant'=>$tenant,'rooms'=>collect()]);
    }
    public function update(Request $r, \App\Models\Tenant $tenant) {
        $r->validate([
            'anggota' => 'nullable|array',
            'anggota.*.nama' => 'required_with:anggota.*.hubungan,anggota.*.umur|nullable|string|max:100',
            'anggota.*.hubungan' => 'nullable|string|max:50',
            'anggota.*.umur' => 'nullable|integer|min:0|max:120',
        ]);
        $tenant->update($r->only('nama','nik','telepon','alamat_asal','pekerjaan'));
        // Sinkronkan anggota: cocokkan berdasarkan id, tambah baru, hapus yang tidak dikirim
        $ada = $tenant->anggota()->get()->keyBy('id');
        $kirim = [];
        foreach ($r->input('anggota', []) as $a) {
            $nama = trim($a['nama'] ?? '');
            if ($nama === '') continue;
            $umur = ($a['umur'] ?? null) !== '' && ($a['umur'] ?? null) !== null ? (int) $a['umur'] : null;
            $id = $a['id'] ?? null;
            if ($id && $ada->has($id)) {
                $ada[$id]->update(['nama'=>$nama,'hubungan'=>$a['hubungan'] ?? 'lainnya','umur'=>$umur]);
                $kirim[] = (int) $id;
            } else {
                $baru = $tenant->anggota()->create(['nama'=>$nama,'hubungan'=>$a['hubungan'] ?? 'lainnya','umur'=>$umur]);
                $kirim[] = $baru->id;
            }
        }
        // Hapus anggota yang sudah tidak ada di form
        $hapus = $ada->keys()->diff($kirim);
        if ($hapus->isNotEmpty()) $tenant->anggota()->whereIn('id', $hapus)->delete();
        return redirect()->route('admin.tenants.index')->with('ok','Diupdate.');
    }
    public function destroy(\App\Models\Tenant $tenant) {
        if ($tenant->activeTenancy) {
            $kamar = optional(optional($tenant->activeTenancy)->room)->nomor ?? '-';
            return back()->withErrors(['kunci'=>'Penghuni masih menghuni Kamar '.$kamar.' - klik "Penghuni Keluar" dulu di halaman detail sebelum menghapus.']);
        }
        \DB::transaction(function () use ($tenant) {
            $tenant->tenancies()->delete();
            $tenant->delete();
        });
        return redirect()->route('admin.tenants.index')->with('ok','Penghuni dan riwayat penempatan ikut dihapus.');
    }
}
