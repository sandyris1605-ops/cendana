@extends('layouts.admin')
@section('title','Hasil Pencarian')
@section('content')
@include('admin.partials.header', ['judul'=>'Hasil Pencarian', 'sub'=> $q !== '' ? 'Kata kunci: "'.$q.'"' : 'Ketik kata kunci di kotak pencarian atas'])
@if($q === '')
<div class="bg-white p-10 rounded-xl border border-slate-200 text-center text-slate-400">
<i class="fa fa-magnifying-glass text-3xl mb-3 block"></i>Mulai ketik kata kunci (nomor kamar, nama barang, barcode, penghuni, supplier, nomor PO) di kotak pencarian.
</div>
@else
@php
$grup = [
    'kamar' => ['label'=>'Kamar','ikon'=>'fa-bed','route_fn'=>fn($x)=>route('admin.rooms.show',$x),'tampil'=>fn($x)=>'Kamar '.$x->nomor.' — '.optional($x->roomType)->nama,'sub'=>fn($x)=>'Lantai '.$x->lantai.' • '.$x->status],
    'barang' => ['label'=>'Barang','ikon'=>'fa-box','route_fn'=>fn($x)=>route('admin.items.show',$x),'tampil'=>fn($x)=>$x->nama,'sub'=>fn($x)=>($x->category->nama ?? '-').' • umur '.$x->umur_pakai_bulan.' bln'],
    'aset' => ['label'=>'Aset / Barcode','ikon'=>'fa-barcode','route_fn'=>fn($x)=>route('admin.assets.show',$x),'tampil'=>fn($x)=>$x->kode_barcode,'sub'=>fn($x)=>optional($x->item)->nama.' • '.(optional(optional($x->activePlacement)->room)->nomor ?? 'Gudang')],
    'penghuni' => ['label'=>'Penghuni','ikon'=>'fa-users','route_fn'=>fn($x)=>route('admin.tenants.show',$x),'tampil'=>fn($x)=>$x->nama,'sub'=>fn($x)=>($x->telepon ?? '-').' • '.($x->pekerjaan ?? '-')],
    'supplier' => ['label'=>'Supplier','ikon'=>'fa-truck','route_fn'=>fn($x)=>route('admin.suppliers.show',$x),'tampil'=>fn($x)=>$x->nama,'sub'=>fn($x)=>($x->telepon ?? '-')],
    'pembelian' => ['label'=>'Pembelian','ikon'=>'fa-cart-shopping','route_fn'=>fn($x)=>route('admin.purchases.show',$x),'tampil'=>fn($x)=>$x->nomor,'sub'=>fn($x)=>optional($x->supplier)->nama.' • Rp '.number_format($x->total,0,',','.')],
];
@endphp
@php $ada = collect($hasil)->sum(fn($r) => $r->count()); @endphp
@if($ada === 0)
<div class="bg-white p-10 rounded-xl border border-slate-200 text-center text-slate-400">
<i class="fa fa-circle-question text-3xl mb-3 block"></i>Tidak ada hasil untuk "<span class="font-semibold text-slate-600">{{ $q }}</span>".
</div>
@endif
<div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
@php $warna = ['kamar'=>'bg-indigo-50 text-indigo-600','barang'=>'bg-amber-50 text-amber-600','aset'=>'bg-emerald-50 text-emerald-600','penghuni'=>'bg-violet-50 text-violet-600','supplier'=>'bg-sky-50 text-sky-600','pembelian'=>'bg-rose-50 text-rose-600']; @endphp
@foreach($grup as $key => $g)
@if($hasil[$key]->count())
<div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
<div class="flex items-center gap-3 px-4 py-3 border-b border-slate-100 bg-slate-50/60">
<div class="w-8 h-8 rounded-lg {{ $warna[$key] }} flex items-center justify-center"><i class="fa {{ $g['ikon'] }} text-sm"></i></div>
<div class="font-semibold text-sm">{{ $g['label'] }}</div>
<span class="ml-auto text-xs text-slate-400">{{ $hasil[$key]->count() }} hasil</span>
</div>
<div class="divide-y divide-slate-100">
@foreach($hasil[$key] as $x)
<a href="{{ ($g['route_fn'])($x) }}" class="flex items-center justify-between px-4 py-2.5 hover:bg-amber-50/50 transition">
<div class="min-w-0">
<div class="text-sm font-medium truncate">{{ ($g['tampil'])($x) }}</div>
<div class="text-xs text-slate-400 truncate">{{ ($g['sub'])($x) }}</div>
</div>
<i class="fa fa-chevron-right text-xs text-slate-300"></i>
</a>
@endforeach
</div>
</div>
@endif
@endforeach
</div>
@endif
@endsection
