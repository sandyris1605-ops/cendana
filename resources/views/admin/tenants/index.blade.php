@extends('layouts.admin')
@section('title','Penghuni')
@section('content')
@include('admin.partials.header', ['judul'=>'Penghuni', 'sub'=>'Data penghuni dan riwayat penempatan', 'ctaHref'=>route('admin.tenants.create'), 'ctaText'=>'Tambah Penghuni'])

{{-- Kartu statistik ringkas --}}
@php
$kartu = [
    ['label'=>'Total Penghuni','val'=>$stat['total'],'ikon'=>'fa-users','tintTxt'=>'text-indigo-600','tintBg'=>'bg-indigo-50'],
    ['label'=>'Sedang Menghuni','val'=>$stat['menghuni'],'ikon'=>'fa-user-check','tintTxt'=>'text-emerald-600','tintBg'=>'bg-emerald-50'],
    ['label'=>'Tidak Menghuni','val'=>$stat['kosong'],'ikon'=>'fa-user-slash','tintTxt'=>'text-slate-500','tintBg'=>'bg-slate-100'],
    ['label'=>'Total Jiwa / Keluarga','val'=>$stat['jiwa'],'ikon'=>'fa-people-roof','tintTxt'=>'text-violet-600','tintBg'=>'bg-violet-50','sub'=>$stat['total'].' kepala + '.$stat['anggota'].' anggota'],
];
@endphp
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 md:gap-4 mb-5">
@foreach($kartu as $i => $k)
<div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 flex items-center gap-3 penghuni-stat" style="animation-delay:{{ $i * 60 }}ms">
  <div class="w-11 h-11 rounded-xl {{ $k['tintBg'] }} {{ $k['tintTxt'] }} flex items-center justify-center shrink-0"><i class="fa {{ $k['ikon'] }}"></i></div>
  <div class="min-w-0">
    <div class="text-[11px] uppercase tracking-wider text-slate-400 font-semibold">{{ $k['label'] }}</div>
    <div class="text-2xl font-bold tracking-tight">{{ $k['val'] }} <span class="text-xs font-medium text-slate-400">orang</span></div>
    @if(!empty($k['sub']))<div class="text-[10px] text-slate-400 mt-0.5">{{ $k['sub'] }}</div>@endif
  </div>
</div>
@endforeach
</div>

@include('admin.partials.search', ['placeholder'=>'Cari nama / NIK / telepon...'])
<div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-x-auto">
<table class="w-full text-sm">
<thead><tr class="bg-gradient-to-r from-slate-50 to-slate-100 text-left text-xs uppercase tracking-wider text-slate-500 border-b border-slate-200">
<th class="px-4 py-3">Nama</th><th class="px-4 py-3">Telepon</th><th class="px-4 py-3">Kamar</th>
</tr></thead>
<tbody class="divide-y divide-slate-100">
@forelse($tenants as $idx => $t)
@php $menghuni = (bool) optional(optional($t->activeTenancy)->room)->id; @endphp
<tr class="penghuni-baris hover:bg-amber-50/40 transition group" style="animation-delay:{{ min($idx,15) * 45 }}ms">
<td class="px-4 py-3">
<div class="flex items-center gap-3">
<div class="w-8 h-8 rounded-xl bg-slate-900 text-amber-400 flex items-center justify-center text-xs font-bold shrink-0">{{ strtoupper(substr($t->nama,0,1)) }}</div>
<a href="{{ route('admin.tenants.show',$t) }}" title="Lihat detail {{ $t->nama }}" class="font-semibold text-indigo-600 group-hover:text-amber-600 transition">{{ $t->nama }}</a>
</div>
</td>
<td class="px-4 py-3 text-slate-500">{{ $t->telepon ?? '-' }}</td>
<td class="px-4 py-3">@if($menghuni)<span class="inline-flex items-center gap-1.5 text-[11px] px-2.5 py-1 rounded-full border font-medium bg-emerald-50 text-emerald-700 border-emerald-200"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>{{ optional(optional($t->activeTenancy)->room)->nomor }}</span>@else<span class="text-[11px] px-2.5 py-1 rounded-full border bg-slate-100 text-slate-500 border-slate-200">Tidak menghuni</span>@endif</td>
</tr>
@empty
<tr><td colspan="3" class="px-4 py-10 text-center text-slate-400"><i class="fa fa-users text-2xl mb-2 block"></i>Tidak ada penghuni yang cocok.</td></tr>
@endforelse
</tbody>
</table>
</div>
@include('admin.partials.pager', ['paginator'=>$tenants])

<style>
@keyframes penghuniNaik { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
.penghuni-stat { opacity: 0; animation: penghuniNaik .4s ease both; }
.penghuni-baris { opacity: 0; animation: penghuniNaik .35s ease both; }
@media (prefers-reduced-motion: reduce) { .penghuni-stat, .penghuni-baris { animation: none; opacity: 1; } }
</style>
@endsection
