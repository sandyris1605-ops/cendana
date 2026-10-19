@extends('layouts.admin')
@section('title','Detail Penghuni')
@section('content')
@php
$kamarAktif = optional(optional($tenant->activeTenancy)->room);
$jmlAnggota = $tenant->anggota->count();
$jmlKeluarga = 1 + $jmlAnggota;
$aktif = (bool) $tenant->activeTenancy;
@endphp
<a href="{{ route('admin.tenants.index') }}" class="inline-flex items-center gap-2 text-sm text-slate-500 hover:text-slate-800 transition mb-4"><i class="fa fa-arrow-left text-xs"></i>Daftar Penghuni</a>

{{-- ===== Profile Card ===== --}}
<div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 mb-4 penghuni-detail" style="animation-delay:0ms">
<div class="flex flex-wrap items-start gap-5">
  <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-indigo-500 to-violet-600 text-white flex items-center justify-center text-3xl font-bold shadow-sm shrink-0">{{ strtoupper(substr($tenant->nama,0,1)) }}</div>
  <div class="min-w-0 flex-1">
    <div class="flex flex-wrap items-center gap-3">
      <h1 class="text-2xl font-bold tracking-tight">{{ $tenant->nama }}</h1>
      <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1 rounded-full border {{ $aktif ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-slate-100 text-slate-500 border-slate-200' }}"><span class="w-1.5 h-1.5 rounded-full {{ $aktif ? 'bg-emerald-500 animate-pulse' : 'bg-slate-400' }}"></span>{{ $aktif ? 'Sedang Menghuni' : 'Tidak Menghuni' }}</span>
    </div>
    <p class="text-sm text-slate-500 mt-1">{{ $tenant->pekerjaan ?? 'Pekerjaan tidak diisi' }}</p>
    <div class="flex flex-wrap gap-2 mt-4">
      <button type="button" onclick="openEditModal()" class="inline-flex items-center gap-2 bg-amber-500 hover:bg-amber-600 text-slate-900 px-4 py-2 rounded-lg text-sm font-semibold shadow-sm transition"><i class="fa fa-pen text-xs"></i>Edit</button>
      @if(!$aktif)
      <form method="POST" action="{{ route('admin.tenants.destroy',$tenant) }}" class="inline" onsubmit="return konfirmasiHapus('Hapus penghuni {{ $tenant->nama }}?\n\nRiwayat penempatan ikut terhapus permanen.', this)">@csrf @method('DELETE')<button class="inline-flex items-center gap-2 bg-rose-500 hover:bg-rose-600 text-white px-4 py-2 rounded-lg text-sm font-semibold shadow-sm transition"><i class="fa fa-trash text-xs"></i>Hapus</button></form>
      @endif
    </div>
  </div>
</div>

<div class="grid grid-cols-2 md:grid-cols-3 gap-x-4 gap-y-3 mt-6 pt-5 border-t border-slate-100">
  <div><div class="text-[11px] uppercase tracking-wider text-slate-400 font-semibold mb-0.5">No. Identitas</div><div class="text-sm font-medium">{{ $tenant->nik ?: '-' }}</div></div>
  <div><div class="text-[11px] uppercase tracking-wider text-slate-400 font-semibold mb-0.5">Telepon</div><div class="text-sm font-medium">{{ $tenant->telepon ?: '-' }}</div></div>
  <div><div class="text-[11px] uppercase tracking-wider text-slate-400 font-semibold mb-0.5">Kamar Saat Ini</div><div class="text-sm font-medium">{{ $kamarAktif ? 'Kamar '.$kamarAktif->nomor : '—' }}</div></div>
  <div><div class="text-[11px] uppercase tracking-wider text-slate-400 font-semibold mb-0.5">Alamat Asal</div><div class="text-sm font-medium">{{ $tenant->alamat_asal ?: '-' }}</div></div>
  <div><div class="text-[11px] uppercase tracking-wider text-slate-400 font-semibold mb-0.5">Total Penempatan</div><div class="text-sm font-medium">{{ $tenant->tenancies->count() }} kamar</div></div>
  <div><div class="text-[11px] uppercase tracking-wider text-slate-400 font-semibold mb-0.5">Jumlah Keluarga</div><div class="text-sm font-medium">{{ $jmlKeluarga }} orang <span class="text-xs text-slate-400">(1 kepala + {{ $jmlAnggota }} anggota)</span></div></div>
</div>
</div>

<div class="space-y-4">
{{-- ===== Anggota Keluarga ===== --}}
@if($tenant->anggota->count())
<div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm penghuni-detail" style="animation-delay:80ms">
<div class="flex items-center gap-3 mb-4">
<div class="w-10 h-10 rounded-xl bg-violet-50 text-violet-600 flex items-center justify-center"><i class="fa fa-people-roof"></i></div>
<div><div class="font-bold">Anggota Keluarga</div><div class="text-xs text-slate-400">{{ $jmlKeluarga }} orang tinggal serumah (1 kepala + {{ $jmlAnggota }} anggota)</div></div>
</div>
<div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-2">
@foreach($tenant->anggota as $ag)
<div class="flex items-center gap-3 bg-slate-50 border border-slate-100 rounded-xl px-3 py-2.5 hover:border-violet-200 hover:bg-violet-50/40 transition">
<div class="w-9 h-9 rounded-full bg-gradient-to-br from-violet-400 to-indigo-500 text-white flex items-center justify-center text-xs font-bold shrink-0">{{ strtoupper(substr($ag->nama,0,1)) }}</div>
<div class="min-w-0 flex-1"><div class="font-semibold text-sm truncate">{{ $ag->nama }}</div><div class="text-xs text-slate-400 capitalize">{{ str_replace('_',' ',$ag->hubungan) }}@if($ag->umur !== null) • {{ $ag->umur }} th @endif</div></div>
</div>
@endforeach
</div>
</div>
@endif
{{-- ===== Riwayat Penempatan ===== --}}
@forelse($tenant->tenancies as $t)
<div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm penghuni-detail hover:shadow-md transition" style="animation-delay:{{ 160 + $loop->index * 50 }}ms">
<div class="flex flex-wrap items-center justify-between gap-3">
<div class="flex items-center gap-3">
<div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center"><i class="fa fa-door-open"></i></div>
<div>
<div class="font-bold">Kamar {{ optional($t->room)->nomor ?? '-' }} <span class="text-xs font-normal text-slate-400">({{ optional(optional($t->room)->roomType)->nama ?? '-' }})</span></div>
<div class="text-xs text-slate-400 mt-0.5">
<span class="inline-flex items-center gap-1"><i class="fa fa-right-to-bracket text-[9px]"></i>Masuk {{ \Illuminate\Support\Carbon::parse($t->tanggal_masuk)->translatedFormat('d M Y') }}</span>
@if($t->tanggal_keluar)<span class="inline-flex items-center gap-1 ml-2"><i class="fa fa-right-from-bracket text-[9px]"></i>Keluar {{ \Illuminate\Support\Carbon::parse($t->tanggal_keluar)->translatedFormat('d M Y') }}</span>@endif
</div>
</div>
</div>
<span class="text-[11px] px-2.5 py-1 rounded-full border font-semibold {{ $t->status=='aktif' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-slate-100 text-slate-500 border-slate-200' }}">{{ ucfirst($t->status) }}</span>
</div>
@if($t->status=='aktif')
<form method="POST" action="{{ route('admin.tenancies.destroy',$t) }}" class="inline mt-3" onsubmit="return konfirmasiHapus('Penghuni {{ $tenant->nama }} keluar dari Kamar {{ optional($t->room)->nomor }}?', this)">@csrf @method('DELETE')<button class="inline-flex items-center gap-1.5 text-xs font-semibold text-rose-600 hover:text-white hover:bg-rose-500 border border-rose-200 hover:border-rose-500 px-3 py-1.5 rounded-lg transition"><i class="fa fa-right-from-bracket"></i>Penghuni Keluar</button></form>
@endif
</div>
@empty
<div class="bg-white p-10 rounded-2xl border border-slate-200 text-center text-slate-400 penghuni-detail" style="animation-delay:80ms"><i class="fa fa-file-contract text-2xl mb-2 block"></i>Belum ada penempatan.</div>
@endforelse
</div>
{{-- ===== Modal Edit Penghuni ===== --}}
<div id="modalEdit" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
<div class="absolute inset-0 bg-slate-900/60" onclick="closeEditModal()"></div>
<div class="relative bg-white rounded-2xl shadow-xl w-full max-w-lg max-h-[90vh] overflow-y-auto p-6">
<div class="flex items-center justify-between mb-4">
<div class="font-bold text-lg">Edit Penghuni</div>
<button type="button" onclick="closeEditModal()" class="w-8 h-8 inline-flex items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition"><i class="fa fa-xmark"></i></button>
</div>
<form method="POST" action="{{ route('admin.tenants.update',$tenant) }}">
@csrf @method('PUT')
@include('admin.tenants._form-fields')
<div class="flex gap-2 mt-5 pt-4 border-t border-slate-100">
<button class="bg-amber-500 hover:bg-amber-600 text-slate-900 px-5 py-2 rounded-lg text-sm font-semibold shadow-sm transition"><i class="fa fa-check mr-1 text-xs"></i>Simpan</button>
<button type="button" onclick="closeEditModal()" class="px-5 py-2 rounded-lg text-sm border border-slate-200 text-slate-600 hover:bg-slate-50 transition">Batal</button>
</div>
</form>
</div>
</div>
<script>
function openEditModal(){document.getElementById('modalEdit').classList.remove('hidden');document.body.classList.add('overflow-hidden');}
function closeEditModal(){document.getElementById('modalEdit').classList.add('hidden');document.body.classList.remove('overflow-hidden');}
document.addEventListener('keydown',function(e){if(e.key==='Escape')closeEditModal();});
</script>
@if($errors->any())
<script>document.addEventListener('DOMContentLoaded',openEditModal);</script>
@endif
<style>
@keyframes penghuniDetailNaik { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
.penghuni-detail { opacity: 0; animation: penghuniDetailNaik .4s ease both; }
@media (prefers-reduced-motion: reduce) { .penghuni-detail { animation: none; opacity: 1; } }
</style>
@endsection
