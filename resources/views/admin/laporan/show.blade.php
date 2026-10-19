@extends('layouts.admin')
@section('title','Laporan '.ucfirst($jenis))
@section('content')
@php
$meta = [
 'keuangan'=>['judul'=>'Laporan Keuangan','sub'=>'Dana masuk vs pengeluaran periode','ikon'=>'fa-coins','grad'=>'from-emerald-500 to-teal-600','tint'=>'bg-emerald-50 text-emerald-600'],
 'okupansi'=>['judul'=>'Laporan Okupansi','sub'=>'Status huni kamar & tingkat hunian','ikon'=>'fa-bed','grad'=>'from-indigo-500 to-violet-600','tint'=>'bg-indigo-50 text-indigo-600'],
 'inventaris'=>['judul'=>'Laporan Inventaris','sub'=>'Seluruh unit aset + lokasi & kondisi','ikon'=>'fa-boxes-stacked','grad'=>'from-amber-500 to-orange-600','tint'=>'bg-amber-50 text-amber-600'],
 'pembelian'=>['judul'=>'Laporan Pembelian','sub'=>'Riwayat belanja per supplier & dana','ikon'=>'fa-cart-shopping','grad'=>'from-sky-500 to-blue-600','tint'=>'bg-sky-50 text-sky-600'],
 'penyusutan'=>['judul'=>'Laporan Penyusutan','sub'=>'Nilai buku & akumulasi penyusutan aset','ikon'=>'fa-chart-line','grad'=>'from-violet-500 to-purple-600','tint'=>'bg-violet-50 text-violet-600'],
 'penggantian'=>['judul'=>'Laporan Penggantian','sub'=>'Jadwal ganti barang berkala & urgensi','ikon'=>'fa-rotate','grad'=>'from-rose-500 to-pink-600','tint'=>'bg-rose-50 text-rose-600'],
];
$m = $meta[$jenis] ?? $meta['inventaris'];
$berbasisTanggal = in_array($jenis, ['keuangan','pembelian','inventaris']);
@endphp
@php
$eksporQuery = ['jenis'=>$jenis,'dari'=>$dari,'sampai'=>$sampai];
@endphp
@include('admin.partials.header', ['judul'=>$m['judul'], 'sub'=>$m['sub'], 'ctaSlot'=>'<a href="'.route('admin.laporan.pdf', $eksporQuery).'" class="inline-flex items-center gap-2 bg-rose-500 hover:bg-rose-600 text-white px-4 py-2 rounded-lg text-sm shadow-sm transition"><i class="fa fa-file-pdf text-xs"></i>PDF</a><a href="'.route('admin.laporan.csv', $eksporQuery).'" class="inline-flex items-center gap-2 bg-emerald-500 hover:bg-emerald-600 text-white px-4 py-2 rounded-lg text-sm shadow-sm transition"><i class="fa fa-file-csv text-xs"></i>CSV/Excel</a>'])

{{-- Hero ringkas --}}
<div class="rounded-xl bg-gradient-to-r {{ $m['grad'] }} text-white p-5 mb-4 shadow-sm flex flex-wrap items-center gap-4">
<div class="w-12 h-12 rounded-xl bg-white/20 flex items-center justify-center text-xl"><i class="fa {{ $m['ikon'] }}"></i></div>
<div>
<div class="font-bold text-lg">{{ $m['judul'] }}</div>
<div class="text-sm text-white/80">Periode {{ \Illuminate\Support\Carbon::parse($dari)->translatedFormat('d M Y') }} — {{ \Illuminate\Support\Carbon::parse($sampai)->translatedFormat('d M Y') }}</div>
</div>
<div class="ml-auto text-right">
<div class="text-xs text-white/70 uppercase tracking-wider">Jumlah baris</div>
<div class="font-bold text-xl">{{ count($rows) }}</div>
</div>
</div>

