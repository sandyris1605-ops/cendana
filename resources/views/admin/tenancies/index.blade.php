@extends('layouts.admin')
@section('title','Hunian')
@section('content')
@include('admin.partials.header', ['judul'=>'Penempatan Hunian', 'sub'=>'Kelola penghuni kamar — tanpa tagihan'])

{{-- Kartu statistik ringkas --}}
@php
$kartu = [
    ['label'=>'Total Penempatan','val'=>$stat['total'],'ikon'=>'fa-file-contract','tintTxt'=>'text-indigo-600','tintBg'=>'bg-indigo-50'],
    ['label'=>'Aktif','val'=>$stat['aktif'],'ikon'=>'fa-circle-check','tintTxt'=>'text-emerald-600','tintBg'=>'bg-emerald-50'],
    ['label'=>'Selesai','val'=>$stat['selesai'],'ikon'=>'fa-clock-rotate-left','tintTxt'=>'text-slate-500','tintBg'=>'bg-slate-100'],
];
@endphp
<div class="grid grid-cols-2 lg:grid-cols-3 gap-3 md:gap-4 mb-5">
@foreach($kartu as $i => $k)
<div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 flex items-center gap-3 hunian-stat" style="animation-delay:{{ $i * 60 }}ms">
  <div class="w-11 h-11 rounded-xl {{ $k['tintBg'] }} {{ $k['tintTxt'] }} flex items-center justify-center shrink-0"><i class="fa {{ $k['ikon'] }}"></i></div>
  <div class="min-w-0">
    <div class="text-[11px] uppercase tracking-wider text-slate-400 font-semibold">{{ $k['label'] }}</div>
    <div class="text-2xl font-bold tracking-tight">{{ $k['val'] }}</div>
  </div>
</div>
@endforeach
</div>

@include('admin.partials.search', ['placeholder'=>'Cari nama penghuni / nomor kamar...'])
<div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-x-auto">
<table class="w-full text-sm">
<thead><tr class="bg-gradient-to-r from-slate-50 to-slate-100 text-left text-xs uppercase tracking-wider text-slate-500 border-b border-slate-200">
<th class="px-4 py-3">Penghuni</th><th class="px-4 py-3">Kamar</th><th class="px-4 py-3">Masuk</th><th class="px-4 py-3">Status</th><th class="px-4 py-3 text-right">Aksi</th>
</tr></thead>
<tbody class="divide-y divide-slate-100">
@php $tbadge = ['aktif'=>'bg-emerald-50 text-emerald-700 border-emerald-200','selesai'=>'bg-slate-100 text-slate-500 border-slate-200','berhenti'=>'bg-rose-50 text-rose-600 border-rose-200'];
$tdot = ['aktif'=>'bg-emerald-500','selesai'=>'bg-slate-400','berhenti'=>'bg-rose-500'];
$tkedip = ['aktif']; @endphp
@forelse($tenancies as $idx => $t)
<tr class="hunian-baris hover:bg-amber-50/40 transition group" style="animation-delay:{{ min($idx,15) * 45 }}ms">
<td class="px-4 py-3">
<div class="flex items-center gap-3">
<div class="w-8 h-8 rounded-xl bg-slate-900 text-amber-400 flex items-center justify-center text-xs font-bold shrink-0">{{ strtoupper(substr(optional($t->tenant)->nama ?? '-',0,1)) }}</div>
<a href="{{ route('admin.tenancies.show',$t) }}" title="Kelola hunian" class="font-semibold text-indigo-600 group-hover:text-amber-600 transition">{{ optional($t->tenant)->nama ?? '-' }}</a>
</div>
</td>
<td class="px-4 py-3"><span class="text-[11px] px-2 py-0.5 rounded-full bg-slate-100 text-slate-600 border border-slate-200">Kamar {{ optional($t->room)->nomor ?? '-' }}</span></td>
<td class="px-4 py-3 text-slate-500">{{ \Illuminate\Support\Carbon::parse($t->tanggal_masuk)->translatedFormat('d M Y') }}</td>
<td class="px-4 py-3"><span class="inline-flex items-center gap-1.5 text-[11px] px-2.5 py-1 rounded-full border font-medium {{ $tbadge[$t->status] ?? 'bg-slate-100 text-slate-600 border-slate-200' }}"><span class="w-1.5 h-1.5 rounded-full {{ $tdot[$t->status] ?? 'bg-slate-400' }} @if(in_array($t->status,$tkedip)) animate-pulse @endif"></span>{{ ucfirst($t->status) }}</span></td>
<td class="px-4 py-3 text-right whitespace-nowrap">
@php $menuHunian = array_values(array_filter([
['tipe'=>'link','label'=>'Kelola','ikon'=>'fa-eye','url'=>route('admin.tenancies.show',$t)],
$t->status=='aktif' ? ['tipe'=>'hapus','label'=>'Keluar','ikon'=>'fa-right-from-bracket','url'=>route('admin.tenancies.destroy',$t),'pesan'=>'Penghuni '.(optional($t->tenant)->nama ?? '-').' keluar dari Kamar '.(optional($t->room)->nomor ?? '-').'?'] : null,
])); @endphp
@include('admin.partials.aksi',['menu'=>$menuHunian])
</td>
</tr>
@empty
<tr><td colspan="5" class="px-4 py-10 text-center text-slate-400"><i class="fa fa-file-contract text-2xl mb-2 block"></i>Tidak ada penempatan yang cocok.</td></tr>
@endforelse
</tbody>
</table>
</div>
@include('admin.partials.pager', ['paginator'=>$tenancies])

<style>
@keyframes hunianNaik { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
.hunian-stat { opacity: 0; animation: hunianNaik .4s ease both; }
.hunian-baris { opacity: 0; animation: hunianNaik .35s ease both; }
@media (prefers-reduced-motion: reduce) { .hunian-stat, .hunian-baris { animation: none; opacity: 1; } }
</style>
@endsection
