<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AssetController extends Controller
{
    public function index(Request $r) {
        $q = \App\Models\AssetUnit::with(['item','activePlacement.room']);
        if ($r->search) $q->where('kode_barcode','like','%'.$r->search.'%')->orWhereHas('item', fn ($i) => $i->where('nama','like','%'.$r->search.'%'));
        if ($r->kondisi === 'dibuang') $q->where('status','dibuang');
        elseif ($r->kondisi) $q->where('kondisi',$r->kondisi)->where('status','!=','dibuang');
        return view('admin.assets.index', ['units'=>$q->orderByDesc('id')->paginate(20)->withQueryString()]);
    }
    public function show(\App\Models\AssetUnit $asset) {
        $asset->load(['item','placements.room']);
        return view('admin.assets.show', compact('asset'));
    }
    public function penggantian(\Illuminate\Http\Request $r) {
        $q = \App\Models\AssetUnit::with(['item','activePlacement.room']);
        if ($r->search) $q->where('kode_barcode','like','%'.$r->search.'%')->orWhereHas('item', fn ($i) => $i->where('nama','like','%'.$r->search.'%'));
        if ($r->kondisi === 'perlu_diganti') $q->where('kondisi','perlu_diganti');
        elseif ($r->kondisi === 'rusak') $q->where('kondisi','rusak');
        elseif ($r->kondisi === 'terlambat') $q->whereDate('tanggal_ganti_rencana','<=',now()->toDateString());
        elseif ($r->kondisi === 'segera') $q->whereDate('tanggal_ganti_rencana','>',now()->toDateString())->whereDate('tanggal_ganti_rencana','<=',now()->addDays(90)->toDateString());
        $units = $q->orderByRaw("CASE WHEN kondisi='perlu_diganti' THEN 0 ELSE 1 END")->orderBy('tanggal_ganti_rencana')->paginate(20)->withQueryString();
        $jmlPerlu = \App\Models\AssetUnit::where('kondisi','perlu_diganti')->count();
        $stokGudang = \App\Models\AssetUnit::where('status','gudang')->where('kondisi','baik')->orderBy('kode_barcode')->get(['id','kode_barcode','item_id']);
        return view('admin.assets.penggantian', compact('units','jmlPerlu','stokGudang'));
    }
    public function penyusutan(\Illuminate\Http\Request $r) {
        $q = \App\Models\AssetUnit::with('item');
        if ($r->search) $q->where('kode_barcode','like','%'.$r->search.'%')->orWhereHas('item', fn ($i) => $i->where('nama','like','%'.$r->search.'%'));
        $units = $q->orderByDesc('id')->paginate(20)->withQueryString();
        $master = \App\Models\Item::orderBy('nama')->get(['id','nama','harga_estimasi','umur_pakai_bulan']);
        $masterJson = $master->map(fn ($m) => ['id'=>$m->id,'nama'=>$m->nama,'harga'=>(float) $m->harga_estimasi,'umur'=>(int) $m->umur_pakai_bulan])->values()->toJson();
        return view('admin.assets.penyusutan', compact('units','master','masterJson'));
    }
    public function hitungPenyusutan(\App\Services\PenyusutanService $layanan) {
        $hasil = $layanan->hitungTertinggal();
        $daftar = $hasil['periode'] ? implode(', ', $hasil['periode']) : 'tidak ada periode baru';
        return back()->with('ok',"Penyusutan dihitung untuk {$hasil['unit']} unit — periode: {$daftar}.");
    }
    public function pasang(Request $r, \App\Models\AssetUnit $asset) {
        $r->validate(['room_id'=>'required|exists:rooms,id']);
        \App\Models\RoomAsset::where('asset_unit_id',$asset->id)->where('status','aktif')->update(['status'=>'dilepas','tanggal_lepas'=>now()->toDateString()]);
        \App\Models\RoomAsset::create(['room_id'=>$r->room_id,'asset_unit_id'=>$asset->id,'tanggal_pasang'=>now()->toDateString(),'status'=>'aktif']);
        $asset->update(['status'=>'terpasang']);
        return back()->with('ok','Barang dipasang.');
    }
    public function lepas(\App\Models\AssetUnit $asset) {
        \App\Models\RoomAsset::where('asset_unit_id',$asset->id)->where('status','aktif')->update(['status'=>'dilepas','tanggal_lepas'=>now()->toDateString()]);
        $asset->update(['status'=>'gudang']);
        return back()->with('ok','Barang dilepas.');
    }
    public function tandaiGanti(\App\Models\AssetUnit $asset) {
        if ($asset->kondisi === 'rusak') return back()->withErrors(['kondisi'=>$asset->kode_barcode.' berstatus rusak (histori) — perbaiki dulu sebelum ditandai.']);
        $asset->update(['kondisi'=>'perlu_diganti']);
        return back()->with('ok',$asset->kode_barcode.' ditandai perlu diganti (mis. putus/rusak sebelum jadwal).');
    }
    public function selesaiGanti(Request $r, \App\Models\AssetUnit $asset) {
        $r->validate(['pengganti_id'=>'nullable|exists:asset_units,id']);
        // Opsi A lama: diperbaiki tanpa ganti fisik
        if (!$r->pengganti_id) {
            $umur = max(1,(int) optional($asset->item)->umur_pakai_bulan);
            $asset->update(['kondisi'=>'baik','tanggal_ganti_rencana'=>now()->addMonths($umur)->toDateString()]);
            return back()->with('ok',$asset->kode_barcode.' selesai diganti — jadwal berikutnya '.$asset->fresh()->tanggal_ganti_rencana.'.');
        }
        // Tukar unit: lama dilepas (rusak, histori) + pengganti dipasang di kamar yang sama
        $ganti = \App\Models\AssetUnit::findOrFail($r->pengganti_id);
        if ($ganti->id === $asset->id) return back()->withErrors(['pengganti_id'=>'Unit pengganti tidak boleh unit itu sendiri.']);
        if ($ganti->item_id !== $asset->item_id) return back()->withErrors(['pengganti_id'=>$ganti->kode_barcode.' beda jenis barang — pilih yang sejenis ('.optional($asset->item)->nama.').']);
        if ($ganti->status !== 'gudang' || $ganti->kondisi !== 'baik') return back()->withErrors(['pengganti_id'=>$ganti->kode_barcode.' tidak tersedia (harus di gudang & kondisi baik).']);
        $kamarId = optional($asset->activePlacement)->room_id;
        if (!$kamarId) return back()->withErrors(['pengganti_id'=>$asset->kode_barcode.' sedang tidak terpasang di kamar mana pun — pakai menu Stok Gudang untuk transfer biasa.']);
        \DB::transaction(function () use ($asset, $ganti, $kamarId) {
            \App\Models\RoomAsset::where('asset_unit_id',$asset->id)->where('status','aktif')
                ->update(['status'=>'dilepas','tanggal_lepas'=>now()->toDateString()]);
            $asset->update(['status'=>'gudang','kondisi'=>'rusak']);
            \App\Models\RoomAsset::create(['room_id'=>$kamarId,'asset_unit_id'=>$ganti->id,'tanggal_pasang'=>now()->toDateString(),'status'=>'aktif']);
            $umur = max(1,(int) optional($ganti->item)->umur_pakai_bulan);
            $ganti->update(['status'=>'terpasang','kondisi'=>'baik','tanggal_ganti_rencana'=>now()->addMonths($umur)->toDateString()]);
        });
        $nomor = optional(\App\Models\Room::find($kamarId))->nomor ?? '-';
        return back()->with('ok',$asset->kode_barcode.' dilepas (rusak, tercatat histori) → '.$ganti->kode_barcode.' dipasang di Kamar '.$nomor.'.');
    }
    public function perbaiki(\App\Models\AssetUnit $asset) {
        $umur = max(1,(int) optional($asset->item)->umur_pakai_bulan);
        $asset->update(['kondisi'=>'baik','tanggal_ganti_rencana'=>now()->addMonths($umur)->toDateString()]);
        return back()->with('ok',$asset->kode_barcode.' diperbaiki — kembali baik dan bisa dipakai lagi.');
    }
    public function buang(\App\Models\AssetUnit $asset) {
        if ($asset->status !== 'gudang' || $asset->kondisi !== 'rusak') {
            return back()->withErrors(['kondisi'=>'Hanya unit rusak yang sedang di gudang yang bisa dibuang.']);
        }
        $asset->update(['status'=>'dibuang']);
        return back()->with('ok',$asset->kode_barcode.' dibuang — tercatat sebagai histori, tidak lagi dihitung di stok/penyusutan.');
    }
}
