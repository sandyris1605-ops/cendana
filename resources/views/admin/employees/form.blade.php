@extends('layouts.admin')
@section('title','Form Karyawan')
@section('content')
@include('admin.partials.header', ['judul'=>($employee->exists ? 'Edit' : 'Tambah').' Karyawan', 'sub'=>'Data tim pengelola', 'ctaHref'=>route('admin.employees.index'), 'ctaText'=>'Kembali', 'ctaIcon'=>'fa-arrow-left'])
<form method="POST" action="{{ $employee->exists ? route('admin.employees.update',$employee) : route('admin.employees.store') }}" class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm max-w-lg">
@csrf @if($employee->exists) @method('PUT') @endif
<div class="space-y-4">
<div><label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">Nama</label><input name="nama" value="{{ old('nama',$employee->nama) }}" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 outline-none" required></div>
<div><label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">Jabatan</label><input name="jabatan" value="{{ old('jabatan',$employee->jabatan) }}" placeholder="mis. Kebersihan, Keamanan" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 outline-none"></div>
<div class="grid grid-cols-2 gap-4">
<div><label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">Telepon</label><input name="telepon" value="{{ old('telepon',$employee->telepon) }}" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 outline-none"></div>
<div><label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">Gaji (Rp)</label><input type="number" name="gaji" value="{{ old('gaji',$employee->gaji) }}" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 outline-none"></div>
</div>
<div>
<label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">Status</label>
<select name="status" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm bg-white focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 outline-none">
<option value="aktif" @selected(old('status', $employee->status ?: 'aktif') === 'aktif')>Aktif — tidak bisa dihapus</option>
<option value="nonaktif" @selected(old('status', $employee->status) === 'nonaktif')>Nonaktif — bisa dihapus</option>
</select>
<p class="text-xs text-slate-400 mt-1.5"><i class="fa fa-circle-info mr-1"></i>Karyawan aktif tidak bisa dihapus — nonaktifkan dulu bila ingin menghapus.</p>
</div>
</div>
<div class="flex gap-2 mt-6 pt-4 border-t border-slate-100">
<button class="bg-amber-500 hover:bg-amber-600 text-slate-900 px-5 py-2 rounded-lg text-sm font-semibold shadow-sm transition"><i class="fa fa-check mr-1 text-xs"></i>Simpan</button>
<a href="{{ route('admin.employees.index') }}" class="px-5 py-2 rounded-lg text-sm border border-slate-200 text-slate-600 hover:bg-slate-50 transition">Batal</a>
</div>
</form>
@endsection
