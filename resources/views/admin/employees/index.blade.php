@extends('layouts.admin')
@section('title','Karyawan')
@section('content')
@include('admin.partials.header', ['judul'=>'Karyawan', 'sub'=>'Tim pengelola Cendana Resident', 'ctaHref'=>route('admin.employees.create'), 'ctaText'=>'Tambah Karyawan'])
@include('admin.partials.search', ['placeholder'=>'Cari nama / jabatan...'])
@if($errors->any())
<div class="bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-xl mb-4 text-sm"><i class="fa fa-circle-exclamation mr-1"></i>{{ $errors->first() }}</div>
@endif
<div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-x-auto">
<table class="w-full text-sm">
<thead><tr class="bg-slate-50 text-left text-xs uppercase tracking-wider text-slate-500 border-b border-slate-200">
<th class="px-4 py-3">Nama</th><th class="px-4 py-3">Jabatan</th><th class="px-4 py-3">Telepon</th><th class="px-4 py-3">Gaji</th><th class="px-4 py-3 text-right">Aksi</th>
</tr></thead>
<tbody class="divide-y divide-slate-100">
@forelse($employees as $e)
<tr class="hover:bg-slate-50 transition">
<td class="px-4 py-3">
<div class="flex items-center gap-3">
<div class="w-8 h-8 rounded-full bg-amber-100 text-amber-700 flex items-center justify-center text-xs font-bold">{{ strtoupper(substr($e->nama,0,1)) }}</div>
<div><span class="font-semibold">{{ $e->nama }}</span>
<br><span class="text-[10px] font-bold uppercase tracking-wider px-1.5 py-0.5 rounded-full {{ ($e->status ?? 'aktif') === 'aktif' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">{{ ($e->status ?? 'aktif') === 'aktif' ? 'Aktif' : 'Nonaktif' }}</span></div>
</div>
</td>
<td class="px-4 py-3">{{ $e->jabatan ?? '-' }}</td>
<td class="px-4 py-3 text-slate-500">{{ $e->telepon ?? '-' }}</td>
<td class="px-4 py-3 font-semibold">Rp {{ number_format($e->gaji,0,',','.') }}</td>
<td class="px-4 py-3 text-right whitespace-nowrap">
<a href="{{ route('admin.employees.edit',$e) }}" class="text-slate-400 hover:text-amber-600 transition"><i class="fa fa-pen mr-1"></i>Edit</a>

        @if(($e->status ?? 'aktif') !== 'aktif')
        <form method="POST" action="{{ route('admin.employees.destroy',$e) }}" class="inline ml-2" onsubmit="return confirm('Hapus {{ $e->nama }}?')">@csrf @method('DELETE')<button class="text-slate-400 hover:text-rose-600 transition" title="Hapus karyawan nonaktif"><i class="fa fa-trash mr-1"></i>Hapus</button></form>
        @endif

</td>
</tr>
@empty
<tr><td colspan="5" class="px-4 py-10 text-center text-slate-400"><i class="fa fa-id-card text-2xl mb-2 block"></i>Tidak ada karyawan yang cocok.</td></tr>
@endforelse
</tbody>
</table>
</div>
@include('admin.partials.pager', ['paginator'=>$employees])
@endsection
