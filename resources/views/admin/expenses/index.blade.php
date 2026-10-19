@extends('layouts.admin')
@section('title','Pengeluaran')
@section('content')
@include('admin.partials.header', ['judul'=>'Pengeluaran', 'sub'=>'Catat dan pantau semua pengeluaran operasional', 'ctaHref'=>route('admin.expenses.create'), 'ctaText'=>'Catat Pengeluaran'])
@include('admin.partials.search', ['placeholder'=>'Cari keterangan / nomor...'])
<div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-x-auto">
<table class="w-full text-sm">
<thead><tr class="bg-slate-50 text-left text-xs uppercase tracking-wider text-slate-500 border-b border-slate-200">
<th class="px-4 py-3">Tanggal</th><th class="px-4 py-3">Keterangan</th><th class="px-4 py-3">Item</th><th class="px-4 py-3">Jumlah</th><th class="px-4 py-3 text-right">Aksi</th>
</tr></thead>
<tbody class="divide-y divide-slate-100">
@forelse($expenses as $e)
<tr class="hover:bg-slate-50 transition">
<td class="px-4 py-3">{{ \Illuminate\Support\Carbon::parse($e->tanggal)->translatedFormat('d M Y') }}</td>
<td class="px-4 py-3">{{ $e->keterangan }}</td>
<td class="px-4 py-3"><span class="text-[11px] px-2 py-0.5 rounded-full bg-amber-50 text-amber-700 border border-amber-200">{{ $e->items->count() }} item</span>@if($e->items->first())<div class="text-xs text-slate-400 mt-1">{{ $e->items->first()->uraian }}{{ $e->items->count() > 1 ? ' +' . ($e->items->count()-1) . ' lainnya' : '' }}</div>@endif</td>
<td class="px-4 py-3 font-semibold text-rose-600">Rp {{ number_format($e->jumlah,0,',','.') }}</td>
<td class="px-4 py-3 text-right whitespace-nowrap">
@php $menuPengeluaran = array_values(array_filter([
['tipe'=>'link','label'=>'Edit','ikon'=>'fa-pen','url'=>route('admin.expenses.edit',$e)],
!$e->purchase && !$e->maintenance ? ['tipe'=>'hapus','label'=>'Hapus','ikon'=>'fa-trash','url'=>route('admin.expenses.destroy',$e),'pesan'=>'Hapus pengeluaran '.$e->nomor."?\n\nSaldo kas otomatis bertambah kembali Rp ".number_format($e->jumlah,0,',','.').'.'] : ['tipe'=>'info','label'=>'Terkunci','ikon'=>'fa-lock','title'=>'Pengeluaran otomatis dari '.(optional($e->purchase)->nomor ?? 'maintenance').' — hapus lewat menu terkait'],
])); @endphp
@include('admin.partials.aksi',['menu'=>$menuPengeluaran])
</td>
</tr>
@empty
<tr><td colspan="5" class="px-4 py-10 text-center text-slate-400"><i class="fa fa-wallet text-2xl mb-2 block"></i>Belum ada pengeluaran yang cocok.</td></tr>
@endforelse
</tbody>
</table>
</div>
@include('admin.partials.pager', ['paginator'=>$expenses])
@endsection
