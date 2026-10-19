@extends('layouts.front')
@section('title','Scan '.$unit->kode_barcode)
@section('content')
<div class="max-w-lg mx-auto px-4 py-10">
<div class="bg-cream border border-pine/10 p-6 rounded-[20px] shadow-card">
<div class="text-xs font-bold text-fern">CENDANA RESIDENT • HASIL SCAN INVENTARIS</div>
<h1 class="font-mono font-bold text-2xl mt-1 text-pine">{{ $unit->kode_barcode }}</h1>
<div class="mt-2 font-bold">{{ optional($unit->item)->nama }}</div>
<div class="text-sm">Lokasi: {{ optional(optional($unit->activePlacement)->room)->nomor ?? 'Gudang' }} • Kondisi: {{ $unit->kondisi=='perlu_diganti' ? 'PERLU DIGANTI' : $unit->kondisi }} • Beli: {{ $unit->tanggal_beli }}</div>
<div class="mt-3 flex justify-center"><img src="data:image/png;base64,{!! \Milon\Barcode\Facades\DNS1DFacade::getBarcodePNG($unit->kode_barcode,'C128',2,60) !!}" alt="{{ $unit->kode_barcode }}" style="max-width:100%;height:auto"></div>
<div class="mt-2 text-center"><img src="data:image/png;base64,{!! \Milon\Barcode\Facades\DNS2DFacade::getBarcodePNG(url('/scan/'.$unit->kode_barcode),'QRCODE',4,4) !!}" alt="QR" style="max-width:100%;height:auto"></div>
<div class="text-xs text-center text-stone-500 mt-1">Scan QR untuk buka halaman ini</div>
</div>
</div>
@endsection
