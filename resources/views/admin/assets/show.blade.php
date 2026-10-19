@extends('layouts.admin')
@section('title','Detail Aset')
@section('content')
@include('admin.partials.header', ['judul'=>'Aset '.$asset->kode_barcode, 'sub'=>optional($asset->item)->nama.' • '.(optional(optional($asset->activePlacement)->room)->nomor ?? 'Gudang')])
<div class="grid md:grid-cols-3 gap-4 mb-4">
<div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
<div class="text-xs uppercase tracking-wider text-slate-400 mb-1">Harga Perolehan</div>
<div class="text-xl font-bold">Rp {{ number_format($asset->harga_perolehan,0,',','.') }}</div>
<div class="text-xs text-slate-400 mt-1">Beli {{ \Illuminate\Support\Carbon::parse($asset->tanggal_beli)->translatedFormat('d M Y') }}</div>
</div>
<div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
<div class="text-xs uppercase tracking-wider text-slate-400 mb-1">Nilai Buku</div>
<div class="text-xl font-bold text-indigo-600">Rp {{ number_format($asset->nilai_buku,0,',','.') }}</div>
<div class="text-xs text-slate-400 mt-1">Setelah penyusutan</div>
</div>
<div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
<div class="text-xs uppercase tracking-wider text-slate-400 mb-1">Rencana Ganti</div>
<div class="text-xl font-bold {{ $asset->tanggal_ganti_rencana && $asset->tanggal_ganti_rencana <= now()->addDays(90) ? 'text-rose-600' : '' }}">{{ $asset->tanggal_ganti_rencana ? \Illuminate\Support\Carbon::parse($asset->tanggal_ganti_rencana)->translatedFormat('d M Y') : '-' }}</div>
<div class="text-xs text-slate-400 mt-1">Umur pakai {{ optional($asset->item)->umur_pakai_bulan }} bulan</div>
</div>
</div>
<div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm flex flex-wrap items-center gap-3">
<div class="flex-1 min-w-[200px]">
<div class="text-xs uppercase tracking-wider text-slate-400 mb-1">Barcode</div>
<div class="font-mono font-bold">{{ $asset->kode_barcode }}</div>
</div>
<form method="POST" action="{{ route('admin.assets.lepas',$asset) }}" class="flex items-center gap-2">@csrf
@if($asset->activePlacement)
<button class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-4 py-2 rounded-lg text-sm transition"><i class="fa fa-warehouse mr-1 text-xs"></i>Lepas ke Gudang</button>
@endif
</form>
@if(!$asset->activePlacement)
<form method="POST" action="{{ route('admin.assets.pasang',$asset) }}" class="flex items-center gap-2">@csrf
<select name="room_id" class="border border-slate-200 rounded-lg px-3 py-2 text-sm bg-white outline-none focus:border-amber-500"><option value="">Pilih kamar...</option>@foreach(\App\Models\Room::orderBy('nomor')->get() as $rm)<option value="{{ $rm->id }}">Kamar {{ $rm->nomor }}</option>@endforeach</select>
<button class="bg-amber-500 hover:bg-amber-600 text-slate-900 px-4 py-2 rounded-lg text-sm font-semibold shadow-sm transition"><i class="fa fa-screwdriver-wrench mr-1 text-xs"></i>Pasang ke Kamar</button>
</form>
@endif
<a href="/scan/{{ $asset->kode_barcode }}" target="_blank" class="bg-slate-900 hover:bg-slate-800 text-white px-4 py-2 rounded-lg text-sm transition"><i class="fa fa-qrcode mr-1 text-xs"></i>Halaman Scan</a>
</div>
@endsection
