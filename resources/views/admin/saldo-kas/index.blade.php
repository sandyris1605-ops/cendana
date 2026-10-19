@extends('layouts.admin')
@section('title','Saldo Kas')
@section('content')
@include('admin.partials.header', ['judul'=>'Saldo Kas', 'sub'=>'Isi saldo untuk biaya pengeluaran (air, listrik, dll) — pengeluaran otomatis mengurangi saldo', 'ctaHref'=>route('admin.expenses.index'), 'ctaText'=>'Lihat Pengeluaran', 'ctaIcon'=>'fa-wallet'])
@php
$porsiKeluar = $totalMasuk > 0 ? min(100, max(0, round($totalKeluar / $totalMasuk * 100))) : 0;
$sisaPersen  = $totalMasuk > 0 ? min(100, max(0, round($saldo / $totalMasuk * 100))) : 0;
@endphp
@if($saldo < 0)
<div class="flex items-start gap-3 bg-amber-50 border border-amber-200 text-amber-800 px-4 py-3 rounded-2xl mb-4 text-sm"><i class="fa fa-triangle-exclamation mt-0.5"></i><div>Saldo kas minus <strong>Rp {{ number_format(abs($saldo),0,',','.') }}</strong> karena ada pengeluaran yang tercatat sebelum fitur saldo. Top up untuk menutupinya agar pengeluaran baru bisa dicatat.</div></div>
@endif

{{-- Kartu statistik --}}
<div class="grid md:grid-cols-3 gap-4 mb-4">
  <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 kas-stat" style="animation-delay:0ms">
    <div class="flex items-center justify-between">
      <div class="text-xs font-medium uppercase tracking-wider text-slate-400">Saldo Saat Ini</div>
      <div class="w-9 h-9 rounded-lg {{ $saldo>=0 ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600' }} flex items-center justify-center"><i class="fa fa-wallet"></i></div>
    </div>
    <div class="text-3xl font-bold tracking-tight mt-2 {{ $saldo>=0 ? 'text-emerald-600' : 'text-rose-600' }}" data-count="{{ $saldo }}" data-prefix="Rp">Rp {{ number_format($saldo,0,',','.') }}</div>
    <div class="mt-3 h-1.5 rounded-full bg-slate-100 overflow-hidden"><div class="kas-bar h-full {{ $saldo>=0 ? 'bg-emerald-500' : 'bg-rose-500' }} rounded-full" data-w="{{ $sisaPersen }}%" style="width:0%"></div></div>
    <div class="text-xs text-slate-500 mt-2">{{ $sisaPersen }}% dari total dana masuk</div>
  </div>
  <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 kas-stat" style="animation-delay:80ms">
    <div class="flex items-center justify-between">
      <div class="text-xs font-medium uppercase tracking-wider text-slate-400">Total Top Up</div>
      <div class="w-9 h-9 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center"><i class="fa fa-arrow-trend-up"></i></div>
    </div>
    <div class="text-3xl font-bold tracking-tight text-emerald-600 mt-2" data-count="{{ $totalMasuk }}" data-prefix="Rp">Rp {{ number_format($totalMasuk,0,',','.') }}</div>
    <div class="text-xs text-slate-500 mt-2">Seluruh dana yang masuk</div>
  </div>
  <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 kas-stat" style="animation-delay:160ms">
    <div class="flex items-center justify-between">
      <div class="text-xs font-medium uppercase tracking-wider text-slate-400">Total Pengeluaran</div>
      <div class="w-9 h-9 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center"><i class="fa fa-arrow-trend-down"></i></div>
    </div>
    <div class="text-3xl font-bold tracking-tight text-rose-600 mt-2" data-count="{{ $totalKeluar }}" data-prefix="Rp">Rp {{ number_format($totalKeluar,0,',','.') }}</div>
    <div class="mt-3 h-1.5 rounded-full bg-slate-100 overflow-hidden"><div class="kas-bar h-full bg-rose-500 rounded-full" data-w="{{ $porsiKeluar }}%" style="width:0%"></div></div>
    <div class="text-xs text-slate-500 mt-2">{{ $porsiKeluar }}% dari total dana masuk</div>
  </div>
</div>

