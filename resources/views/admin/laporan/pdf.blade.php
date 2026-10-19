<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Laporan {{ ucfirst($jenis) }}</title>
<style>
body{font-family:DejaVu Sans,Arial,sans-serif;font-size:10px;color:#1e293b;margin:24px}
h1{font-size:17px;margin:0;color:#0f172a}
h2{font-size:12px;margin:14px 0 6px;color:#334155}
.sub{font-size:10px;color:#64748b;margin:2px 0 12px}
.kop{border-bottom:3px solid #f59e0b;padding-bottom:8px;margin-bottom:12px}
table{width:100%;border-collapse:collapse;margin-bottom:10px}
th{background:#f1f5f9;border:1px solid #cbd5e1;padding:5px 6px;text-align:left;font-size:9px;text-transform:uppercase;color:#475569}
td{border:1px solid #e2e8f0;padding:5px 6px}
tr:nth-child(even) td{background:#f8fafc}
.td-r{text-align:right}
.strong{font-weight:bold}
.cards td{border:1px solid #e2e8f0;padding:7px}
.cards .lbl{font-size:8px;text-transform:uppercase;color:#94a3b8;letter-spacing:.5px}
.cards .val{font-size:13px;font-weight:bold;color:#0f172a}
.green{color:#059669}.red{color:#e11d48}.blue{color:#4f46e5}.amber{color:#d97706}
.total td{background:#f1f5f9;font-weight:bold}
.foot{margin-top:14px;font-size:9px;color:#94a3b8;text-align:right}
</style></head><body>
<div class="kop"><h1>Cendana Resident — Laporan {{ ucfirst($jenis) }}</h1>
<div class="sub">Periode {{ $dari }} s/d {{ $sampai }} • Dicetak {{ now()->translatedFormat('d M Y H:i') }}</div></div>

@php $rupiah = fn($n) => 'Rp '.number_format($n,0,',','.'); @endphp

@switch($jenis)
@case('keuangan')
<table class="cards"><tr>
<td><div class="lbl">Masuk (Kas)</div><div class="val green">{{ $rupiah($masuk) }}</div></td>
<td><div class="lbl">Keluar (Kas)</div><div class="val red">{{ $rupiah($keluar) }}</div></td>
<td><div class="lbl">Bersih Kas</div><div class="val {{ $masuk-$keluar>=0?'green':'red' }}">{{ $rupiah($masuk-$keluar) }}</div></td>
<td><div class="lbl">Belanja Perusahaan</div><div class="val blue">{{ $rupiah($belanjaPerusahaan ?? 0) }}</div></td>
<td><div class="lbl">Transaksi</div><div class="val">{{ $jmlTransaksi ?? 0 }}</div></td>
</tr></table>
<h2>Pemasukan Kas</h2>
<table><tr><th>Tanggal</th><th>Keterangan</th><th class="td-r">Jumlah</th></tr>
@foreach($rows as $x)<tr><td>{{ $x->tanggal }}</td><td>{{ $x->keterangan ?? 'Top Up' }}</td><td class="td-r strong">{{ $rupiah($x->jumlah) }}</td></tr>@endforeach
<tr class="total"><td colspan="2">Total Masuk</td><td class="td-r">{{ $rupiah($masuk) }}</td></tr></table>
<h2>Pengeluaran Kas</h2>
<table><tr><th>Tanggal</th><th>Nomor</th><th>Kategori</th><th>Keterangan</th><th class="td-r">Jumlah</th></tr>
@foreach($rows2 as $x)<tr><td>{{ $x->tanggal }}</td><td>{{ $x->nomor }}</td><td>{{ optional($x->category)->nama ?? '-' }}</td><td>{{ $x->keterangan }}</td><td class="td-r strong">{{ $rupiah($x->jumlah) }}</td></tr>@endforeach
<tr class="total"><td colspan="4">Total Keluar</td><td class="td-r">{{ $rupiah($keluar) }}</td></tr></table>
@if(count($rows3 ?? [])>0)
<h2>Belanja Perusahaan (di luar kas)</h2>
<table><tr><th>Tanggal</th><th>PO</th><th>Supplier</th><th class="td-r">Total</th></tr>
@foreach($rows3 as $p)<tr><td>{{ $p->tanggal }}</td><td>{{ $p->nomor }}</td><td>{{ optional($p->supplier)->nama ?? '-' }}</td><td class="td-r">{{ $rupiah($p->total) }}</td></tr>@endforeach</table>
@endif
@break

@case('okupansi')
<table class="cards"><tr>
<td><div class="lbl">Total Kamar</div><div class="val">{{ $total }}</div></td>
<td><div class="lbl">Terisi</div><div class="val green">{{ $terisi }} ({{ $persen }}%)</div></td>
<td><div class="lbl">Tersedia</div><div class="val blue">{{ $tersedia }}</div></td>
<td><div class="lbl">Maintenance</div><div class="val amber">{{ $maintenance }}</div></td>
</tr></table>
<table><tr><th>Kamar</th><th>Gedung</th><th>Tipe</th><th>Lantai</th><th>Penghuni</th><th>Status</th></tr>
@foreach($rows as $rm)<tr><td class="strong">{{ $rm->nomor }}</td><td>{{ optional($rm->building)->nama ?? '-' }}</td><td>{{ optional($rm->roomType)->nama ?? '-' }}</td><td>{{ $rm->lantai }}</td><td>{{ optional(optional($rm->activeTenancy)->tenant)->nama ?? '-' }}</td><td>{{ ucfirst($rm->status) }}</td></tr>@endforeach</table>
@break

@case('inventaris')
<table class="cards"><tr>
<td><div class="lbl">Total Unit</div><div class="val">{{ count($rows) }}</div></td>
<td><div class="lbl">Terpasang</div><div class="val blue">{{ $jmlTerpasang }}</div></td>
<td><div class="lbl">Di Gudang</div><div class="val">{{ $jmlGudang }}</div></td>
<td><div class="lbl">Rusak / Dibuang</div><div class="val red">{{ $jmlRusak }} / {{ $jmlDibuang }}</div></td>
<td><div class="lbl">Total Nilai Buku</div><div class="val green">{{ $rupiah($totalBuku) }}</div></td>
</tr></table>
<table><tr><th>Barcode</th><th>Barang</th><th>Lokasi</th><th>Kondisi</th><th>Status</th><th class="td-r">Perolehan</th><th class="td-r">Nilai Buku</th><th>Beli</th></tr>
@foreach($rows as $x)<tr><td>{{ $x->kode_barcode }}</td><td>{{ optional($x->item)->nama }}</td><td>{{ optional(optional($x->activePlacement)->room)->nomor ?? 'Gudang' }}</td><td>{{ $x->status=='dibuang' ? 'Dibuang' : ucfirst(str_replace('_',' ',$x->kondisi ?? 'baik')) }}</td><td>{{ ucfirst($x->status) }}</td><td class="td-r">{{ $rupiah($x->harga_perolehan) }}</td><td class="td-r strong">{{ $rupiah($x->nilai_buku) }}</td><td>{{ $x->tanggal_beli }}</td></tr>@endforeach
<tr class="total"><td colspan="5">Total {{ count($rows) }} unit</td><td class="td-r">{{ $rupiah($totalPerolehan) }}</td><td class="td-r">{{ $rupiah($totalBuku) }}</td><td></td></tr></table>
@break

@case('pembelian')
<table class="cards"><tr>
<td><div class="lbl">Total Belanja</div><div class="val">{{ $rupiah($totalBelanja) }}</div></td>
<td><div class="lbl">Dana Kas</div><div class="val green">{{ $rupiah($totalKas) }}</div></td>
<td><div class="lbl">Dana Perusahaan</div><div class="val blue">{{ $rupiah($totalPerusahaan) }}</div></td>
<td><div class="lbl">PO</div><div class="val">{{ count($rows) }}</div></td>
<td><div class="lbl">Item Dibeli</div><div class="val">{{ $jmlItem }} unit</div></td>
</tr></table>
<table><tr><th>Tanggal</th><th>Nomor</th><th>Supplier</th><th>Dana</th><th>Item</th><th class="td-r">Total</th></tr>
@foreach($rows as $p)<tr><td>{{ $p->tanggal }}</td><td>{{ $p->nomor }}</td><td>{{ optional($p->supplier)->nama ?? '-' }}</td><td>{{ ucfirst($p->sumber_dana ?? 'kas') }}</td><td>{{ $p->items->map(fn($i)=>$i->qty.'x '.optional($i->item)->nama)->implode(', ') }}</td><td class="td-r strong">{{ $rupiah($p->total) }}</td></tr>@endforeach
<tr class="total"><td colspan="5">Total {{ count($rows) }} PO</td><td class="td-r">{{ $rupiah($totalBelanja) }}</td></tr></table>
@break

@case('penyusutan')
<table class="cards"><tr>
<td><div class="lbl">Total Perolehan</div><div class="val">{{ $rupiah($totalPerolehan) }}</div></td>
<td><div class="lbl">Total Nilai Buku</div><div class="val blue">{{ $rupiah($totalBuku) }}</div></td>
<td><div class="lbl">Akumulasi Susut</div><div class="val red">{{ $rupiah($akumulasiSusut) }}</div></td>
<td><div class="lbl">Persen Susut</div><div class="val amber">{{ $persenSusut }}%</div></td>
<td><div class="lbl">Susut {{ $periode }}</div><div class="val">{{ $rupiah($susutPeriode) }}</div></td>
</tr></table>
<table><tr><th>Barcode</th><th>Barang</th><th class="td-r">Perolehan</th><th class="td-r">Residu</th><th class="td-r">Akum. Susut</th><th class="td-r">Nilai Buku</th><th class="td-r">% Susut</th></tr>
@foreach($rows as $x)@php $susut=$x->harga_perolehan-$x->nilai_buku;$pc=$x->harga_perolehan?min(100,round($susut/$x->harga_perolehan*100)):0;@endphp
<tr><td>{{ $x->kode_barcode }}</td><td>{{ optional($x->item)->nama }}</td><td class="td-r">{{ $rupiah($x->harga_perolehan) }}</td><td class="td-r">{{ $rupiah($x->nilai_residu) }}</td><td class="td-r">{{ $rupiah($susut) }}</td><td class="td-r strong">{{ $rupiah($x->nilai_buku) }}</td><td class="td-r">{{ $pc }}%</td></tr>
@endforeach
<tr class="total"><td colspan="2">Total {{ count($rows) }} unit</td><td class="td-r">{{ $rupiah($totalPerolehan) }}</td><td></td><td class="td-r">{{ $rupiah($akumulasiSusut) }}</td><td class="td-r">{{ $rupiah($totalBuku) }}</td><td class="td-r">{{ $persenSusut }}%</td></tr></table>
@break

@default
<table class="cards"><tr>
<td><div class="lbl">Perlu Diganti</div><div class="val red">{{ $jmlPerlu }}</div></td>
<td><div class="lbl">Terlambat</div><div class="val amber">{{ $jmlTerlambat }}</div></td>
<td><div class="lbl">Segera (90 hr)</div><div class="val blue">{{ $jmlSegera }}</div></td>
<td><div class="lbl">Total Terjadwal</div><div class="val">{{ count($rows) }}</div></td>
</tr></table>
<table><tr><th>Barcode</th><th>Barang</th><th>Kamar</th><th>Rencana Ganti</th><th>Kondisi</th><th>Status Jadwal</th></tr>
@foreach($rows as $u)@php
$lewat=$u->kondisi!=='perlu_diganti'&&$u->tanggal_ganti_rencana&&$u->tanggal_ganti_rencana<=now()->toDateString();
$dekat=!$lewat&&$u->kondisi==='baik'&&$u->tanggal_ganti_rencana&&$u->tanggal_ganti_rencana<=now()->addDays(90)->toDateString();
@endphp
<tr><td>{{ $u->kode_barcode }}</td><td>{{ optional($u->item)->nama }}</td><td>{{ optional(optional($u->activePlacement)->room)->nomor ?? 'Gudang' }}</td><td>{{ $u->tanggal_ganti_rencana }}</td><td>{{ $u->kondisi=='perlu_diganti'?'Perlu Diganti':ucfirst($u->kondisi ?? 'baik') }}</td><td>{{ $u->kondisi=='perlu_diganti'?'Perlu Diganti':($lewat?'Terlambat':($dekat?'Segera':'Terjadwal')) }}</td></tr>
@endforeach</table>
@endswitch

<div class="foot">Cendana Resident — dokumen dihasilkan otomatis oleh sistem • {{ now()->format('Y-m-d H:i') }}</div>
</body></html>

