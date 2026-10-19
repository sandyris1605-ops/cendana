@extends('layouts.admin')
@section('title','Laporan')
@section('content')
@include('admin.partials.header', ['judul'=>'Pusat Laporan', 'sub'=>'Laporan lengkap — unduh sebagai PDF atau Excel (CSV)'])
<div class="grid md:grid-cols-3 gap-4">
@php
$ikon = ['keuangan'=>'fa-coins','okupansi'=>'fa-bed','inventaris'=>'fa-boxes-stacked','pembelian'=>'fa-cart-shopping','penyusutan'=>'fa-chart-line','penggantian'=>'fa-rotate'];
$warna = ['keuangan'=>'bg-emerald-50 text-emerald-600','okupansi'=>'bg-indigo-50 text-indigo-600','inventaris'=>'bg-amber-50 text-amber-600','pembelian'=>'bg-sky-50 text-sky-600','penyusutan'=>'bg-violet-50 text-violet-600','penggantian'=>'bg-rose-50 text-rose-600'];
@endphp
@foreach(['keuangan'=>'Dana masuk vs keluar, saldo periode','okupansi'=>'Okupansi kamar & penghuni','inventaris'=>'Seluruh unit + barcode + lokasi','pembelian'=>'Riwayat pembelian per supplier','penyusutan'=>'Nilai buku & akumulasi susut','penggantian'=>'Jadwal ganti barang berkala'] as $k=>$v)
<a href="{{ route('admin.laporan.show',$k) }}" class="lift bg-white p-5 rounded-xl border border-slate-200 shadow-sm hover:shadow-md hover:border-amber-400 transition group">
<div class="w-11 h-11 rounded-xl {{ $warna[$k] }} flex items-center justify-center text-lg"><i class="fa {{ $ikon[$k] }}"></i></div>
<div class="font-bold mt-3">Laporan {{ ucfirst($k) }}</div>
<div class="text-sm text-slate-500">{{ $v }}</div>
<div class="text-xs text-amber-600 font-semibold mt-3 group-hover:underline">Buka laporan →</div>
</a>
@endforeach
</div>
@endsection