<div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm mb-4">
<div class="text-xs font-semibold uppercase tracking-wider text-emerald-600 mb-3"><i class="fa fa-plus-circle mr-1"></i>Top Up Saldo</div>
<form method="POST" action="{{ route('admin.saldo-kas.topup') }}" class="flex flex-wrap items-center gap-2">
@csrf
<input type="date" name="tanggal" value="{{ old('tanggal', date('Y-m-d')) }}" class="border border-slate-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-emerald-500">
<input type="number" name="jumlah" value="{{ old('jumlah') }}" placeholder="Jumlah (Rp)" min="1" class="border border-slate-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-emerald-500" style="min-width:180px">
<input name="keterangan" value="{{ old('keterangan') }}" placeholder="Keterangan (mis. Setoran awal)" class="flex-1 border border-slate-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-emerald-500" style="min-width:220px">
<button class="bg-emerald-500 hover:bg-emerald-600 text-white px-5 py-2 rounded-lg text-sm font-semibold shadow-sm transition"><i class="fa fa-plus mr-1 text-xs"></i>Top Up</button>
</form>
@error('jumlah')<p class="text-xs text-rose-600 mt-2"><i class="fa fa-circle-exclamation mr-1"></i>{{ $message }}</p>@enderror
@error('tanggal')<p class="text-xs text-rose-600 mt-2"><i class="fa fa-circle-exclamation mr-1"></i>{{ $message }}</p>@enderror
</div>
@include('admin.partials.search', ['placeholder'=>'Cari uraian / nomor PO...', 'extra'=>'<select name="jenis" class="border border-slate-200 rounded-lg px-3 py-2 text-sm bg-white outline-none focus:border-amber-500" onchange="this.form.submit()"><option value="">Semua jenis</option><option value="masuk" '.(request('jenis')=='masuk'?'selected':'').'>Masuk</option><option value="keluar" '.(request('jenis')=='keluar'?'selected':'').'>Keluar</option></select>'])
<div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-x-auto">
<table class="w-full text-sm">
<thead><tr class="bg-gradient-to-r from-slate-50 to-slate-100 text-left text-xs uppercase tracking-wider text-slate-500 border-b border-slate-200">
<th class="px-4 py-3">Tanggal</th><th class="px-4 py-3">Jenis</th><th class="px-4 py-3">Uraian</th><th class="px-4 py-3">Jumlah</th><th class="px-4 py-3 text-right">Saldo Sesudah</th><th class="px-4 py-3 text-right">Aksi</th>
</tr></thead>
<tbody class="divide-y divide-slate-100">
@forelse($mutasi as $idx => $m)
@php $masuk = $m['jenis']=='masuk'; @endphp
<tr class="kas-baris hover:bg-slate-50/80 transition" style="animation-delay:{{ min($idx,15) * 45 }}ms">
<td class="px-4 py-3 text-slate-500 whitespace-nowrap">{{ \Illuminate\Support\Carbon::parse($m['tanggal'])->translatedFormat('d M Y') }}</td>
<td class="px-4 py-3"><span class="inline-flex items-center gap-1.5 text-[11px] px-2.5 py-1 rounded-full border font-medium {{ $masuk ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-rose-50 text-rose-700 border-rose-200' }}"><i class="fa {{ $masuk ? 'fa-arrow-down' : 'fa-arrow-up' }} text-[9px]"></i>{{ strtoupper($m['jenis']) }}</span></td>
<td class="px-4 py-3">{{ $m['uraian'] }}@if(!empty($m['ref']))<div class="mt-1"><a href="{{ $m['ref_url'] }}" class="inline-block text-[11px] px-2 py-0.5 rounded-full bg-sky-50 text-sky-700 border border-sky-200 hover:bg-sky-100 transition font-mono"><i class="fa fa-cart-shopping mr-1"></i>{{ $m['ref'] }}</a></div>@endif</td>
<td class="px-4 py-3 font-semibold whitespace-nowrap {{ $masuk ? 'text-emerald-600' : 'text-rose-600' }}"><i class="fa {{ $masuk ? 'fa-plus' : 'fa-minus' }} text-[10px] mr-0.5 opacity-70"></i>Rp {{ number_format($m['jumlah'],0,',','.') }}</td>
<td class="px-4 py-3 text-right font-semibold text-slate-700 whitespace-nowrap">Rp {{ number_format($m['saldo_sesudah'],0,',','.') }}</td>
<td class="px-4 py-3 text-right whitespace-nowrap">
@if($masuk && !empty($m['topup_id']))
<form method="POST" action="{{ route('admin.saldo-kas.topup.destroy', $m['topup_id']) }}" onsubmit="return konfirmasiHapus('Hapus top up Rp {{ number_format($m['jumlah'],0,',','.') }} pada {{ $m['tanggal'] }}?\n\nSaldo kas otomatis berkurang sebesar nominal tersebut.', this)">
@csrf @method('DELETE')
<button type="submit" class="flex items-center gap-1.5 px-3 py-1.5 text-sm bg-white border border-rose-200 hover:bg-rose-50 hover:text-rose-600 hover:border-rose-300 text-rose-600 rounded-lg transition shadow-sm"><i class="fa fa-trash text-xs"></i> Hapus</button>
</form>
@else
<span class="text-slate-300">-</span>
@endif
</td>
</tr>
@empty
<tr><td colspan="6" class="px-4 py-10 text-center text-slate-400"><i class="fa fa-sack-dollar text-2xl mb-2 block"></i>Belum ada mutasi. Top up saldo untuk memulai.</td></tr>
@endforelse
</tbody>
</table>
</div>
@include('admin.partials.pager', ['paginator'=>$mutasi])
<style>
@keyframes kasNaik { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }
.kas-stat { opacity: 0; animation: kasNaik .45s ease both; }
.kas-baris { opacity: 0; animation: kasNaik .35s ease both; }
@media (prefers-reduced-motion: reduce) { .kas-stat, .kas-baris { animation: none; opacity: 1; } }
</style>
<script>
(function () {
  var nf = new Intl.NumberFormat('id-ID');
  // Count-up angka pada kartu statistik
  document.querySelectorAll('[data-count]').forEach(function (el) {
    var target = parseFloat(el.getAttribute('data-count')) || 0;
    var prefix = el.getAttribute('data-prefix') || '';
    var dur = 900, start = null;
    function frame(ts) {
      if (!start) start = ts;
      var p = Math.min((ts - start) / dur, 1);
      var eased = 1 - Math.pow(1 - p, 3);
      el.textContent = prefix + ' ' + nf.format(Math.round(target * eased));
      if (p < 1) requestAnimationFrame(frame);
    }
    requestAnimationFrame(frame);
  });
  // Animasi progress bar
  requestAnimationFrame(function () {
    setTimeout(function () {
      document.querySelectorAll('.kas-bar').forEach(function (b) { b.style.transition = 'width .9s cubic-bezier(.4,0,.2,1)'; b.style.width = b.getAttribute('data-w'); });
    }, 80);
  });
})();
</script>
@endsection
