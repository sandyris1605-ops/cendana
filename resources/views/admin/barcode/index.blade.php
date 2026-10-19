@extends('layouts.admin')
@section('title','Barcode Inventaris')
@section('content')
@include('admin.partials.header', ['judul'=>'Barcode Barang', 'sub'=>'Label siap tempel di fisik barang — scan dari HP tanpa login', 'ctaSlot'=>'<a href="'.route('admin.barcode.cetak').'" target="_blank" class="inline-flex items-center gap-2 bg-amber-500 hover:bg-amber-600 text-slate-900 px-4 py-2 rounded-lg text-sm font-semibold shadow-sm transition"><i class="fa fa-print text-xs"></i>Cetak 60 Label</a>'])
@include('admin.partials.search', ['placeholder'=>'Cari kode barcode...'])
<form method="GET" action="{{ route('admin.barcode.index') }}" class="flex flex-wrap items-end gap-3 mb-4 bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
<div class="flex-1 min-w-[180px]">
<label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">Filter per Kamar</label>
<select name="room_id" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm bg-white focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 outline-none">
<option value="">— Semua Barang —</option>
@foreach($rooms as $rm)
<option value="{{ $rm->id }}" @selected(request('room_id')==$rm->id)>{{ $rm->nomor }}</option>
@endforeach
</select>
</div>
<div class="flex gap-2">
<button type="submit" class="inline-flex items-center gap-2 bg-slate-900 hover:bg-slate-800 text-white px-4 py-2 rounded-lg text-sm font-semibold transition"><i class="fa fa-filter text-xs"></i>Terapkan</button>
@if(request('room_id'))
<a href="{{ route('admin.barcode.cetak',['room_id'=>request('room_id')]) }}" target="_blank" class="inline-flex items-center gap-2 bg-amber-500 hover:bg-amber-600 text-slate-900 px-4 py-2 rounded-lg text-sm font-semibold transition"><i class="fa fa-print text-xs"></i>Cetak Kamar Ini</a>
@endif
</div>
</form>
<div class="grid grid-cols-2 md:grid-cols-4 gap-3">
@forelse($units as $u)
<div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm text-center hover:border-amber-400 transition">
<div class="text-xs font-bold truncate">{{ optional($u->item)->nama }}</div>
<div class="my-2"><img src="data:image/png;base64,{!! \Milon\Barcode\Facades\DNS1DFacade::getBarcodePNG($u->kode_barcode, 'C128', 2, 40) !!}" alt="{{ $u->kode_barcode }}" style="max-width:100%;height:auto"></div>
<div class="font-mono text-xs font-semibold">{{ $u->kode_barcode }}</div>
<div class="text-[11px] text-slate-400 mt-1">{{ optional(optional($u->activePlacement)->room)->nomor ?? 'Gudang' }}</div>
</div>
@empty
<div class="col-span-full bg-white p-10 rounded-xl border border-slate-200 text-center text-slate-400"><i class="fa fa-barcode text-2xl mb-2 block"></i>Tidak ada barcode yang cocok.</div>
@endforelse
</div>
@include('admin.partials.pager', ['paginator'=>$units])
@endsection
