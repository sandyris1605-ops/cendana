<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TenancyController extends Controller
{
    public function index(Request $r) {
        $q = \App\Models\Tenancy::with(['tenant','room.roomType']);
        if ($r->search) $q->where(function ($w) use ($r) {
            $w->whereHas('tenant', fn ($t) => $t->where('nama','like','%'.$r->search.'%'))
              ->orWhereHas('room', fn ($t) => $t->where('nomor','like','%'.$r->search.'%'));
        });
        $data = $q->orderByDesc('id')->paginate(20)->withQueryString();
        $stat = [
            'total' => \App\Models\Tenancy::count(),
            'aktif' => \App\Models\Tenancy::where('status','aktif')->count(),
            'selesai' => \App\Models\Tenancy::where('status','selesai')->count(),
        ];
        return view('admin.tenancies.index', ['tenancies'=>$data,'stat'=>$stat]);
    }
    public function show(\App\Models\Tenancy $tenancy) {
        $tenancy->load(['tenant','room.roomType']);
        return view('admin.tenancies.show', compact('tenancy'));
    }
    public function pindah(Request $r, \App\Models\Tenancy $tenancy)
    {
        if ($tenancy->status !== 'aktif') {
            return back()->withErrors(['pindah' => 'Hanya kontrak aktif yang bisa dipindahkan.']);
        }

        $r->validate([
            'room_tujuan_id' => 'required|exists:rooms,id',
            'tanggal_pindah' => 'required|date',
            'asset_ids'      => 'nullable|array',
            'asset_ids.*'    => 'exists:room_assets,id',
        ]);

        $roomAsal   = $tenancy->room;
        $roomTujuan = \App\Models\Room::findOrFail($r->room_tujuan_id);

        if ($roomTujuan->id === $roomAsal->id) {
            return back()->withErrors(['pindah' => 'Kamar tujuan harus berbeda dari kamar asal.']);
        }
        if ($roomTujuan->status !== 'tersedia') {
            return back()->withErrors(['pindah' => 'Kamar '.$roomTujuan->nomor.' tidak tersedia (status: '.$roomTujuan->status.'). Kosongkan dulu sebelum memindahkan penghuni.']);
        }

        // Ambil hanya penempatan (room_assets) yang valid: milik kamar asal & masih aktif.
        // Ini mencegah manipulasi ID dari luar — barang kamar lain tidak akan ikut.
        $dipilih = \App\Models\RoomAsset::whereIn('id', $r->asset_ids ?? [])
            ->where('room_id', $roomAsal->id)
            ->where('status', 'aktif')
            ->get();

        $tgl = $r->tanggal_pindah;
        $jmlBarang = $dipilih->count();

        \DB::transaction(function () use ($tenancy, $roomAsal, $roomTujuan, $tgl, $dipilih) {
            // 1) Tutup kontrak lama di kamar asal
            $tenancy->update(['status' => 'selesai', 'tanggal_keluar' => $tgl]);

            // 2) Buat kontrak baru di kamar tujuan
            \App\Models\Tenancy::create([
                'tenant_id'      => $tenancy->tenant_id,
                'room_id'        => $roomTujuan->id,
                'tanggal_masuk'  => $tgl,
                'catatan'        => $tenancy->catatan,
                'status'         => 'aktif',
            ]);

            // 3) Update status kamar
            $roomAsal->update(['status' => 'tersedia']);
            $roomTujuan->update(['status' => 'terisi']);

            // 4) Pindahkan hanya barang yang dicentang; yang tidak dicentang tetap di kamar asal
            foreach ($dipilih as $ra) {
                // Tutup penempatan lama (histori kamar asal)
                $ra->update(['status' => 'dilepas', 'tanggal_lepas' => $tgl]);
                // Buat penempatan baru di kamar tujuan
                \App\Models\RoomAsset::create([
                    'room_id'        => $roomTujuan->id,
                    'asset_unit_id'  => $ra->asset_unit_id,
                    'tanggal_pasang' => $tgl,
                    'status'         => 'aktif',
                ]);
            }
        });

        \App\Models\ActivityLog::create([
            'user_id' => auth()->id(),
            'aksi'    => 'Pindah Kamar',
            'modul'   => 'tenancies',
        ]);

        $nama = optional($tenancy->tenant)->nama ?? 'Penghuni';
        $nama = htmlspecialchars($nama, ENT_QUOTES, 'UTF-8');
        $pesanBarang = $jmlBarang > 0 ? ", {$jmlBarang} unit barang ikut" : ', barang tidak ikut';
        return redirect()
            ->route('admin.rooms.show', $roomTujuan->id)
            ->with('ok', $nama.' dipindahkan: Kamar '.$roomAsal->nomor.' → Kamar '.$roomTujuan->nomor.$pesanBarang.'.');
    }

    public function destroy(\App\Models\Tenancy $tenancy) {
        if ($tenancy->status !== 'aktif') return back()->with('ok','Penempatan sudah selesai.');
        $tenancy->update(['status'=>'selesai','tanggal_keluar'=>now()->toDateString()]);
        $tenancy->room()->update(['status'=>'tersedia']);
        return back()->with('ok','Penghuni keluar, kamar tersedia.');
    }
}
