<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BarcodeController extends Controller
{
    public function index(\Illuminate\Http\Request $r) {
        $q = \App\Models\AssetUnit::with(['item','activePlacement.room']);
        if ($r->search) $q->where('kode_barcode','like','%'.$r->search.'%');
        if ($r->item_id) $q->where('item_id',$r->item_id);
        if ($r->room_id) $q->whereHas('activePlacement', fn($w) => $w->where('room_id',$r->room_id));
        return view('admin.barcode.index', ['units'=>$q->orderBy('kode_barcode')->paginate(48)->withQueryString(),'items'=>\App\Models\Item::orderBy('nama')->get(),'rooms'=>\App\Models\Room::orderBy('nomor')->get()]);
    }
    public function cetak(\Illuminate\Http\Request $r) {
        $room = null;
        $q = \App\Models\AssetUnit::with('item');
        if ($r->room_id) {
            $room = \App\Models\Room::find($r->room_id);
            $q->whereHas('activePlacement', fn($w) => $w->where('room_id',$r->room_id));
        } elseif ($r->ids) {
            $q->whereIn('id', array_filter(explode(',', $r->ids)));
        } else {
            $q->limit(60);
        }
        $units = $q->orderBy('kode_barcode')->get();
        return view('admin.barcode.cetak', compact('units','room'));
    }
    public function scan($kode) {
        $unit = \App\Models\AssetUnit::with(['item','activePlacement.room','placements.room'])->where('kode_barcode',$kode)->firstOrFail();
        if (request()->wantsJson()) return response()->json($unit);
        return view('admin.barcode.scan', compact('unit'));
    }
}