{{-- Filter periode --}}
@if($berbasisTanggal)
<form method="GET" action="{{ route('admin.laporan.show',$jenis) }}" class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm mb-4 flex flex-wrap items-end gap-3">
<div><label class="block text-[11px] font-semibold uppercase tracking-wider text-slate-500 mb-1">Dari Tanggal</label><input type="date" name="dari" value="{{ $dari }}" class="border border-slate-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-amber-500"></div>
<div><label class="block text-[11px] font-semibold uppercase tracking-wider text-slate-500 mb-1">Sampai Tanggal</label><input type="date" name="sampai" value="{{ $sampai }}" class="border border-slate-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-amber-500"></div>
<button class="bg-amber-500 hover:bg-amber-600 text-slate-900 px-4 py-2 rounded-lg text-sm font-semibold shadow-sm transition"><i class="fa fa-filter mr-1 text-xs"></i>Terapkan</button>
<a href="{{ route('admin.laporan.show',$jenis) }}" class="px-4 py-2 rounded-lg text-sm border border-slate-200 text-slate-600 hover:bg-slate-50 transition">Reset</a>
<div class="text-xs text-slate-400 ml-auto"><i class="fa fa-circle-info mr-1"></i>Cakupan tanggal: belanja/pembayaran</div>
</form>
@else
<div class="bg-white px-4 py-3 rounded-xl border border-slate-200 shadow-sm mb-4 text-xs text-slate-500"><i class="fa fa-clock-rotate-left mr-1"></i>Laporan ini berupa <strong>snapshot data saat ini</strong> (tidak difilter periode).</div>
@endif

{{-- Kartu ringkasan per jenis --}}
@php
$cards = [];
if ($jenis=='keuangan') $cards = [
 ['Masuk (Kas)','Rp '.number_format($masuk,0,',','.'),'fa-arrow-trend-up','text-emerald-600','bg-emerald-50'],
 ['Keluar (Kas)','Rp '.number_format($keluar,0,',','.'),'fa-arrow-trend-down','text-rose-600','bg-rose-50'],
 ['Bersih Kas','Rp '.number_format($masuk-$keluar,0,',','.'),($masuk-$keluar>=0?'fa-circle-check':'fa-circle-xmark'),($masuk-$keluar>=0?'text-indigo-600':'text-rose-600'),($masuk-$keluar>=0?'bg-indigo-50':'bg-rose-50')],
 ['Belanja Perusahaan','Rp '.number_format($belanjaPerusahaan ?? 0,0,',','.'),'fa-building','text-sky-600','bg-sky-50'],
 ['Transaksi',($jmlTransaksi ?? 0).' entri','fa-receipt','text-slate-600','bg-slate-100'],
];
elseif ($jenis=='okupansi') $cards = [
 ['Total Kamar',$total,'fa-bed','text-indigo-600','bg-indigo-50'],
 ['Terisi',$terisi.' ('.$persen.'%)','fa-user-check','text-emerald-600','bg-emerald-50'],
 ['Tersedia',$tersedia,'fa-door-open','text-sky-600','bg-sky-50'],
 ['Maintenance',$maintenance,'fa-screwdriver-wrench','text-amber-600','bg-amber-50'],
];
elseif ($jenis=='inventaris') $cards = [
 ['Total Unit',count($rows),'fa-boxes-stacked','text-amber-600','bg-amber-50'],
 ['Terpasang',$jmlTerpasang,'fa-location-dot','text-indigo-600','bg-indigo-50'],
 ['Di Gudang',$jmlGudang,'fa-warehouse','text-sky-600','bg-sky-50'],
 ['Rusak / Dibuang',$jmlRusak.' / '.$jmlDibuang,'fa-triangle-exclamation','text-rose-600','bg-rose-50'],
 ['Total Nilai Buku','Rp '.number_format($totalBuku,0,',','.'),'fa-scale-balanced','text-emerald-600','bg-emerald-50'],
];
elseif ($jenis=='pembelian') $cards = [
 ['Total Belanja','Rp '.number_format($totalBelanja,0,',','.'),'fa-cart-shopping','text-sky-600','bg-sky-50'],
 ['Dana Kas','Rp '.number_format($totalKas,0,',','.'),'fa-sack-dollar','text-emerald-600','bg-emerald-50'],
 ['Dana Perusahaan','Rp '.number_format($totalPerusahaan,0,',','.'),'fa-building','text-violet-600','bg-violet-50'],
 ['PO',count($rows).' dokumen','fa-file-invoice','text-amber-600','bg-amber-50'],
 ['Item Dibeli',$jmlItem.' unit','fa-box','text-slate-600','bg-slate-100'],
];
elseif ($jenis=='penyusutan') $cards = [
 ['Total Perolehan','Rp '.number_format($totalPerolehan,0,',','.'),'fa-coins','text-amber-600','bg-amber-50'],
 ['Total Nilai Buku','Rp '.number_format($totalBuku,0,',','.'),'fa-wallet','text-indigo-600','bg-indigo-50'],
 ['Akumulasi Susut','Rp '.number_format($akumulasiSusut,0,',','.'),'fa-chart-line','text-rose-600','bg-rose-50'],
 ['Persen Susut',$persenSusut.'%','fa-percent','text-violet-600','bg-violet-50'],
 ['Susut '.$periode,'Rp '.number_format($susutPeriode,0,',','.'),'fa-calendar','text-slate-600','bg-slate-100'],
];

