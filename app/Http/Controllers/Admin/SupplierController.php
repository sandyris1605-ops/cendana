<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index(Request $r) {
        $q = \App\Models\Supplier::withCount('purchases');
        if ($r->search) $q->where('nama','like','%'.$r->search.'%')->orWhere('telepon','like','%'.$r->search.'%');
        return view('admin.suppliers.index', ['suppliers'=>$q->orderBy('nama')->paginate(20)->withQueryString()]);
    }
    public function create() { return view('admin.suppliers.form', ['supplier'=>new \App\Models\Supplier()]); }
    public function store(Request $r) {
        $r->validate(['nama'=>'required','no_pajak'=>'nullable|string|max:100']);
        \App\Models\Supplier::create($r->all());
        return redirect()->route('admin.suppliers.index')->with('ok','Supplier ditambah.');
    }
    public function show(\App\Models\Supplier $supplier) {
        $supplier->load('purchases.items')->loadCount('purchases');
        return view('admin.suppliers.show', compact('supplier'));
    }
    public function edit(\App\Models\Supplier $supplier) { return view('admin.suppliers.form', compact('supplier')); }
    public function update(Request $r, \App\Models\Supplier $supplier) {
        $r->validate(['nama'=>'required','no_pajak'=>'nullable|string|max:100']);
        $supplier->update($r->all());
        return redirect()->route('admin.suppliers.index')->with('ok','Diupdate.');
    }
    public function destroy(\App\Models\Supplier $supplier) {
        if ($supplier->purchases()->exists()) return back()->withErrors(['hapus'=>'Supplier '.$supplier->nama.' tidak bisa dihapus karena masih ada '.$supplier->purchases()->count().' riwayat pembelian.']);
        $supplier->delete(); return redirect()->route('admin.suppliers.index')->with('ok','Dihapus.');
    }
}
