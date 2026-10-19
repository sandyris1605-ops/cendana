<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class GudangController extends Controller
{
    public function index(Request $r)
    {
        $units = \App\Models\AssetUnit::with(['item.category', 'purchase'])
            ->where('status', 'gudang');
        if ($r->search) {
            $units->where(function ($w) use ($r) {
                $w->where('kode_barcode', 'like', '%'.$r->search.'%')
                    ->orWhereHas('item', fn ($i) => $i->where('nama', 'like', '%'.$r->search.'%'));
            });
        }

        return view('admin.gudang.index', [
            'units' => $units->orderByDesc('id')->paginate(20)->withQueryString(),
            'rooms' => \App\Models\Room::orderBy('nomor')->get(),
        ]);
    }

    public function transfer(Request $r)
    {
        $r->validate([
            'room_id' => 'required|exists:rooms,id',
            'kode_barcode' => 'required|string',
        ]);

        $kode = trim($r->input('kode_barcode'));

        if ($kode === '') {
            return back()->withInput()->withErrors(['kode_barcode' => 'Pilih minimal 1 unit untuk ditransfer.']);
        }

        $barcodes = [$kode];

        $room = \App\Models\Room::findOrFail($r->room_id);

        $sukses = [];
        $gagal = [];

        foreach (array_values(array_unique($barcodes)) as $kode) {
            $unit = \App\Models\AssetUnit::with(['item', 'activePlacement.room'])
                ->where('kode_barcode', $kode)
                ->first();

            if (!$unit) {
                $gagal[] = 'Barcode "'.$kode.'" tidak ditemukan.';
                continue;
            }
            if ($unit->kondisi === 'rusak') {
                $gagal[] = $unit->kode_barcode.' berstatus RUSAK (histori) — tidak bisa dipasang. Perbaiki dulu via menu Penggantian.';
                continue;
            }
            if ($unit->kondisi === 'perlu_diganti') {
                $gagal[] = $unit->kode_barcode.' ditandai perlu diganti — ganti unit baru via menu Penggantian, bukan transfer biasa.';
                continue;
            }
            if (optional($unit->activePlacement)->room_id == $room->id) {
                $gagal[] = $unit->kode_barcode.' sudah berada di Kamar '.$room->nomor.'.';
                continue;
            }

            $from = optional(optional($unit->activePlacement)->room)->nomor ?? 'Gudang';

            // Tutup penempatan aktif (jika ada) lalu buat penempatan baru — sama seperti AssetController::pasang
            \App\Models\RoomAsset::where('asset_unit_id', $unit->id)->where('status', 'aktif')
                ->update(['status' => 'dilepas', 'tanggal_lepas' => now()->toDateString()]);
            \App\Models\RoomAsset::create([
                'room_id' => $room->id,
                'asset_unit_id' => $unit->id,
                'tanggal_pasang' => now()->toDateString(),
                'status' => 'aktif',
            ]);
            $unit->update(['status' => 'terpasang']);

            $sukses[] = $unit->kode_barcode.' ('.optional($unit->item)->nama.')';
        }

        // Mode tunggal: pertahankan pesan persis seperti sebelumnya.
        if (count($barcodes) === 1 && count($sukses) === 1) {
            return back()->withInput()->with('ok', $sukses[0].' dipindahkan: Gudang → Kamar '.$room->nomor.'.');
        }

        $pesan = count($sukses).' unit dipindahkan ke Kamar '.$room->nomor.'.';
        if ($gagal) {
            $pesan .= ' Gagal '.count($gagal).': '.implode(' | ', $gagal);
            return back()->withInput()->withErrors(['kode_barcode' => $pesan]);
        }
        return back()->withInput()->with('ok', $pesan);
    }
}