else $cards = [
 ['Perlu Diganti',$jmlPerlu,'fa-triangle-exclamation','text-rose-600','bg-rose-50'],
 ['Terlambat',$jmlTerlambat,'fa-clock','text-amber-600','bg-amber-50'],
 ['Segera (90 hr)',$jmlSegera,'fa-hourglass-half','text-sky-600','bg-sky-50'],
 ['Total Terjadwal',count($rows),'fa-rotate','text-indigo-600','bg-indigo-50'],
];
@endphp
<div class="grid grid-cols-2 lg:grid-cols-{{ min(count($cards),5) }} gap-3 mb-4">
@foreach($cards as $c)
<div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
<div class="flex items-center gap-2 mb-1"><div class="w-8 h-8 rounded-lg {{ $c[4] }} {{ $c[3] }} flex items-center justify-center text-sm"><i class="fa {{ $c[2] }}"></i></div><div class="text-[11px] uppercase tracking-wider text-slate-400 font-semibold">{{ $c[0] }}</div></div>
<div class="text-lg font-bold {{ $c[3] }}">{{ $c[1] }}</div>
</div>
@endforeach
</div>

{{-- ============ TABEL DETAIL PER JENIS ============ --}}
@php $th = 'px-4 py-3 text-left text-xs uppercase tracking-wider'; $td = 'px-4 py-3'; $rupiah = fn($n) => 'Rp '.number_format($n,0,',','.'); $tgl = fn($d) => $d ? \Illuminate\Support\Carbon::parse($d)->translatedFormat('d M Y') : '-'; @endphp

@if($jenis=='keuangan')
<div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-x-auto mb-4">
<div class="px-4 py-3 border-b border-slate-100 font-semibold text-sm text-emerald-700"><i class="fa fa-arrow-trend-up mr-1"></i>Pemasukan Kas</div>
<table class="w-full text-sm"><thead><tr class="bg-slate-50 border-b border-slate-200 text-slate-500"><th class="{{ $th }}">Tanggal</th><th class="{{ $th }}">Keterangan</th><th class="{{ $th }} text-right">Jumlah</th></tr></thead>
<tbody class="divide-y divide-slate-100">
@forelse($rows as $x)<tr class="hover:bg-slate-50 transition"><td class="{{ $td }}">{{ $tgl($x->tanggal) }}</td><td class="{{ $td }}">{{ $x->keterangan ?? 'Top Up' }}</td><td class="{{ $td }} text-right font-semibold text-emerald-600">{{ $rupiah($x->jumlah) }}</td></tr>
@empty<tr><td colspan="3" class="px-4 py-8 text-center text-slate-400">Tidak ada pemasukan pada periode ini.</td></tr>@endforelse
</tbody></table>
</div>

