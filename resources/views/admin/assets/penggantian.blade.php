@extends('layouts.admin')
@section('title','Barang Perlu Diganti')
@section('content')
@include('admin.partials.header', ['judul'=>'Penggantian Tahunan / Berkala', 'sub'=>'Unit yang mendekati / melewati umur pakai, atau rusak mendadak — badge merah = didahulukan'])
@if(($jmlPerlu ?? 0) > 0)
<div class="bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded-xl mb-4 text-sm"><i class="fa fa-triangle-exclamation mr-1"></i><strong>{{ $jmlPerlu }} unit perlu diganti</strong> (rusak sebelum jadwal) — segera ganti fisik, lalu tekan Selesai Diganti.</div>
@endif
@include('admin.partials.search', ['placeholder'=>'Cari barcode / nama barang...', 'extra'=>'<select name="kondisi" class="border border-slate-200 rounded-lg px-3 py-2 text-sm bg-white outline-none focus:border-amber-500" onchange="this.form.submit()"><option value="">Semua kondisi</option><option value="perlu_diganti" '.(request('kondisi')=='perlu_diganti'?'selected':'').'>Perlu Diganti</option><option value="rusak" '.(request('kondisi')=='rusak'?'selected':'').'>Rusak (histori)</option><option value="terlambat" '.(request('kondisi')=='terlambat'?'selected':'').'>Terlambat</option><option value="segera" '.(request('kondisi')=='segera'?'selected':'').'>Segera</option></select>'])
@php $errPengganti = $errors->has('pengganti_id') ? $errors->first('pengganti_id') : ($errors->has('kondisi') ? $errors->first('kondisi') : null); @endphp
@if($errPengganti)
<div class="bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded-xl mb-4 text-sm"><i class="fa fa-circle-xmark mr-1"></i>{{ $errPengganti }}</div>
@endif
@if($stokGudang->isEmpty())
<div class="bg-amber-50 border border-amber-200 text-amber-800 px-4 py-3 rounded-xl mb-4 text-sm"><i class="fa fa-box-open mr-1"></i><strong>Stok pengganti kosong</strong> — beli/terima unit baru dulu (menu Pembelian) agar bisa menukar unit perlu diganti. Sementara bisa pakai tombol "Tanpa Tukar" (perbaikan).</div>
@endif
<div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-x-auto">
<table class="w-full text-sm">
<thead><tr class="bg-slate-50 text-left text-xs uppercase tracking-wider text-slate-500 border-b border-slate-200">
<th class="px-4 py-3">Barcode</th><th class="px-4 py-3">Barang</th><th class="px-4 py-3">Kamar</th><th class="px-4 py-3">Rencana Ganti</th><th class="px-4 py-3">Status</th><th class="px-4 py-3 text-right">Aksi</th>
</tr></thead>
<tbody class="divide-y divide-slate-100">
@forelse($units as $u)
@php
$perlu = $u->kondisi === 'perlu_diganti';
$rusak = $u->kondisi === 'rusak';
$dibuang = $u->status === 'dibuang';
$lewat = !$perlu && !$rusak && $u->tanggal_ganti_rencana && $u->tanggal_ganti_rencana <= now()->toDateString();
$dekat = !$perlu && !$rusak && $u->tanggal_ganti_rencana && $u->tanggal_ganti_rencana <= now()->addDays(90)->toDateString();
$penggantiSejenis = $stokGudang->where('item_id', $u->item_id)->values();
@endphp
<tr class="hover:bg-slate-50 transition {{ $perlu || $lewat ? 'bg-rose-50/60' : ($rusak ? 'bg-slate-50/80' : '') }}">
<td class="px-4 py-3"><a href="{{ route('admin.assets.show',$u) }}" class="font-mono text-xs font-semibold text-indigo-600 hover:text-amber-600 transition">{{ $u->kode_barcode }}</a></td>
<td class="px-4 py-3">{{ optional($u->item)->nama }}</td>
<td class="px-4 py-3">{{ optional(optional($u->activePlacement)->room)->nomor ?? 'Gudang' }}</td>
<td class="px-4 py-3 text-slate-500">{{ $u->tanggal_ganti_rencana ? \Illuminate\Support\Carbon::parse($u->tanggal_ganti_rencana)->translatedFormat('d M Y') : '-' }}</td>
<td class="px-4 py-3"><span class="text-[11px] px-2 py-0.5 rounded-full border font-medium {{ $dibuang ? 'bg-slate-300 text-slate-600 border-slate-300' : ($perlu ? 'bg-rose-500 text-white border-rose-500' : ($rusak ? 'bg-slate-700 text-white border-slate-700' : ($lewat ? 'bg-rose-50 text-rose-600 border-rose-200' : ($dekat ? 'bg-amber-50 text-amber-700 border-amber-200' : 'bg-slate-100 text-slate-500 border-slate-200')))) }}"><i class="fa {{ $dibuang ? 'fa-trash' : ($perlu ? 'fa-triangle-exclamation' : ($rusak ? 'fa-circle-xmark' : 'fa-rotate')) }} mr-1"></i>{{ $dibuang ? 'Dibuang' : ($perlu ? 'Perlu Diganti' : ($rusak ? 'Rusak' : ($lewat ? 'Terlambat' : ($dekat ? 'Segera' : 'Terjadwal')))) }}</span></td>
<td class="px-4 py-3 text-right whitespace-nowrap">
@if($dibuang)
<span class="text-slate-400 text-xs" title="Unit sudah dibuang — hanya arsip histori"><i class="fa fa-box-archive mr-1"></i>Arsip</span>
@elseif($rusak)
<form method="POST" action="{{ route('admin.assets.perbaiki',$u) }}" class="inline" onsubmit="return konfirmasiHapus('Kembalikan {{ $u->kode_barcode }} ke kondisi baik (sudah diperbaiki)?', this)">@csrf<button class="text-indigo-600 hover:text-indigo-700 transition text-xs font-semibold"><i class="fa fa-wrench mr-1"></i>Perbaiki</button></form>
<form method="POST" action="{{ route('admin.assets.buang',$u) }}" class="inline ml-1" onsubmit="return konfirmasiHapus('Buang {{ $u->kode_barcode }}? Unit tidak akan muncul di stok dan tidak bisa dipakai lagi — histori tetap tersimpan.', this)">@csrf<button class="text-rose-500 hover:text-rose-600 transition text-xs font-semibold" title="Buang unit rusak"><i class="fa fa-trash mr-1"></i>Buang</button></form>
@elseif($perlu)
@if(optional($u->activePlacement)->room_id)
<form method="POST" action="{{ route('admin.assets.selesai-ganti',$u) }}" class="inline-flex items-center gap-1" onsubmit="return konfirmasiHapus('Tukar {{ $u->kode_barcode }} dengan unit terpilih? Unit lama dilepas & berstatus rusak (histori).', this)">
@csrf
<select name="pengganti_id" class="border border-slate-200 rounded-lg px-2 py-1 text-xs bg-white outline-none focus:border-amber-500" required>
<option value="">— pilih pengganti —</option>
@foreach($penggantiSejenis as $p)
<option value="{{ $p->id }}">{{ $p->kode_barcode }}</option>
@endforeach
</select>
<button class="text-emerald-600 hover:text-emerald-700 transition text-xs font-semibold"><i class="fa fa-arrow-right-arrow-left mr-1"></i>Tukar</button>
</form>
@endif
<form method="POST" action="{{ route('admin.assets.selesai-ganti',$u) }}" class="inline ml-1" onsubmit="return konfirmasiHapus('Unit {{ $u->kode_barcode }} diperbaiki tanpa ganti fisik (kembali baik, jadwal dihitung ulang)?', this)">@csrf<button class="text-slate-400 hover:text-emerald-600 transition text-xs" title="Perbaiki tanpa ganti unit"><i class="fa fa-circle-check mr-1"></i>Tanpa Tukar</button></form>
@else
<form method="POST" action="{{ route('admin.assets.tandai-ganti',$u) }}" class="inline" onsubmit="return konfirmasiHapus('Tandai {{ $u->kode_barcode }} perlu diganti (mis. putus/rusak sebelum jadwal)?', this)">@csrf<button class="text-slate-400 hover:text-rose-600 transition text-xs"><i class="fa fa-triangle-exclamation mr-1"></i>Tandai Perlu Diganti</button></form>
@endif
</td>
</tr>
@empty
<tr><td colspan="6" class="px-4 py-10 text-center text-slate-400"><i class="fa fa-rotate text-2xl mb-2 block"></i>Tidak ada unit yang cocok.</td></tr>
@endforelse
</tbody>
</table>
</div>
@include('admin.partials.pager', ['paginator'=>$units])
@endsection
