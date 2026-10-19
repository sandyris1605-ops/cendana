<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AssetUnit;
use App\Models\Item;
use App\Models\Purchase;
use App\Models\Room;
use App\Models\Supplier;
use App\Models\Tenant;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function cari(Request $r) {
        $q = trim((string) $r->q);
        $hasil = [];
        if ($q !== '') {
            $like = '%'.$q.'%';
            $hasil = [
                'kamar' => Room::with('roomType')->where('nomor','like',$like)->orderBy('nomor')->limit(8)->get(),
                'barang' => Item::with('category')->where('nama','like',$like)->orWhereHas('category', fn ($qq) => $qq->where('nama','like',$like))->orderBy('nama')->limit(8)->get(),
                'aset' => AssetUnit::with(['item','activePlacement.room'])
                    ->where('kode_barcode','like',$like)
                    ->orWhereHas('item', fn ($qq) => $qq->where('nama','like',$like))->orderBy('kode_barcode')->limit(8)->get(),
                'penghuni' => Tenant::where('nama','like',$like)->orWhere('nik','like',$like)->orWhere('telepon','like',$like)->orderBy('nama')->limit(8)->get(),
                'supplier' => Supplier::where('nama','like',$like)->orWhere('telepon','like',$like)->orderBy('nama')->limit(8)->get(),
                'pembelian' => Purchase::with('supplier')->where('nomor','like',$like)->orderByDesc('id')->limit(8)->get(),
            ];
        }
        return view('admin.search', compact('q','hasil'));
    }
}