<div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-x-auto mb-4">
<div class="px-4 py-3 border-b border-slate-100 font-semibold text-sm text-rose-700"><i class="fa fa-arrow-trend-down mr-1"></i>Rincian Pengeluaran Kas</div>
<table class="w-full text-sm"><thead><tr class="bg-slate-50 border-b border-slate-200 text-slate-500"><th class="{{ $th }}">Tanggal</th><th class="{{ $th }}">Nomor</th><th class="{{ $th }}">Keterangan</th><th class="{{ $th }} text-right">Jumlah</th></tr></thead>
<tbody class="divide-y divide-slate-100">
@forelse($rows2 as $x)<tr class="hover:bg-slate-50 transition"><td class="{{ $td }}">{{ $tgl($x->tanggal) }}</td><td class="{{ $td }} font-mono text-xs">{{ $x->nomor }}</td><td class="{{ $td }} text-slate-500">{{ $x->keterangan }}</td><td class="{{ $td }} text-right font-semibold text-rose-600">{{ $rupiah($x->jumlah) }}</td></tr>
@empty<tr><td colspan="4" class="px-4 py-8 text-center text-slate-400">Tidak ada pengeluaran kas pada periode ini.</td></tr>@endforelse
</tbody></table>
</div>
@if(count($rows3 ?? []) > 0)
<div class="bg-white rounded-xl border border-sky-200 shadow-sm overflow-x-auto mb-4">
<div class="px-4 py-3 border-b border-sky-100 font-semibold text-sm text-sky-700"><i class="fa fa-building mr-1"></i>Belanja Perusahaan (PO) — di luar saldo kas</div>
<table class="w-full text-sm"><thead><tr class="bg-sky-50 border-b border-sky-100 text-sky-600"><th class="{{ $th }}">Tanggal</th><th class="{{ $th }}">Nomor PO</th><th class="{{ $th }}">Supplier</th><th class="{{ $th }} text-right">Total</th></tr></thead>
<tbody class="divide-y divide-slate-100">
@foreach($rows3 as $p)<tr class="hover:bg-slate-50 transition"><td class="{{ $td }}">{{ $tgl($p->tanggal) }}</td><td class="{{ $td }} font-mono text-xs">{{ $p->nomor }}</td><td class="{{ $td }}">{{ optional($p->supplier)->nama ?? '-' }}</td><td class="{{ $td }} text-right font-semibold text-sky-700">{{ $rupiah($p->total) }}</td></tr>@endforeach
</tbody></table>
</div>
@endif
@elseif($jenis=='okupansi')
<div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-x-auto">
<table class="w-full text-sm"><thead><tr class="bg-slate-50 border-b border-slate-200 text-slate-500"><th class="{{ $th }}">Kamar</th><th class="{{ $th }}">Gedung</th><th class="{{ $th }}">Tipe</th><th class="{{ $th }}">Lantai</th><th class="{{ $th }}">Penghuni</th><th class="{{ $th }}">Status</th></tr></thead>
<tbody class="divide-y divide-slate-100">
@php $badge=['tersedia'=>'bg-emerald-50 text-emerald-700 border-emerald-200','terisi'=>'bg-indigo-50 text-indigo-600 border-indigo-200','maintenance'=>'bg-amber-50 text-amber-700 border-amber-200']; @endphp
@forelse($rows as $rm)<tr class="hover:bg-slate-50 transition">
<td class="{{ $td }} font-semibold">{{ $rm->nomor }}</td>
<td class="{{ $td }} text-slate-500">{{ optional($rm->building)->nama ?? '-' }}</td>
<td class="{{ $td }}">{{ optional($rm->roomType)->nama ?? '-' }} <span class="text-xs text-slate-400">({{ optional($rm->roomType)->ukuran ?? '-' }})</span></td>
<td class="{{ $td }}">{{ $rm->lantai }}</td>
<td class="{{ $td }}">{{ optional(optional($rm->activeTenancy)->tenant)->nama ?? '-' }}</td>
<td class="{{ $td }}"><span class="text-[11px] px-2 py-0.5 rounded-full border font-medium {{ $badge[$rm->status] ?? 'bg-slate-100 text-slate-600 border-slate-200' }}">{{ ucfirst($rm->status) }}</span></td>
</tr>
@empty<tr><td colspan="6" class="px-4 py-8 text-center text-slate-400">Belum ada kamar.</td></tr>@endforelse
</tbody></table>
</div>

