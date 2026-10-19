<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    public function index(Request $r) {
        $q = \App\Models\Item::with('category')->withCount('units');
        if ($r->search) {
            $q->where(function ($w) use ($r) {
                $w->where('nama', 'like', '%'.$r->search.'%')
                    ->orWhereHas('category', function ($c) use ($r) {
                        $c->where('nama', 'like', '%'.$r->search.'%');
                    });
            });
        }
        $items = $q->orderBy('nama')->paginate(20)->withQueryString();
        $stat = [
            'jenis' => \App\Models\Item::count(),
            'unit' => \App\Models\AssetUnit::count(),
            'ganti' => \App\Models\AssetUnit::where('kondisi','perlu_diganti')->count(),
            'kategori' => \App\Models\Category::count(),
        ];
        return view('admin.items.index', compact('items','stat'));
    }
    public function create() { return view('admin.items.form',['item'=>new \App\Models\Item()]); }
    public function store(Request $request) {
        $request->merge(['kategori_id' => $request->input('kategori_id') === '' ? null : $request->input('kategori_id')]);
        $request->validate([
            'nama' => 'required',
            'kategori_id' => 'nullable|integer|exists:categories,id',
            'umur_pakai_bulan' => 'required|integer|min:1',
        ]);
        \App\Models\Item::create($request->all());
        return redirect()->route('admin.items.index')->with('ok','Barang ditambah.');
    }
    public function show(\App\Models\Item $item) {
        $item->load('units.activePlacement.room', 'category');
        return view('admin.items.show', compact('item'));
    }
    public function edit(\App\Models\Item $item) { return view('admin.items.form', compact('item')); }
    public function update(Request $request, \App\Models\Item $item) {
        $request->merge(['kategori_id' => $request->input('kategori_id') === '' ? null : $request->input('kategori_id')]);
        $request->validate([
            'kategori_id' => 'nullable|integer|exists:categories,id',
        ]);
        $item->update($request->all());
        return redirect()->route('admin.items.index')->with('ok','Barang diupdate.');
    }
    public function destroy(\App\Models\Item $item) {
        $unit = $item->units()->count();
        if ($unit > 0) return back()->with('ok', 'Tidak dapat dihapus karena masih memiliki '.$unit.' unit (barcode aktif). Hapus unitnya dulu di menu Aset/Pembelian.');
        if ($item->purchaseItems()->exists()) return back()->with('ok', 'Tidak dapat dihapus karena tercatat di riwayat pembelian.');
        $item->delete();
        return redirect()->route('admin.items.index')->with('ok', 'Barang dihapus.');
    }
}
