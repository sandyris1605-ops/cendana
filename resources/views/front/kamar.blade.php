@extends('layouts.front')
@section('title','Status Kamar Hari Ini — Cendana Resident')
@section('content')
@php
$siap = $rooms->where('status','tersedia')->count();
@endphp
<div class="bg-pine text-white overflow-hidden relative">
<div class="max-w-6xl mx-auto px-4 py-7 md:py-8 relative">
<div class="text-[13px] text-white/60"><a href="/" class="hover:text-white">Beranda</a> / Kamar</div>
<h1 class="font-display text-[28px] md:text-[34px] leading-tight mt-1.5">Status kamar hari ini.</h1>
<p class="text-white/70 text-[13.5px] mt-1.5 max-w-xl">Data asli dari sistem admin — <b class="text-white">{{ $siap }} siap survei</b> dari {{ $rooms->count() }} kamar. Tanpa harga di web, datang survei jam 08.00–17.00, kami bilang langsung.</p>
</div>
</div>
<div class="max-w-6xl mx-auto px-4 -mt-4 pb-8">
<div class="bg-cream border border-pine/10 rounded-2xl shadow-card p-3 flex flex-wrap gap-2 items-center">
<div class="flex gap-2" id="filterTipe">
<button data-t="semua" class="fbtn text-[13px] font-bold px-4 py-2 rounded-full bg-pine text-white">Semua</button>
@foreach($types as $t)
<button data-t="{{ $t->nama }}" class="fbtn text-[13px] font-bold px-4 py-2 rounded-full bg-mint text-pine">{{ $t->nama }}</button>
@endforeach
</div>
<div class="flex gap-2 ml-auto" id="filterStatus">
<button data-s="semua" class="sbtn text-[13px] font-bold px-4 py-2 rounded-full bg-pine text-white">Semua status</button>
<button data-s="tersedia" class="sbtn text-[13px] font-bold px-4 py-2 rounded-full bg-mint text-pine">Siap huni</button>
<button data-s="terisi" class="sbtn text-[13px] font-bold px-4 py-2 rounded-full bg-mint text-pine">Terisi</button>
</div>
<div id="hitung" class="w-full text-[12.5px] text-stone-500"></div>
</div>
<div id="gridKamar" class="grid sm:grid-cols-2 lg:grid-cols-3 gap-3 mt-4">
@foreach($rooms as $r)
@php $ada = $r->status=='tersedia'; $tipe = optional($r->roomType)->nama ?? '-'; @endphp
<div class="kamar card-hover bg-cream rounded-[16px] border {{ $ada?'border-fern/30':'border-pine/10' }} shadow-card p-4" data-tipe="{{ $tipe }}" data-status="{{ $r->status }}">
<div class="flex items-start justify-between gap-2">
<div>
<div class="font-display text-[24px] text-pine leading-none">Kamar {{ $r->nomor }}</div>
<div class="text-[12.5px] text-stone-500 mt-1.5">{{ $tipe }} • Lantai {{ $r->lantai ?? '-' }}</div>
</div>
@if($ada)
<span class="text-[11.5px] font-bold px-3 py-1.5 rounded-full bg-fern text-white shrink-0">Siap huni</span>
@else
<span class="text-[11.5px] font-bold px-3 py-1.5 rounded-full bg-stone-200 text-stone-500 shrink-0">Terisi</span>
@endif
</div>
@if(!empty($r->catatan))
<div class="mt-3 text-[12.5px] text-stone-500 italic">“{{ $r->catatan }}”</div>
@endif
@if($ada)
<span class="mt-4 block text-center text-[13px] text-stone-600">Siap survei — datang langsung jam 08.00–17.00</span>
@else
<div class="mt-4 text-center text-[12.5px] text-stone-400 bg-stone-100 rounded-xl py-2.5">Lagi dihuni — datang survei untuk antrian terbaru</div>
@endif
</div>
@endforeach
</div>
<div class="mt-4 bg-mint/70 border border-pine/10 rounded-2xl p-4 text-[13px] text-stone-600">Status diambil dari sistem admin. Kalau ragu, datang survei jam 08.00–17.00 — kami terima langsung, bukan bot.</div>
</div>
@push('scripts')
<script>
(function(){
var ft='semua',fs='semua';
function terapkan(){
var list=document.querySelectorAll('.kamar');var n=0;
list.forEach(function(el){
var okT=(ft==='semua'||el.dataset.tipe===ft);
var okS=(fs==='semua'||el.dataset.status===fs);
var show=okT&&okS;el.style.display=show?'':'none';if(show)n++;
});
document.getElementById('hitung').textContent='Menampilkan '+n+' dari '+list.length+' kamar';
}
document.querySelectorAll('.fbtn').forEach(function(b){b.addEventListener('click',function(){
ft=b.dataset.t;document.querySelectorAll('.fbtn').forEach(function(x){x.className='fbtn text-[13px] font-bold px-4 py-2 rounded-full bg-mint text-pine'});b.className='fbtn text-[13px] font-bold px-4 py-2 rounded-full bg-pine text-white';terapkan();});});
document.querySelectorAll('.sbtn').forEach(function(b){b.addEventListener('click',function(){
fs=b.dataset.s;document.querySelectorAll('.sbtn').forEach(function(x){x.className='sbtn text-[13px] font-bold px-4 py-2 rounded-full bg-mint text-pine'});b.className='sbtn text-[13px] font-bold px-4 py-2 rounded-full bg-pine text-white';terapkan();});});
terapkan();
})();
</script>
@endpush
@endsection
