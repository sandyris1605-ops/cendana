@extends('layouts.admin')
@section('title','Detail Kamar '.$room->nomor)
@section('content')
@php
$penghuni = optional(optional($room->activeTenancy)->tenant)->nama;
$jmlBarang = $room->assets->where('status','aktif')->count();
$jmlKontrak = $room->tenancies->count();
@endphp
<a href="{{ route('admin.rooms.index') }}" class="inline-flex items-center gap-2 text-sm text-slate-500 hover:text-slate-800 transition mb-4"><i class="fa fa-arrow-left text-xs"></i>Daftar Kamar</a>
<div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5 md:p-6 mb-4">
<div class="flex flex-wrap items-center gap-4">
<div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-indigo-500 to-violet-600 text-white flex items-center justify-center text-xl shadow-sm shrink-0"><i class="fa fa-bed"></i></div>
<div class="min-w-0 flex-1">
<div class="text-[11px] uppercase tracking-widest text-slate-400 font-semibold">Kamar</div>
<h1 class="text-2xl font-bold tracking-tight">Kamar {{ $room->nomor }}</h1>
<p class="text-sm text-slate-500 mt-0.5">{{ optional($room->roomType)->nama ?? '-' }} • Ukuran {{ optional($room->roomType)->ukuran ?? '-' }} • Lantai {{ $room->lantai ?? '-' }}</p>
</div>
<span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold border {{ $badge[$room->status] ?? 'bg-slate-100 text-slate-600 border-slate-200' }}">{{ ucfirst($room->status) }}</span>
@if($room->status==='tersedia')
<form method="POST" action="{{ route('admin.rooms.destroy',$room) }}" class="inline" onsubmit="return konfirmasiHapus('Hapus kamar {{ $room->nomor }}? Riwayat hunian ikut terhapus permanen. Pastikan barang sudah dilepas.', this)">@csrf @method('DELETE')<button class="inline-flex items-center gap-2 bg-rose-500 hover:bg-rose-600 text-white px-4 py-2 rounded-lg text-sm font-semibold shadow-sm transition"><i class="fa fa-trash text-xs"></i>Hapus</button></form>
@endif
<a href="{{ route('admin.barcode.cetak',['room_id'=>$room->id]) }}" target="_blank" class="inline-flex items-center gap-2 bg-slate-900 hover:bg-slate-800 text-white px-4 py-2 rounded-lg text-sm font-semibold shadow-sm transition"><i class="fa fa-barcode text-xs"></i>Cetak Barcode</a>
@if($room->status==='terisi' && $room->activeTenancy)
<button type="button" onclick="openPindah();return false" class="inline-flex items-center gap-2 bg-indigo-500 hover:bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-semibold shadow-sm transition"><i class="fa fa-right-left text-xs"></i>Pindahkan Penghuni + Barang</button>
@endif
<a href="{{ route('admin.rooms.edit',$room) }}" class="inline-flex items-center gap-2 bg-amber-500 hover:bg-amber-600 text-slate-900 px-4 py-2 rounded-lg text-sm font-semibold shadow-sm transition"><i class="fa fa-pen text-xs"></i>Edit</a>
</div>
<div class="grid grid-cols-2 md:grid-cols-4 gap-3 mt-5 pt-5 border-t border-slate-100">
<div><div class="text-[11px] uppercase tracking-wider text-slate-400 font-semibold">Gedung</div><div class="font-semibold text-sm mt-0.5">{{ optional($room->building)->nama ?? '-' }}</div></div>
<div><div class="text-[11px] uppercase tracking-wider text-slate-400 font-semibold">Tipe</div><div class="font-semibold text-sm mt-0.5">{{ optional($room->roomType)->nama ?? '-' }}</div></div>
<div><div class="text-[11px] uppercase tracking-wider text-slate-400 font-semibold">Lantai</div><div class="font-semibold text-sm mt-0.5">Lantai {{ $room->lantai ?? '-' }}</div></div>
<div><div class="text-[11px] uppercase tracking-wider text-slate-400 font-semibold">Penghuni</div><div class="font-semibold text-sm mt-0.5">{{ $penghuni ?? '—' }}</div></div>
</div>
</div>
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
<div class="bg-white rounded-xl border border-slate-200 shadow-sm p-4 flex items-center gap-3">
<div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0"><i class="fa fa-boxes-stacked"></i></div>
<div><div class="text-[11px] uppercase tracking-wider text-slate-400 font-semibold">Barang Aktif</div><div class="text-xl font-bold">{{ $jmlBarang }} <span class="text-xs font-medium text-slate-400">unit</span></div></div>
</div>
<div class="bg-white rounded-xl border border-slate-200 shadow-sm p-4 flex items-center gap-3">
<div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0"><i class="fa fa-file-contract"></i></div>
<div><div class="text-[11px] uppercase tracking-wider text-slate-400 font-semibold">Riwayat Hunian</div><div class="text-xl font-bold">{{ $jmlKontrak }} <span class="text-xs font-medium text-slate-400">kontrak</span></div></div>
</div>
</div>
<div class="grid lg:grid-cols-2 gap-4">
<div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
<div class="flex items-center gap-3 mb-4">
<div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center"><i class="fa fa-bed"></i></div>
<div><div class="font-bold">Barang di Kamar</div><div class="text-xs text-slate-400">{{ $jmlBarang }} unit aktif • Penghuni: {{ $penghuni ?? '-' }}</div></div>
@if($jmlBarang > 0)
<a href="{{ route('admin.barcode.cetak',['room_id'=>$room->id]) }}" target="_blank" class="ml-auto inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-amber-600 border border-slate-200 hover:border-amber-400 px-2.5 py-1.5 rounded-lg transition"><i class="fa fa-barcode"></i>Cetak Barcode</a>
@endif
</div>
<div class="divide-y divide-slate-100">
@forelse($room->assets->where('status','aktif') as $a)
@php $unit = $a->unit; $perlu = (optional($unit)->kondisi ?? '')=='perlu_diganti'; @endphp
<div class="flex items-center gap-3 py-2.5 text-sm">
<div class="w-9 h-9 rounded-lg {{ $perlu ? 'bg-rose-50 text-rose-600' : 'bg-slate-100 text-slate-500' }} flex items-center justify-center shrink-0"><i class="fa fa-box text-xs"></i></div>
<div class="min-w-0 flex-1"><div class="font-medium truncate">{{ optional(optional($unit)->item)->nama ?? '-' }}</div><div class="text-xs text-slate-400 font-mono">{{ optional($unit)->kode_barcode ?? '-' }}</div></div>
@if($unit)
<a href="{{ route('admin.assets.show',$unit) }}" title="Lihat detail aset" class="text-slate-300 hover:text-amber-600 transition shrink-0"><i class="fa fa-arrow-up-right-from-square text-xs"></i></a>
@endif
<span class="text-[11px] px-2 py-0.5 rounded-full border font-medium shrink-0 {{ $perlu ? 'bg-rose-500 text-white border-rose-500' : 'bg-slate-100 text-slate-600 border-slate-200' }}">{{ $perlu ? 'Perlu Diganti' : (optional($unit)->kondisi ?? '-') }}</span>
</div>
@empty
<div class="text-sm text-slate-400 text-center py-6"><i class="fa fa-box-open text-2xl mb-2 block text-slate-300"></i>Belum ada barang terpasang.</div>
@endforelse
</div>
</div>
<div class="space-y-4">
<div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
<div class="flex items-center gap-3 mb-4">
<div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center"><i class="fa fa-clock-rotate-left"></i></div>
<div><div class="font-bold">Riwayat Penghuni</div><div class="text-xs text-slate-400">{{ $jmlKontrak }} kontrak tercatat</div></div>
</div>
<div class="divide-y divide-slate-100">
@forelse($room->tenancies as $t)
@php $namaTenant = optional($t->tenant)->nama ?? '-'; @endphp
<div class="flex items-center gap-3 py-2.5 text-sm">
<div class="w-9 h-9 rounded-full bg-slate-900 text-amber-400 flex items-center justify-center text-xs font-bold shrink-0">{{ strtoupper(substr($namaTenant,0,1)) }}</div>
<div class="min-w-0 flex-1"><div class="font-medium truncate">{{ $namaTenant }}</div><div class="text-xs text-slate-400">Masuk {{ \Illuminate\Support\Carbon::parse($t->tanggal_masuk)->translatedFormat('d M Y') }}@if($t->tanggal_keluar) • Keluar {{ \Illuminate\Support\Carbon::parse($t->tanggal_keluar)->translatedFormat('d M Y') }}@endif</div></div>
<span class="text-[11px] px-2 py-0.5 rounded-full font-medium border shrink-0 {{ $t->status=='aktif' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-slate-100 text-slate-500 border-slate-200' }}">{{ ucfirst($t->status) }}</span>
</div>
@empty
<div class="text-sm text-slate-400 text-center py-6"><i class="fa fa-users text-2xl mb-2 block text-slate-300"></i>Belum ada riwayat penghuni.</div>
@endforelse
</div>
</div>
</div>
</div>

