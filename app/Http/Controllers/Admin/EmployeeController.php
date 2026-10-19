<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function index(Request $r) {
        $q = \App\Models\Employee::query();
        if ($r->search) $q->where('nama','like','%'.$r->search.'%')->orWhere('jabatan','like','%'.$r->search.'%');
        return view('admin.employees.index', ['employees'=>$q->orderBy('nama')->paginate(20)->withQueryString()]);
    }
    public function create() { return view('admin.employees.form', ['employee'=>new \App\Models\Employee()]); }
    public function store(Request $r) {
        $r->validate([
            'nama' => 'required',
            'status' => 'nullable|in:aktif,nonaktif',
        ]);
        \App\Models\Employee::create($r->all() + ['status' => $r->input('status') ?: 'aktif']);
        return redirect()->route('admin.employees.index')->with('ok','Karyawan ditambah.');
    }
    public function edit(\App\Models\Employee $employee) { return view('admin.employees.form', compact('employee')); }
    public function update(Request $r, \App\Models\Employee $employee) {
        $r->validate([
            'status' => 'nullable|in:aktif,nonaktif',
        ]);
        $employee->update($r->all() + ['status' => $r->input('status') ?: $employee->status]);
        return redirect()->route('admin.employees.index')->with('ok','Diupdate.');
    }
    public function destroy(\App\Models\Employee $employee) {
        // Alur: nonaktifkan dulu baru boleh hapus — karyawan aktif tidak boleh dihapus.
        if ($employee->status === 'aktif') {
            return back()->withErrors(['kunci' => 'Karyawan aktif tidak bisa dihapus. Nonaktifkan dulu ' . $employee->nama . ' (Edit → Status: Nonaktif), baru bisa dihapus.']);
        }
        $employee->delete();
        return back()->with('ok','Dihapus.');
    }
}
