@extends('layouts.admin')
@section('title','Barang')
@section('content')
@include('admin.partials.header', ['judul'=>'Katalog Barang', 'sub'=>'Daftar barang + umur pakai untuk penggantian berkala', 'ctaSlot'=>'<button type="button" onclick="openBarangModal()" class="inline-flex items-center gap-2 bg-amber-500 hover:bg-amber-600 text-slate-900 px-4 py-2 rounded-lg text-sm font-semibold shadow-sm transition"><i class="fa fa-plus text-xs"></i>Tambah Barang</button>'])

{{-- Kartu statistik ringkas (animasi fade-slide berurutan) --}}
@php
$kartu = [
    ['label'=>'Jenis Barang','val'=>$stat['jenis'],'ikon'=>'fa-box','tintTxt'=>'text-indigo-600','tintBg'=>'bg-indigo-50'],
    ['label'=>'Total Unit','val'=>$stat['unit'],'ikon'=>'fa-barcode','tintTxt'=>'text-sky-600','tintBg'=>'bg-sky-50'],
    ['label'=>'Perlu Diganti','val'=>$stat['ganti'],'ikon'=>'fa-triangle-exclamation','tintTxt'=>'text-rose-600','tintBg'=>'bg-rose-50'],
    ['label'=>'Kategori','val'=>$stat['kategori'],'ikon'=>'fa-tags','tintTxt'=>'text-amber-600','tintBg'=>'bg-amber-50'],
];
@endphp
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 md:gap-4 mb-5">
@foreach($kartu as $i => $k)
<div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 flex items-center gap-3 barang-stat" style="animation-delay:{{ $i * 60 }}ms">
  <div class="w-11 h-11 rounded-xl {{ $k['tintBg'] }} {{ $k['tintTxt'] }} flex items-center justify-center shrink-0"><i class="fa {{ $k['ikon'] }}"></i></div>
  <div class="min-w-0">
    <div class="text-[11px] uppercase tracking-wider text-slate-400 font-semibold">{{ $k['label'] }}</div>
    <div class="text-2xl font-bold tracking-tight">{{ $k['val'] }}</div>
  </div>
</div>
@endforeach
</div>

@include('admin.partials.search', ['placeholder'=>'Cari nama / kategori barang...'])
<div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-x-auto">
 <table class="w-full text-sm">
 <thead><tr class="bg-gradient-to-r from-slate-50 to-slate-100 text-left text-xs uppercase tracking-wider text-slate-500 border-b border-slate-200">
 <th class="px-4 py-3">Nama</th><th class="px-4 py-3">Kategori</th><th class="px-4 py-3">Estimasi Harga</th><th class="px-4 py-3">Umur Pakai</th><th class="px-4 py-3">Unit</th>
 </tr></thead>
 <tbody class="divide-y divide-slate-100">
 @forelse($items as $idx => $i)
 <tr class="barang-baris hover:bg-amber-50/40 transition group" style="animation-delay:{{ min($idx,15) * 45 }}ms">
 <td class="px-4 py-3"><a href="{{ route('admin.items.show',$i) }}" title="Lihat detail {{ $i->nama }}" class="inline-flex items-center gap-2 font-semibold text-indigo-600 group-hover:text-amber-600 transition"><span class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 group-hover:bg-amber-100 group-hover:text-amber-600 inline-flex items-center justify-center transition"><i class="fa fa-box text-xs"></i></span>{{ $i->nama }}</a></td>
 <td class="px-4 py-3"><span class="text-[11px] px-2 py-0.5 rounded-full bg-slate-100 text-slate-600 border border-slate-200">{{ $i->category->nama ?? '-' }}</span></td>
 <td class="px-4 py-3">Rp {{ number_format($i->harga_estimasi,0,',','.') }}</td>
 <td class="px-4 py-3">{{ $i->umur_pakai_bulan }} bln</td>
 <td class="px-4 py-3"><span class="text-[11px] px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-600 border border-indigo-200 font-semibold">{{ $i->units_count }} unit</span></td>
 </tr>
 @empty
 <tr><td colspan="5" class="px-4 py-10 text-center text-slate-400"><i class="fa fa-box text-2xl mb-2 block"></i>Tidak ada barang yang cocok.</td></tr>
 @endforelse
 </tbody>
 </table>
 </div>
 @include('admin.partials.pager', ['paginator'=>$items])

{{-- ===== Modal Tambah Barang ===== --}}
<div id="modalBarang" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
<div class="absolute inset-0 bg-slate-900/60" onclick="closeBarangModal()"></div>
<div class="relative bg-white rounded-2xl shadow-xl w-full max-w-lg max-h-[90vh] overflow-y-auto p-6">
<div class="flex items-center justify-between mb-4">
<div class="font-bold text-lg">Tambah Barang</div>
<button type="button" onclick="closeBarangModal()" class="w-8 h-8 inline-flex items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition"><i class="fa fa-xmark"></i></button>
</div>
<form method="POST" action="{{ route('admin.items.store') }}">
@csrf
@include('admin.items._form-fields', ['item' => new \App\Models\Item()])
<div class="flex gap-2 mt-6 pt-4 border-t border-slate-100">
<button class="bg-amber-500 hover:bg-amber-600 text-slate-900 px-5 py-2 rounded-lg text-sm font-semibold shadow-sm transition"><i class="fa fa-check mr-1 text-xs"></i>Simpan</button>
<button type="button" onclick="closeBarangModal()" class="px-5 py-2 rounded-lg text-sm border border-slate-200 text-slate-600 hover:bg-slate-50 transition">Batal</button>
</div>
</form>
</div>
</div>
<script>
function openBarangModal(){document.getElementById('modalBarang').classList.remove('hidden');document.body.classList.add('overflow-hidden');}
function closeBarangModal(){document.getElementById('modalBarang').classList.add('hidden');document.body.classList.remove('overflow-hidden');}
document.addEventListener('keydown',function(e){if(e.key==='Escape')closeBarangModal();});
</script>
@if($errors->any())
<script>document.addEventListener('DOMContentLoaded',openBarangModal);</script>
@endif

<style>
@keyframes barangNaik { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
.barang-stat { opacity: 0; animation: barangNaik .4s ease both; }
.barang-baris { opacity: 0; animation: barangNaik .35s ease both; }
@media (prefers-reduced-motion: reduce) { .barang-stat, .barang-baris { animation: none; opacity: 1; } }
</style>
@endsection