@if($room->status==='terisi' && $room->activeTenancy)
<div id="modalPindah" class="fixed inset-0 z-50 hidden">
  <div class="absolute inset-0 bg-black/50" onclick="closePindah()"></div>
  <div class="relative min-h-full flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden">
      <div class="flex items-center gap-3 px-5 py-4 border-b border-slate-100">
        <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0"><i class="fa fa-right-left"></i></div>
        <div class="flex-1">
          <div class="font-bold">Pindahkan Penghuni + Barang</div>
          <div class="text-xs text-slate-400">Dari Kamar {{ $room->nomor }} ke kamar tujuan</div>
        </div>
        <button type="button" onclick="closePindah()" class="text-slate-400 hover:text-slate-600 w-8 h-8 flex items-center justify-center"><i class="fa fa-xmark"></i></button>
      </div>
      <form method="POST" action="{{ route('admin.tenancies.pindah', $room->activeTenancy) }}" class="p-5 space-y-4">
        @csrf
        <div class="bg-slate-50 border border-slate-100 rounded-lg p-3 text-sm">
          <div class="flex items-center justify-between">
            <span class="text-slate-500">Penghuni</span>
            <span class="font-semibold">{{ optional(optional($room->activeTenancy)->tenant)->nama ?? '-' }}</span>
          </div>
          <div class="flex items-center justify-between mt-1.5">
            <span class="text-slate-500">Kamar asal</span>
            <span class="font-semibold">Kamar {{ $room->nomor }}</span>
          </div>
        </div>
        <div>
          <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">Kamar Tujuan <span class="text-rose-500">*</span></label>
          <select name="room_tujuan_id" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm bg-white focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 outline-none">
            <option value="">Pilih kamar yang tersedia...</option>
            @forelse($roomsTersedia as $rt)
            <option value="{{ $rt->id }}">Kamar {{ $rt->nomor }} — {{ optional($rt->roomType)->nama }}</option>
            @empty
            <option value="" disabled>Tidak ada kamar tersedia</option>
            @endforelse
          </select>
          <p class="text-[11px] text-slate-400 mt-1">Hanya kamar berstatus "tersedia" yang bisa dipilih.</p>
        </div>
        <div>
          <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">Tanggal Pindah</label>
          <input type="date" name="tanggal_pindah" value="{{ date('Y-m-d') }}" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 outline-none">
        </div>
        @if($jmlBarang > 0)
        <div class="border border-slate-200 rounded-lg overflow-hidden">
          <div class="flex items-center justify-between px-3 py-2 bg-slate-50 border-b border-slate-100">
            <div class="text-xs font-semibold uppercase tracking-wider text-slate-500"><i class="fa fa-boxes-stacked mr-1 text-indigo-500"></i>Pilih Barang yang Ikut</div>
            <button type="button" id="btnToggleBarang" onclick="toggleSemuaBarang()" class="text-xs font-semibold text-indigo-600 hover:text-indigo-500">Kosongkan</button>
          </div>
          <div class="max-h-56 overflow-y-auto divide-y divide-slate-100">
            @foreach($room->assets->where('status','aktif') as $a)
            @php $unit = $a->unit; $perlu = (optional($unit)->kondisi ?? '')=='perlu_diganti'; @endphp
            <label class="flex items-center gap-3 px-3 py-2.5 text-sm cursor-pointer hover:bg-slate-50 transition">
              <input type="checkbox" class="js-barang rounded border-slate-300 text-indigo-500 focus:ring-indigo-500/30" name="asset_ids[]" value="{{ $a->id }}" checked onchange="hitungBarang()">
              <div class="w-8 h-8 rounded-lg {{ $perlu ? 'bg-rose-50 text-rose-600' : 'bg-slate-100 text-slate-500' }} flex items-center justify-center shrink-0"><i class="fa fa-box text-xs"></i></div>
              <div class="min-w-0 flex-1">
                <div class="font-medium truncate">{{ optional(optional($unit)->item)->nama ?? '-' }}</div>
                <div class="text-xs text-slate-400 font-mono">{{ optional($unit)->kode_barcode ?? '-' }}</div>
              </div>
              <span class="text-[11px] px-2 py-0.5 rounded-full border font-medium shrink-0 {{ $perlu ? 'bg-rose-500 text-white border-rose-500' : 'bg-slate-100 text-slate-600 border-slate-200' }}">{{ $perlu ? 'Perlu Diganti' : (optional($unit)->kondisi ?? '-') }}</span>
            </label>
            @endforeach
          </div>
          <div class="px-3 py-2 bg-slate-50 border-t border-slate-100 text-xs text-slate-500"><span id="ringkasBarang" class="font-semibold text-slate-700">{{ $jmlBarang }}</span> dari {{ $jmlBarang }} barang dipilih • yang tidak dicentang tetap di kamar asal.</div>
        </div>
        @else
        <div class="bg-slate-50 border border-slate-100 rounded-lg p-3 text-xs text-slate-500 flex items-center gap-2"><i class="fa fa-circle-info text-slate-400"></i>Tidak ada barang aktif di kamar ini — hanya penghuni yang dipindahkan.</div>
        @endif
        <div class="flex gap-2 pt-1">
          <button class="flex-1 bg-indigo-500 hover:bg-indigo-600 text-white px-5 py-2.5 rounded-lg text-sm font-semibold shadow-sm transition"><i class="fa fa-check mr-1 text-xs"></i>Pindahkan Sekarang</button>
          <button type="button" onclick="closePindah()" class="px-5 py-2.5 rounded-lg text-sm border border-slate-200 text-slate-600 hover:bg-slate-50 transition">Batal</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endif
@endsection
@push('scripts')
<script>
 function openPindah(){var m=document.getElementById('modalPindah');if(m)m.classList.remove('hidden');document.body.style.overflow='hidden'}
 function closePindah(){var m=document.getElementById('modalPindah');if(m)m.classList.add('hidden');document.body.style.overflow=''}
 document.addEventListener('keydown',function(e){if(e.key==='Escape')closePindah()});
 function hitungBarang(){
   var boxes=document.querySelectorAll('#modalPindah .js-barang');
   var n=0;boxes.forEach(function(b){if(b.checked)n++});
   var el=document.getElementById('ringkasBarang');if(el)el.textContent=n;
   var btn=document.getElementById('btnToggleBarang');
   if(btn)btn.textContent=(n===boxes.length)?'Kosongkan':'Pilih semua';
 }
 function toggleSemuaBarang(){
   var boxes=document.querySelectorAll('#modalPindah .js-barang');
   var semua=Array.prototype.every.call(boxes,function(b){return b.checked});
   boxes.forEach(function(b){b.checked=!semua});
   hitungBarang();
 }
</script>
@endpush
