@extends('layouts.admin')
@section('title','Dashboard')
@section('content')
@php $fmtRp = fn($v) => 'Rp '.number_format($v,0,',','.'); @endphp
<div class="grid md:grid-cols-4 gap-4 mb-6">
<div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
<div class="flex items-center justify-between">
<div class="text-xs text-slate-500 font-medium uppercase tracking-wider">Okupansi</div>
<div class="w-9 h-9 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center"><i class="fa fa-bed"></i></div>
</div>
<div class="text-2xl font-bold mt-2">{{ $terisi }}<span class="text-slate-400 text-lg">/{{ $totalKamar }}</span> <span class="text-emerald-600 text-lg">({{ $okupansi }}%)</span></div>
<div class="mt-3 h-1.5 rounded-full bg-slate-100 overflow-hidden"><div class="h-full bg-emerald-500 rounded-full" style="width:{{ $okupansi }}%"></div></div>
<div class="text-xs text-slate-500 mt-2">{{ $tersedia }} kamar tersedia</div>
</div>
<div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
<div class="flex items-center justify-between">
<div class="text-xs text-slate-500 font-medium uppercase tracking-wider">Dana Masuk Bulan Ini</div>
<div class="w-9 h-9 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center"><i class="fa fa-arrow-trend-up"></i></div>
</div>
<div class="text-2xl font-bold mt-2 text-indigo-600">{{ $fmtRp($kasBulan) }}</div>
<div class="text-xs text-slate-500 mt-1">Periode {{ now()->format('F Y') }}</div>
</div>
<div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
<div class="flex items-center justify-between">
<div class="text-xs text-slate-500 font-medium uppercase tracking-wider">Pengeluaran Bulan Ini</div>
<div class="w-9 h-9 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center"><i class="fa fa-arrow-trend-down"></i></div>
</div>
<div class="text-2xl font-bold mt-2 text-rose-600">{{ $fmtRp($keluarBulan) }}</div>
<div class="text-xs text-slate-500 mt-1">Saldo bulan ini {{ $fmtRp($kasBulan - $keluarBulan) }}</div>
</div>
<div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
<div class="flex items-center justify-between">
<div class="text-xs text-slate-500 font-medium uppercase tracking-wider">Nilai Buku Aset</div>
<div class="w-9 h-9 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center"><i class="fa fa-gem"></i></div>
</div>
<div class="text-2xl font-bold mt-2 text-amber-600">{{ $fmtRp($nilaiAset) }}</div>
<div class="text-xs text-slate-500 mt-1">Setelah penyusutan berjalan</div>
</div>
</div>
<div class="grid md:grid-cols-3 gap-4 mb-6">
<div class="md:col-span-2 bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
<div class="flex items-center justify-between mb-4">
<h3 class="font-semibold">Saldo Kas 6 Bulan</h3>
<div class="flex gap-4 text-xs text-slate-500">
<span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span> Saldo berjalan</span>
</div>
</div>
<canvas id="chartKas" height="120"></canvas>
</div>
<div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
<h3 class="font-semibold mb-1">Okupansi Kamar</h3>
<div class="text-xs text-slate-400 mb-3">{{ $okupansi }}% terisi</div>
<div class="max-w-[220px] mx-auto"><canvas id="chartOkupansi"></canvas></div>
</div>
</div>
<div class="grid md:grid-cols-3 gap-4 mb-6">
<div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
<div class="flex items-center justify-between mb-3">
<h3 class="font-semibold">Pengeluaran Terakhir</h3>
<a href="{{ route('admin.expenses.index') }}" class="text-xs text-rose-600 hover:underline">Pengeluaran →</a>
</div>
<div class="space-y-2">
@forelse($keluarBaru as $e)
<div class="flex items-center justify-between text-sm border-b last:border-0 pb-2">
<div>
<div class="font-medium">{{ $e->keterangan ?? $e->nomor }}</div>
<div class="text-xs text-slate-400">{{ \Illuminate\Support\Carbon::parse($e->tanggal)->translatedFormat('d M Y') }}@if($e->category) • {{ $e->category->nama }}@endif</div>
</div>
<div class="text-rose-600 font-medium text-sm">- {{ $fmtRp($e->jumlah) }}</div>
</div>
@empty
<div class="text-sm text-slate-400 text-center py-3">Belum ada pengeluaran tercatat.</div>
@endforelse
</div>
</div>
<div class="md:col-span-2 bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
<div class="flex items-center justify-between mb-3">
<h3 class="font-semibold">Barang Perlu Diganti @if(($jmlPerluDiganti ?? 0) > 0)<span class="text-[11px] px-2 py-0.5 rounded-full bg-rose-500 text-white font-semibold ml-1">{{ $jmlPerluDiganti }} mendesak</span>@endif</h3>
<a href="{{ route('admin.penggantian') }}" class="text-xs text-indigo-600 hover:underline">Lihat semua →</a>
</div>
<div class="overflow-x-auto">
<table class="w-full text-sm">
<thead><tr class="text-left text-xs uppercase tracking-wider text-slate-400 border-b"><th class="pb-2">Barcode</th><th class="pb-2">Barang</th><th class="pb-2">Rencana Ganti</th><th class="pb-2">Sisa</th></tr></thead>
<tbody>
@forelse($ganti as $g)
@php $sisa = $g->tanggal_ganti_rencana ? now()->startOfDay()->diffInDays(\Illuminate\Support\Carbon::parse($g->tanggal_ganti_rencana), false) : null; $perluG = $g->kondisi === 'perlu_diganti'; @endphp
<tr class="border-b last:border-0 hover:bg-slate-50 {{ $perluG ? 'bg-rose-50/60' : '' }}">
<td class="py-2 font-mono text-xs">{{ $g->kode_barcode }}</td>
<td class="py-2">{{ optional($g->item)->nama }}</td>
<td class="py-2">{{ $g->tanggal_ganti_rencana ? \Illuminate\Support\Carbon::parse($g->tanggal_ganti_rencana)->translatedFormat('d M Y') : '-' }}</td>
<td class="py-2">@if($perluG)<span class="text-[11px] px-2 py-0.5 rounded-full bg-rose-500 text-white font-semibold">Perlu Diganti</span>@else<span class="text-[11px] px-2 py-0.5 rounded-full {{ $sisa<0?'bg-rose-50 text-rose-600 border border-rose-200':($sisa<=30?'bg-amber-50 text-amber-700 border border-amber-200':'bg-slate-100 text-slate-600') }}">{{ $sisa<0?'Terlambat '.abs($sisa).' hr':$sisa.' hr lagi' }}</span>@endif</td>
</tr>
@empty
<tr><td colspan="4" class="py-3 text-slate-400 text-center">Tidak ada barang dalam 90 hari ke depan.</td></tr>
@endforelse
</tbody>
</table>
</div>
</div>
</div>
<div class="grid md:grid-cols-3 gap-4">
<div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
<div class="flex items-center justify-between mb-3">
<h3 class="font-semibold">Pemasukan Terakhir</h3>
<a href="{{ route('admin.saldo-kas.index') }}" class="text-xs text-indigo-600 hover:underline">Saldo Kas →</a>
</div>
<div class="space-y-2">
@forelse(\App\Models\TopUp::orderByDesc('tanggal')->orderByDesc('id')->limit(8)->get() as $t)
<div class="flex items-center justify-between text-sm border-b last:border-0 pb-2">
<div>
<div class="font-medium">{{ $t->keterangan ?? 'Top Up' }}</div>
<div class="text-xs text-slate-400">{{ \Illuminate\Support\Carbon::parse($t->tanggal)->translatedFormat('d M Y') }}</div>
</div>
<div class="text-emerald-600 font-medium text-sm">+ {{ $fmtRp($t->jumlah) }}</div>
</div>
@empty
<div class="text-sm text-slate-400 text-center py-3">Belum ada pemasukan tercatat.</div>
@endforelse
</div>
</div>
<div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
<h3 class="font-semibold mb-3">Booking Baru</h3>
<div class="space-y-2">
@forelse($bookBaru as $b)
<div class="flex items-center gap-3 text-sm border-b last:border-0 pb-2">
<div class="w-8 h-8 rounded-full bg-slate-900 text-white flex items-center justify-center text-xs font-bold">{{ strtoupper(substr($b->nama,0,1)) }}</div>
<div>
<div class="font-medium">{{ $b->nama }}</div>
<div class="text-xs text-slate-400">{{ $b->telepon }}</div>
</div>
</div>
@empty
<div class="text-sm text-slate-400 text-center py-3">Tidak ada booking baru.</div>
@endforelse
</div>
</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<script>
const rp = v => 'Rp ' + new Intl.NumberFormat('id-ID').format(v);
const jt = v => (v/1000000).toLocaleString('id-ID') + ' jt';
new Chart(document.getElementById('chartKas'), {
type: 'line',
data: {
labels: @json($label6),
datasets: [
{ label: 'Saldo', data: @json($saldo6), borderColor: '#10b981', backgroundColor: 'rgba(16,185,129,.12)', fill: true, tension: .35, borderWidth: 2.5, pointBackgroundColor: '#10b981', pointBorderColor: '#10b981', pointRadius: 4, pointHoverRadius: 6 }
]
},
options: {
responsive: true, plugins: { legend: { display: false }, tooltip: { callbacks: { label: c => ' ' + rp(c.parsed.y) } } },
scales: { y: { ticks: { callback: v => jt(v) }, grid: { color: 'rgba(148,163,184,.15)' } }, x: { grid: { display: false } } }
}
});
new Chart(document.getElementById('chartOkupansi'), {
type: 'doughnut',
data: { labels: ['Terisi','Tersedia'], datasets: [{ data: [{{ $terisi }}, {{ $tersedia }}], backgroundColor: ['#10b981','#e2e8f0'], borderWidth: 0, hoverOffset: 6 }] },
options: { responsive: true, cutout: '70%', plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, usePointStyle: true, font: { size: 11 } } } } }
});
</script>
@endsection