@elseif($jenis=='inventaris')
<div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-x-auto">
<table class="w-full text-sm"><thead><tr class="bg-slate-50 border-b border-slate-200 text-slate-500"><th class="{{ $th }}">Barcode</th><th class="{{ $th }}">Barang</th><th class="{{ $th }}">Lokasi</th><th class="{{ $th }}">Kondisi</th><th class="{{ $th }}">Status</th><th class="{{ $th }} text-right">Perolehan</th><th class="{{ $th }} text-right">Nilai Buku</th><th class="{{ $th }}">Beli</th></tr></thead>
<tbody class="divide-y divide-slate-100">
@php $kBadge=['baik'=>'bg-emerald-50 text-emerald-700 border-emerald-200','perlu_diganti'=>'bg-rose-500 text-white border-rose-500','rusak'=>'bg-slate-700 text-white border-slate-700']; @endphp
@forelse($rows as $x)
@php $db = $x->status==='dibuang'; $k = $db ? 'dibuang' : ($x->kondisi ?? 'baik'); @endphp
<tr class="hover:bg-slate-50 transition {{ $k=='perlu_diganti'||$k=='rusak'?'bg-rose-50/60':($db?'bg-slate-50/80':'') }}">
<td class="{{ $td }} font-mono text-xs font-semibold text-indigo-600">{{ $x->kode_barcode }}</td>
<td class="{{ $td }}">{{ optional($x->item)->nama }}</td>
<td class="{{ $td }}">{{ optional(optional($x->activePlacement)->room)->nomor ?? 'Gudang' }}</td>
<td class="{{ $td }}"><span class="text-[11px] px-2 py-0.5 rounded-full border font-medium {{ $db ? 'bg-slate-300 text-slate-600 border-slate-300' : ($kBadge[$k] ?? $kBadge['baik']) }}">{{ $db ? 'Dibuang' : ucfirst(str_replace('_',' ',$k)) }}</span></td>
<td class="{{ $td }}"><span class="text-[11px] px-2 py-0.5 rounded-full {{ ['terpasang'=>'bg-indigo-50 text-indigo-600','gudang'=>'bg-sky-50 text-sky-600','dibuang'=>'bg-slate-100 text-slate-500'][$x->status] ?? 'bg-slate-100 text-slate-500' }}">{{ ucfirst($x->status) }}</span></td>
<td class="{{ $td }} text-right">{{ $rupiah($x->harga_perolehan) }}</td>
<td class="{{ $td }} text-right font-semibold">{{ $rupiah($x->nilai_buku) }}</td>
<td class="{{ $td }} text-slate-500">{{ $tgl($x->tanggal_beli) }}</td>
</tr>
@empty<tr><td colspan="8" class="px-4 py-8 text-center text-slate-400">Tidak ada aset dibeli pada periode ini.</td></tr>@endforelse
</tbody>
<tfoot><tr class="bg-slate-50 font-semibold border-t border-slate-200"><td class="{{ $td }} text-slate-500" colspan="5">Total {{ count($rows) }} unit</td><td class="{{ $td }} text-right">{{ $rupiah($totalPerolehan) }}</td><td class="{{ $td }} text-right text-emerald-600">{{ $rupiah($totalBuku) }}</td><td class="{{ $td }}"></td></tr></tfoot>
</table>
</div>

