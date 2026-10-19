@extends('layouts.admin')
@section('title','Kamar')
@section('content')
@include('admin.partials.header', ['judul'=>'Data Kamar', 'sub'=>'Kelola kamar, tipe, dan status hunian', 'ctaSlot'=>'<button type="button" onclick="openKamarModal()" class="inline-flex items-center gap-2 bg-amber-500 hover:bg-amber-600 text-slate-900 px-4 py-2 rounded-lg text-sm font-semibold shadow-sm transition"><i class="fa fa-plus text-xs"></i>Tambah Kamar</button>'])

{{-- Kartu statistik ringkas (animasi fade-slide berurutan) --}}
@php
$kartu = [
    ['label'=>'Total Kamar','val'=>$stat['total'],'ikon'=>'fa-bed','tintTxt'=>'text-indigo-600','tintBg'=>'bg-indigo-50'],
    ['label'=>'Tersedia','val'=>$stat['tersedia'],'ikon'=>'fa-door-open','tintTxt'=>'text-emerald-600','tintBg'=>'bg-emerald-50'],
    ['label'=>'Terisi','val'=>$stat['terisi'],'ikon'=>'fa-user','tintTxt'=>'text-rose-600','tintBg'=>'bg-rose-50'],
    ['label'=>'Maintenance','val'=>$stat['maintenance'],'ikon'=>'fa-screwdriver-wrench','tintTxt'=>'text-amber-600','tintBg'=>'bg-amber-50'],
];
@endphp
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 md:gap-4 mb-5">
@foreach($kartu as $i => $k)
<div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 flex items-center gap-3 kamar-stat" style="animation-delay:{{ $i * 60 }}ms">
  <div class="w-11 h-11 rounded-xl {{ $k['tintBg'] }} {{ $k['tintTxt'] }} flex items-center justify-center shrink-0"><i class="fa {{ $k['ikon'] }}"></i></div>
  <div class="min-w-0">
    <div class="text-[11px] uppercase tracking-wider text-slate-400 font-semibold">{{ $k['label'] }}</div>
    <div class="text-2xl font-bold tracking-tight">{{ $k['val'] }} <span class="text-xs font-medium text-slate-400">kamar</span></div>
  </div>
</div>
@endforeach
</div>

@php $extra = '<select name="status" onchange="this.form.submit()" class="border border-slate-200 rounded-lg text-sm py-2 px-3 bg-white outline-none focus:border-amber-500"><option value="">Semua Status</option>'
    .collect(['tersedia','terisi','maintenance'])->map(fn($s)=>'<option value="'.$s.'" '.(request('status')==$s?'selected':'').'>'.$s.'</option>')->implode('')
    .'</select>'; @endphp
