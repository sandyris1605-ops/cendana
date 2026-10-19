@extends('layouts.admin')
@section('title','Inventaris & Aset')
@section('content')
@include('admin.partials.header', ['judul'=>'Inventaris & Aset', 'sub'=>$units->total().' unit terdaftar dengan barcode', 'ctaSlot'=>'<a href="'.route('admin.barcode.cetak').'" target="_blank" class="inline-flex items-center gap-2 bg-white border border-slate-200 hover:border-slate-400 text-slate-700 px-4 py-2 rounded-lg text-sm shadow-sm transition"><i class="fa fa-print text-xs"></i>Cetak Barcode</a><a href="'.route('admin.penggantian').'" class="inline-flex items-center gap-2 bg-rose-500 hover:bg-rose-600 text-white px-4 py-2 rounded-lg text-sm shadow-sm transition"><i class="fa fa-rotate text-xs"></i>Perlu Diganti</a>'])
@include('admin.partials.search', ['placeholder'=>'Cari barcode / nama barang...', 'extra'=>'<select name="kondisi" class="border border-slate-200 rounded-lg px-3 py-2 text-sm bg-white outline-none focus:border-amber-500" onchange="this.form.submit()"><option value="">Semua kondisi</option><option value="baik" '.(request('kondisi')=='baik'?'selected':'').'>Baik</option><option value="perlu_diganti" '.(request('kondisi')=='perlu_diganti'?'selected':'').'>Perlu Diganti</option><option value="rusak" '.(request('kondisi')=='rusak'?'selected':'').'>Rusak</option><option value="dibuang" '.(request('kondisi')=='dibuang'?'selected':'').'>Dibuang</option></select>'])
<div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-x-auto">
<table class="w-full text-sm">
<thead><tr class="bg-slate-50 text-left text-xs uppercase tracking-wider text-slate-500 border-b border-slate-200">
<th class="px-4 py-3">Barcode</th><th class="px-4 py-3">Barang</th><th class="px-4 py-3">Lokasi</th><th class="px-4 py-3">Kondisi</th><th class="px-4 py-3">Nilai Buku</th><th class="px-4 py-3">Rencana Ganti</th>
</tr></thead>
<tbody class="divide-y divide-slate-100">
@forelse($units as $u)
@php
$dibuang = $u->status === 'dibuang';
$k = $dibuang ? 'dibuang' : ($u->kondisi ?? 'baik');
$badge = [
'baik' => 'bg-emerald-50 text-emerald-700 border-emerald-200|fa-circle-check|Baik',
'perlu_diganti' => 'bg-rose-500 text-white border-rose-500|fa-triangle-exclamation|Perlu Diganti',
'rusak' => 'bg-slate-700 text-white border-slate-700|fa-circle-xmark|Rusak',
'dibuang' => 'bg-slate-300 text-slate-600 border-slate-300|fa-trash|Dibuang',
];
$bd = $badge[$k] ?? $badge['baik'];
$bdArr = explode('|', $bd);
@endphp
<tr class="hover:bg-slate-50 transition {{ $k=='perlu_diganti'||$k=='rusak' ? 'bg-rose-50/60' : ($dibuang ? 'bg-slate-50/80' : '') }}">
<td class="px-4 py-3"><a href="{{ route('admin.assets.show',$u) }}" class="font-mono text-xs font-semibold text-indigo-600 hover:text-amber-600 transition">{{ $u->kode_barcode }}</a></td>
<td class="px-4 py-3">{{ optional($u->item)->nama }}</td>
<td class="px-4 py-3"><span class="text-[11px] px-2 py-0.5 rounded-full bg-slate-100 text-slate-600 border border-slate-200">{{ optional(optional($u->activePlacement)->room)->nomor ?? 'Gudang' }}</span></td>
<td class="px-4 py-3"><span class="text-[11px] px-2 py-0.5 rounded-full border font-medium {{ $bdArr[0] }}"><i class="fa {{ $bdArr[1] }} mr-1"></i>{{ $bdArr[2] }}</span></td>
<td class="px-4 py-3">Rp {{ number_format($u->nilai_buku,0,',','.') }}</td>
<td class="px-4 py-3 text-slate-500">{{ $u->tanggal_ganti_rencana ? \Illuminate\Support\Carbon::parse($u->tanggal_ganti_rencana)->translatedFormat('d M Y') : '-' }}</td>
</tr>
@empty
<tr><td colspan="6" class="px-4 py-10 text-center text-slate-400"><i class="fa fa-boxes-stacked text-2xl mb-2 block"></i>Tidak ada aset yang cocok.</td></tr>
@endforelse
</tbody>
</table>
</div>
@include('admin.partials.pager', ['paginator'=>$units])
@endsection