@elseif($jenis=='pembelian')
<div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-x-auto">
<table class="w-full text-sm"><thead><tr class="bg-slate-50 border-b border-slate-200 text-slate-500"><th class="{{ $th }}">Tanggal</th><th class="{{ $th }}">Nomor</th><th class="{{ $th }}">Supplier</th><th class="{{ $th }}">Dana</th><th class="{{ $th }}">Item</th><th class="{{ $th }} text-right">Total</th></tr></thead>
<tbody class="divide-y divide-slate-100">
@forelse($rows as $p)<tr class="hover:bg-slate-50 transition">
<td class="{{ $td }}">{{ $tgl($p->tanggal) }}</td>
<td class="{{ $td }} font-mono text-xs font-semibold">{{ $p->nomor }}</td>
<td class="{{ $td }}">{{ optional($p->supplier)->nama ?? '-' }}</td>
<td class="{{ $td }}"><span class="text-[11px] px-2 py-0.5 rounded-full border {{ $p->sumber_dana=='perusahaan' ? 'bg-violet-50 text-violet-600 border-violet-200' : 'bg-emerald-50 text-emerald-600 border-emerald-200' }}">{{ ucfirst($p->sumber_dana ?? 'kas') }}</span></td>
<td class="{{ $td }} text-slate-500 text-xs">{{ $p->items->map(fn($i) => $i->qty.'x '.optional($i->item)->nama)->implode(', ') ?: '-' }}</td>
<td class="{{ $td }} text-right font-semibold">{{ $rupiah($p->total) }}</td>
</tr>
@empty<tr><td colspan="6" class="px-4 py-8 text-center text-slate-400">Tidak ada pembelian pada periode ini.</td></tr>@endforelse
</tbody>
<tfoot><tr class="bg-slate-50 font-semibold border-t border-slate-200"><td class="{{ $td }} text-slate-500" colspan="5">Total {{ count($rows) }} PO — {{ $jmlItem }} item</td><td class="{{ $td }} text-right text-sky-600">{{ $rupiah($totalBelanja) }}</td></tr></tfoot>
</table>
</div>

@elseif($jenis=='penyusutan')
<div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-x-auto">
<table class="w-full text-sm"><thead><tr class="bg-slate-50 border-b border-slate-200 text-slate-500"><th class="{{ $th }}">Barcode</th><th class="{{ $th }}">Barang</th><th class="{{ $th }} text-right">Perolehan</th><th class="{{ $th }} text-right">Residu</th><th class="{{ $th }} text-right">Akum. Susut</th><th class="{{ $th }} text-right">Nilai Buku</th><th class="{{ $th }} text-center">% Susut</th></tr></thead>
<tbody class="divide-y divide-slate-100">
@forelse($rows as $x)
@php $susut = $x->harga_perolehan - $x->nilai_buku; $pc = $x->harga_perolehan ? min(100, round($susut/$x->harga_perolehan*100)) : 0; @endphp
<tr class="hover:bg-slate-50 transition">
<td class="{{ $td }} font-mono text-xs font-semibold text-indigo-600">{{ $x->kode_barcode }}</td>
<td class="{{ $td }}">{{ optional($x->item)->nama }}</td>
<td class="{{ $td }} text-right">{{ $rupiah($x->harga_perolehan) }}</td>
<td class="{{ $td }} text-right text-slate-500">{{ $rupiah($x->nilai_residu) }}</td>
<td class="{{ $td }} text-right text-rose-600">{{ $rupiah($susut) }}</td>
<td class="{{ $td }} text-right font-semibold">{{ $rupiah($x->nilai_buku) }}</td>
<td class="{{ $td }} text-center">
<div class="flex items-center gap-2 justify-center"><div class="w-16 h-1.5 bg-slate-100 rounded-full overflow-hidden"><div class="h-full {{ $pc>=80?'bg-rose-500':($pc>=50?'bg-amber-500':'bg-emerald-500') }}" style="width:{{ $pc }}%"></div></div><span class="text-xs font-semibold {{ $pc>=80?'text-rose-600':($pc>=50?'text-amber-600':'text-emerald-600') }}">{{ $pc }}%</span></div>
</td>
</tr>
@empty<tr><td colspan="7" class="px-4 py-8 text-center text-slate-400">Belum ada aset.</td></tr>@endforelse
</tbody>
<tfoot><tr class="bg-slate-50 font-semibold border-t border-slate-200"><td class="{{ $td }} text-slate-500" colspan="2">Total {{ count($rows) }} unit</td><td class="{{ $td }} text-right">{{ $rupiah($totalPerolehan) }}</td><td class="{{ $td }}"></td><td class="{{ $td }} text-right text-rose-600">{{ $rupiah($akumulasiSusut) }}</td><td class="{{ $td }} text-right text-emerald-600">{{ $rupiah($totalBuku) }}</td><td class="{{ $td }} text-center">{{ $persenSusut }}%</td></tr></tfoot>
</table>
</div>