@include('admin.partials.search', ['placeholder'=>'Cari nomor kamar...', 'extra'=>$extra])
<div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-x-auto">
<table class="w-full text-sm">
<thead><tr class="bg-gradient-to-r from-slate-50 to-slate-100 text-left text-xs uppercase tracking-wider text-slate-500 border-b border-slate-200">
<th class="px-4 py-3">Nomor</th><th class="px-4 py-3">Tipe</th><th class="px-4 py-3">Lantai</th><th class="px-4 py-3">Status</th><th class="px-4 py-3">Penghuni</th>
</tr></thead>
<tbody class="divide-y divide-slate-100">
@php
$badge = [
  'tersedia'=>'bg-emerald-50 text-emerald-700 border-emerald-200',
  'terisi'=>'bg-rose-50 text-rose-600 border-rose-200',
  'maintenance'=>'bg-amber-50 text-amber-700 border-amber-200',
];
$dot = ['tersedia'=>'bg-emerald-500','terisi'=>'bg-rose-500','maintenance'=>'bg-amber-500'];
$berkedip = ['terisi','maintenance'];
@endphp
@forelse($rooms as $i => $r)
@php $penghuni = optional(optional($r->activeTenancy)->tenant)->nama; @endphp
<tr class="kamar-baris hover:bg-amber-50/40 transition group" style="animation-delay:{{ min($i,15) * 45 }}ms">
<td class="px-4 py-3"><a href="{{ route('admin.rooms.show',$r) }}" title="Lihat detail Kamar {{ $r->nomor }}" class="inline-flex items-center gap-2 font-semibold text-indigo-600 group-hover:text-amber-600 transition"><span class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 group-hover:bg-amber-100 group-hover:text-amber-600 inline-flex items-center justify-center transition"><i class="fa fa-bed text-xs"></i></span>{{ $r->nomor }}</a></td>
<td class="px-4 py-3">{{ optional($r->roomType)->nama }} <span class="text-xs text-slate-400">({{ optional($r->roomType)->ukuran ?? '-' }})</span></td>
<td class="px-4 py-3">Lantai {{ $r->lantai }}</td>
<td class="px-4 py-3"><span class="inline-flex items-center gap-1.5 text-[11px] px-2.5 py-1 rounded-full border font-medium {{ $badge[$r->status] ?? 'bg-slate-100 text-slate-600 border-slate-200' }}"><span class="w-1.5 h-1.5 rounded-full {{ $dot[$r->status] ?? 'bg-slate-400' }} @if(in_array($r->status,$berkedip)) animate-pulse @endif"></span>{{ ucfirst($r->status) }}</span></td>
<td class="px-4 py-3">@if($penghuni)<span class="inline-flex items-center gap-2"><span class="w-7 h-7 rounded-full bg-slate-900 text-amber-400 inline-flex items-center justify-center text-[11px] font-bold">{{ strtoupper(substr($penghuni,0,1)) }}</span>{{ $penghuni }}</span>@else<span class="text-slate-400">&mdash;</span>@endif</td>
</tr>
@empty
<tr><td colspan="5" class="px-4 py-10 text-center text-slate-400"><i class="fa fa-bed text-2xl mb-2 block"></i>Tidak ada kamar yang cocok.</td></tr>
@endforelse
</tbody>
</table>
</div>
@include('admin.partials.pager', ['paginator'=>$rooms])

{{-- ===== Modal Tambah Kamar ===== --}}
<div id="modalKamar" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
<div class="absolute inset-0 bg-slate-900/60" onclick="closeKamarModal()"></div>
<div class="relative bg-white rounded-2xl shadow-xl w-full max-w-lg max-h-[90vh] overflow-y-auto p-6">
<div class="flex items-center justify-between mb-4">
<div class="font-bold text-lg">Tambah Kamar</div>
<button type="button" onclick="closeKamarModal()" class="w-8 h-8 inline-flex items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition"><i class="fa fa-xmark"></i></button>
</div>
<form method="POST" action="{{ route('admin.rooms.store') }}">
@csrf
@include('admin.rooms._form-fields', ['room' => new \App\Models\Room()])
<div class="flex gap-2 mt-6 pt-4 border-t border-slate-100">
<button class="bg-amber-500 hover:bg-amber-600 text-slate-900 px-5 py-2 rounded-lg text-sm font-semibold shadow-sm transition"><i class="fa fa-check mr-1 text-xs"></i>Simpan</button>
<button type="button" onclick="closeKamarModal()" class="px-5 py-2 rounded-lg text-sm border border-slate-200 text-slate-600 hover:bg-slate-50 transition">Batal</button>
</div>
</form>
</div>
</div>
<script>
function openKamarModal(){document.getElementById('modalKamar').classList.remove('hidden');document.body.classList.add('overflow-hidden');}
function closeKamarModal(){document.getElementById('modalKamar').classList.add('hidden');document.body.classList.remove('overflow-hidden');}
document.addEventListener('keydown',function(e){if(e.key==='Escape')closeKamarModal();});
</script>
@if($errors->any())
<script>document.addEventListener('DOMContentLoaded',openKamarModal);</script>
@endif

<style>
@keyframes kamarNaik { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
.kamar-stat { opacity: 0; animation: kamarNaik .4s ease both; }
.kamar-baris { opacity: 0; animation: kamarNaik .35s ease both; }
@media (prefers-reduced-motion: reduce) { .kamar-stat, .kamar-baris { animation: none; opacity: 1; } }
</style>
@endsection