@else {{-- penggantian --}}
<div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-x-auto">
<table class="w-full text-sm"><thead><tr class="bg-slate-50 border-b border-slate-200 text-slate-500"><th class="{{ $th }}">Barcode</th><th class="{{ $th }}">Barang</th><th class="{{ $th }}">Kamar</th><th class="{{ $th }}">Rencana Ganti</th><th class="{{ $th }}">Kondisi</th><th class="{{ $th }}">Status Jadwal</th></tr></thead>
<tbody class="divide-y divide-slate-100">
@forelse($rows as $u)
@php
$lewat = $u->kondisi !== 'perlu_diganti' && $u->tanggal_ganti_rencana && $u->tanggal_ganti_rencana <= now()->toDateString();
$dekat = !$lewat && $u->kondisi === 'baik' && $u->tanggal_ganti_rencana && $u->tanggal_ganti_rencana <= now()->addDays(90)->toDateString();
$k = $u->kondisi ?? 'baik';
$kBadge = ['baik'=>'bg-emerald-50 text-emerald-700 border-emerald-200','perlu_diganti'=>'bg-rose-500 text-white border-rose-500','rusak'=>'bg-slate-700 text-white border-slate-700'];
@endphp
<tr class="hover:bg-slate-50 transition {{ $k=='perlu_diganti'||$lewat?'bg-rose-50/60':'' }}">
<td class="{{ $td }} font-mono text-xs font-semibold text-indigo-600">{{ $u->kode_barcode }}</td>
<td class="{{ $td }}">{{ optional($u->item)->nama }}</td>
<td class="{{ $td }}">{{ optional(optional($u->activePlacement)->room)->nomor ?? 'Gudang' }}</td>
<td class="{{ $td }}">{{ $tgl($u->tanggal_ganti_rencana) }}</td>
<td class="{{ $td }}"><span class="text-[11px] px-2 py-0.5 rounded-full border font-medium {{ $kBadge[$k] ?? $kBadge['baik'] }}">{{ $k=='perlu_diganti'?'Perlu Diganti':ucfirst($k) }}</span></td>
<td class="{{ $td }}"><span class="text-[11px] px-2 py-0.5 rounded-full border font-medium {{ $k=='perlu_diganti'?'bg-rose-100 text-rose-700 border-rose-200':($lewat?'bg-rose-50 text-rose-600 border-rose-200':($dekat?'bg-amber-50 text-amber-700 border-amber-200':'bg-slate-100 text-slate-500 border-slate-200')) }}">{{ $k=='perlu_diganti'?'Perlu Diganti':($lewat?'Terlambat':($dekat?'Segera':'Terjadwal')) }}</span></td>
</tr>
@empty<tr><td colspan="6" class="px-4 py-8 text-center text-slate-400">Tidak ada unit terjadwal ganti dalam 1 tahun.</td></tr>@endforelse
</tbody></table>
</div>
@endif
@endsection



