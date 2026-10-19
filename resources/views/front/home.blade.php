@extends('layouts.front')
@section('title','Cendana Resident — Kos Terawat di Lowokwaru, Malang')
@section('content')
@php
$tersediaCount = $rooms->where('status','tersedia')->count();
$totalCount = $rooms->count();
@endphp
<header class="relative overflow-hidden bg-paper paper-grain">
<div class="blob w-[320px] h-[320px] rounded-full bg-[#DDEEDF] blur-3xl opacity-60 -top-20 -right-20"></div>
<div class="blob w-[220px] h-[220px] rounded-full bg-[#FFF3D9] blur-3xl opacity-60 top-32 -left-20"></div>
<div class="relative max-w-6xl mx-auto px-4 pt-5 md:pt-7 pb-5 grid lg:grid-cols-[1.15fr_.85fr] gap-5 items-start">
<div class="pt-1">
<div class="inline-flex items-center gap-2 bg-white border border-pine/10 rounded-full pl-2 pr-3 py-1.5 text-[13px] shadow-card">
<span class="w-6 h-6 rounded-full bg-fern text-white flex items-center justify-center text-xs"><i class="fa-solid fa-key"></i></span>
<span class="text-stone-600"><b class="text-pine">{{ $tersediaCount }} kamar siap</b> minggu ini dari {{ $totalCount }} kamar</span>
</div>
<h1 class="font-display text-pine text-[30px] leading-[1.1] md:text-[40px] mt-3">Kamar yang keurus,<br>tinggal <span class="squiggle">bawa koper.<svg viewBox="0 0 120 12" preserveAspectRatio="none"><path d="M2 8 Q 20 2 40 7 T 78 7 T 118 5" fill="none" stroke="#B7E4C7" stroke-width="5" stroke-linecap="round"/></svg></span></h1>
<p class="mt-3 text-[14.5px] leading-relaxed text-stone-600 max-w-xl">Halo, saya <b>Bu Anik</b> — pengelola harian Cendana di Lowokwaru. Semua kamar kami data satu-satu: kasur, lemari, token listrik, sampai ember. Kalau ada yang rusak, tinggal lapor, kami benerin.</p>
<div class="mt-4 flex flex-wrap gap-2.5">
<a href="/kamar" class="btn-pine px-5 py-3 rounded-2xl text-sm font-bold inline-flex items-center gap-2"><i class="fa-solid fa-bed"></i>Lihat kamar tersedia</a>
</div>
<div class="mt-4 flex flex-wrap gap-x-4 gap-y-1.5 text-[13px] text-stone-600">
<span><i class="fa-solid fa-circle-check text-fern mr-1.5"></i>Cleaning lorong tiap Jumat</span>
<span><i class="fa-solid fa-circle-check text-fern mr-1.5"></i>CCTV lorong & gerbang</span>
<span><i class="fa-solid fa-circle-check text-fern mr-1.5"></i>Token listrik masing-masing</span>
</div>
<div class="mt-4 bg-cream border border-pine/10 rounded-2xl p-3.5 flex gap-3 items-start shadow-card max-w-xl">
<img src="https://ui-avatars.com/api/?name=Bu+Anik&background=1A3C34&color=B7E4C7&bold=true" alt="Bu Anik" class="w-11 h-11 rounded-full object-cover shrink-0">
<div class="text-[13.5px] leading-relaxed text-stone-600"><span class="text-pine font-bold">Catatan Bu Anik hari ini:</span> “Kamar yang foto-fotonya terang itu habis dirapikan Jumat kemarin. Kalau mau survei, silakan datang langsung jam 08.00–17.00 atau lihat status kamar dulu di web. Jangan langsung datang tanpa konfirmasi, kadang saya lagi belanja.”</div>
</div>
</div>
<div class="bg-pine text-white rounded-[18px] p-4 md:p-5 shadow-card relative overflow-hidden">
<div class="blob w-44 h-44 rounded-full bg-white/10 blur-2xl -top-8 -right-8"></div>
<div class="relative flex items-center justify-between gap-2">
<div class="font-display text-lg">Ketersediaan hari ini</div>
<div class="text-[11px] bg-white/15 rounded-full px-2.5 py-1">dari sistem admin</div>
</div>
<div class="relative mt-3 space-y-2">
@foreach($types as $t)
<div class="bg-white/[.07] border border-white/10 rounded-2xl px-4 py-3 flex items-center justify-between gap-3">
<div>
<div class="font-bold text-[15px]">{{ $t->nama }}</div>
<div class="text-xs text-white/60">{{ $t->ukuran ?? 'Ukuran standar' }} • {{ $t->rooms_count }} kamar</div>
</div>
@if($t->tersedia > 0)
<span class="shrink-0 bg-[#7BD99A] text-pine-950 text-xs font-bold px-3 py-1.5 rounded-full">{{ $t->tersedia }} siap</span>
@else
<span class="shrink-0 bg-white/15 text-white/80 text-xs font-semibold px-3 py-1.5 rounded-full">Penuh</span>
@endif
</div>
@endforeach
</div>
<a href="/kamar" class="relative mt-3 block text-center bg-white text-pine font-bold text-sm rounded-2xl py-2.5 hover:bg-leaf transition">Lihat nomor kamarnya →</a>
<p class="relative mt-2 text-[12px] text-white/60 text-center">Status asli dari data admin. Kalau ragu, datang survei jam 08.00–17.00.</p>
</div>
</div>
</header>
<section class="bg-cream border-y border-pine/10">
<div class="max-w-6xl mx-auto px-4 py-3 grid grid-cols-2 md:grid-cols-4 gap-3 text-[13px]">
<div class="flex items-center gap-2.5"><span class="w-9 h-9 rounded-xl bg-mint text-fern flex items-center justify-center shrink-0"><i class="fa-solid fa-broom"></i></span><span><b class="text-pine">{{ $totalCount }} kamar</b> didata & dicek rutin</span></div>
<div class="flex items-center gap-2.5"><span class="w-9 h-9 rounded-xl bg-mint text-fern flex items-center justify-center shrink-0"><i class="fa-solid fa-video"></i></span><span>CCTV lorong + <b class="text-pine">gerbang dikunci malam</b></span></div>
<div class="flex items-center gap-2.5"><span class="w-9 h-9 rounded-xl bg-mint text-fern flex items-center justify-center shrink-0"><i class="fa-solid fa-bolt"></i></span><span><b class="text-pine">Token sendiri</b>, bayar sewa bisa transfer</span></div>
<div class="flex items-center gap-2.5"><span class="w-9 h-9 rounded-xl bg-mint text-fern flex items-center justify-center shrink-0"><i class="fa-solid fa-handshake"></i></span><span>Komplain <b class="text-pine">lewat grup penghuni</b></span></div>
</div>
</section>
<section id="kamar" class="py-8">
<div class="max-w-6xl mx-auto px-4">
<div class="flex flex-wrap items-end justify-between gap-3">
<div>
<div class="text-fern text-[13px] font-bold tracking-wide">PILIH SESUAI KEBUTUHAN</div>
<h2 class="font-display text-pine text-[26px] md:text-[30px] leading-tight mt-1">Tiga tipe, beda ukuran & isi.</h2>
<p class="text-stone-500 text-[13.5px] mt-1.5 max-w-lg">Yang beda cuma luas & kelengkapan. Kebersihan & keamanan sama semua. Harga kami terjangkau — datang survei, kami bilang langsung.</p>
</div>
<a href="/kamar" class="text-sm font-bold text-fern hover:text-pine">Lihat semua nomor kamar →</a>
</div>
<div class="grid md:grid-cols-3 gap-4 mt-5">
@php $ikonTipe = ['Standar'=>'fa-bed','Deluxe'=>'fa-bed-pulse','Exclusive'=>'fa-house-laptop']; $catatanTipe = ['Standar'=>'Cocok buat mahasiswa — ringkas tapi rapi.','Deluxe'=>'Lebih lega + air panas. Paling cepat penuh.','Exclusive'=>'Paling luas, jendela besar, sirkulasi enak.']; @endphp
@foreach($types as $t)
@php
$fasilitasArr = array_filter(array_map('trim', explode(',', (string)($t->fasilitas ?? ''))));
if(empty($fasilitasArr) && !empty($t->deskripsi)) { $fasilitasArr = array_filter(array_map('trim', explode(',', (string)$t->deskripsi))); }
if(empty($fasilitasArr)) { $fasilitasArr = ['Kasur + sprei','Lemari pakaian','Meja belajar','Kamar mandi dalam']; }
$pesanTipe = 'Halo Cendana Resident, mau tanya tipe '.$t->nama.' masih ada yang kosong?';
@endphp
<div class="card-hover bg-cream rounded-[20px] border border-pine/10 overflow-hidden shadow-card flex flex-col">
<div class="bg-mint px-6 pt-6 pb-5 relative overflow-hidden">
<div class="absolute -right-6 -top-6 w-28 h-28 rounded-full bg-white/60"></div>
<div class="relative flex items-center justify-between">
<span class="w-11 h-11 rounded-2xl bg-pine text-leaf flex items-center justify-center text-lg"><i class="fa-solid {{ $ikonTipe[$t->nama] ?? 'fa-door-open' }}"></i></span>
@if($t->tersedia>0)
<span class="text-xs font-bold bg-fern text-white px-3 py-1.5 rounded-full">{{ $t->tersedia }} tersedia</span>
@else
<span class="text-xs font-bold bg-stone-200 text-stone-500 px-3 py-1.5 rounded-full">Lagi penuh</span>
@endif
</div>
<div class="relative font-display text-[26px] text-pine mt-3">{{ $t->nama }}</div>
<div class="relative text-[13px] text-stone-500">Ukuran {{ $t->ukuran ?? '-' }} • {{ $t->rooms_count }} kamar</div>
<div class="relative text-[13px] text-stone-600 italic mt-1">“{{ $catatanTipe[$t->nama] ?? 'Dijaga kebersihannya tiap minggu.' }}”</div>
</div>
<div class="p-6 flex-1 flex flex-col">
<ul class="space-y-2 text-[13.5px] text-stone-600">
@foreach(array_slice($fasilitasArr,0,5) as $f)
<li class="flex gap-2"><i class="fa-solid fa-check text-fern mt-1 text-xs"></i><span>{{ $f }}</span></li>
@endforeach
</ul>
<div class="mt-3 text-[13px] {{ $t->tersedia>0?'text-fern font-semibold':'text-stone-400' }}">
<i class="fa-solid {{ $t->tersedia>0?'fa-circle-check':'fa-circle-minus' }} mr-1"></i>{{ $t->tersedia }} dari {{ $t->rooms_count }} kamar tersedia
</div>
<div class="mt-5 text-center">
<a href="/kamar" class="w-full inline-block text-center border border-pine/20 text-pine font-bold text-[13px] rounded-xl py-2.5 hover:bg-pine hover:text-white transition">Lihat nomor</a>
</div>
</div>
</div>
@endforeach
</div>
</div>
</section>
<section id="fasilitas" class="py-8 bg-mint/60 border-y border-pine/10">
<div class="max-w-6xl mx-auto px-4">
<div class="max-w-xl">
<div class="text-fern text-[13px] font-bold tracking-wide">YANG DIDAPAT & ATURANNYA</div>
<h2 class="font-display text-pine text-[26px] md:text-[30px] leading-tight mt-1">Fasilitas yang beneran dipakai.</h2>
<p class="text-stone-500 text-[13.5px] mt-1.5">Bukan daftar pajangan. Ini yang penghuni pakai tiap hari — plus aturan biar sama-sama enak.</p>
</div>
<div class="grid grid-cols-2 md:grid-cols-4 gap-3 mt-5">
@foreach(['WiFi kencang, tiap lantai ada router'=>'fa-wifi','CCTV lorong + gerbang dikunci'=>'fa-video','Cleaning tiap Jumat'=>'fa-broom','Dapur bersama + kulkas'=>'fa-kitchen-set','Parkir motor di dalam'=>'fa-square-parking','Air panas (Deluxe ke atas)'=>'fa-shower'] as $f=>$i)
<div class="card-hover bg-cream rounded-2xl border border-pine/10 p-4 shadow-card">
<i class="fa-solid {{ $i }} text-lg text-fern"></i>
<div class="mt-2 text-[13.5px] font-bold text-pine leading-snug">{{ $f }}</div>
</div>
@endforeach
</div>
<div class="mt-4 bg-cream border border-dashed border-clay/50 rounded-2xl p-4 text-[13px] text-stone-600 leading-relaxed">
<b class="text-pine">Aturan singkat biar betah bareng:</b> tamu maks. 21.00 • knalpot brong dilarang • sampah dipilah • bayar sewa maks. tgl 5.
</div>
</div>
</section>
<section id="cara-survei" class="py-8">
<div class="max-w-6xl mx-auto px-4 grid lg:grid-cols-2 gap-5 items-start">
<div>
<div class="text-fern text-[13px] font-bold tracking-wide">NGGAK RIBET</div>
<h2 class="font-display text-pine text-[26px] leading-tight mt-1">Cara survei: datang langsung jam 08.00–17.00.</h2>
<p class="text-stone-500 text-[13.5px] mt-1.5">Jangan langsung datang — kadang Bu Anik lagi di pasar.</p>
<div class="mt-4 space-y-2.5">
<div class="flex gap-3 bg-cream border border-pine/10 rounded-2xl p-4 shadow-card">
<span class="w-8 h-8 rounded-full bg-pine text-white font-bold flex items-center justify-center shrink-0 text-sm">1</span>
<div class="text-[13.5px]"><b class="text-pine\">Datang survei, sebut tanggalnya.</b><div class="text-stone-500 mt-0.5">Contoh: “Halo, saya Dimas, mau lihat tipe Deluxe Sabtu siang.”</div></div>
</div>
<div class="flex gap-3 bg-cream border border-pine/10 rounded-2xl p-4 shadow-card">
<span class="w-8 h-8 rounded-full bg-pine text-white font-bold flex items-center justify-center shrink-0 text-sm">2</span>
<div class="text-[13.5px]"><b class="text-pine">Datang & lihat 2-3 kamar langsung.</b><div class="text-stone-500 mt-0.5">Boleh cek air, colokan, sinyal. Survei jam 08.00-17.00 paling terang.</div></div>
</div>
<div class="flex gap-3 bg-cream border border-pine/10 rounded-2xl p-4 shadow-card">
<span class="w-8 h-8 rounded-full bg-pine text-white font-bold flex items-center justify-center shrink-0 text-sm">3</span>
<div class="text-[13.5px]"><b class="text-pine">Cocok? Kamar ditahan 3 hari.</b><div class="text-stone-500 mt-0.5">Tanda jadi dulu, sisanya pas tanda tangan + serah terima kunci.</div></div>
</div>
</div>
</div>
<div class="bg-pine text-white rounded-[18px] p-4 md:p-5 relative overflow-hidden">
<div class="font-display text-lg">Kata yang sudah tinggal</div>
<div class="mt-3 space-y-2.5">
<div class="bg-white/10 rounded-2xl p-3.5 text-[13px] leading-relaxed">“Awalnya cuma mau survei bentar, eh malah betah 2 tahun. Airnya kenceng, adminnya enak diajak ngomong.”<div class="mt-1.5 text-white/60 text-xs">— Rizka, penghuni lt. 2 (UB)</div></div>
<div class="bg-white/10 rounded-2xl p-3.5 text-[13px] leading-relaxed">“Token sendiri jadi adil. Pernah lampu mati jam 9 malam, langsung dibenerin malam itu juga.”<div class="mt-1.5 text-white/60 text-xs">— Bagas, karyawan swasta</div></div>
</div>
</div>
</div>
</section>
<section id="faq" class="pb-4">
<div class="max-w-6xl mx-auto px-4">
<div class="bg-cream border border-pine/10 rounded-[18px] p-4 md:p-5 shadow-card">
<div class="font-display text-[20px] text-pine">Sering ditanyakan</div>
<div class="mt-3 grid md:grid-cols-2 gap-2 text-[13.5px]">
<details class="border border-pine/10 rounded-xl bg-white px-4 py-3"><summary class="font-bold text-pine cursor-pointer">Boleh bawa pasangan / menginap?</summary><div class="text-stone-600 mt-2">Tamu boleh sampai jam 21.00. Menginap lawan jenis tidak boleh.</div></details>
<details class="border border-pine/10 rounded-xl bg-white px-4 py-3"><summary class="font-bold text-pine cursor-pointer">Listrik bagaimana?</summary><div class="text-stone-600 mt-2">Token per kamar, isi sendiri. Rata-rata 50-100rb/bulan tanpa AC.</div></details>
<details class="border border-pine/10 rounded-xl bg-white px-4 py-3"><summary class="font-bold text-pine cursor-pointer">Boleh masak & bawa hewan?</summary><div class="text-stone-600 mt-2">Masak di dapur bersama boleh. Hewan berbulu tidak boleh.</div></details>
<details class="border border-pine/10 rounded-xl bg-white px-4 py-3"><summary class="font-bold text-pine cursor-pointer">Bayar & DP bagaimana?</summary><div class="text-stone-600 mt-2">Transfer / tunai, kwitansi diterima saat tanda tangan. DP menahan kamar 3 hari.</div></details>
</div>
</div>
</div>
</section>
<section class="pb-3">
<div class="max-w-6xl mx-auto px-4">
<div class="bg-pine rounded-[20px] p-5 md:p-7 relative overflow-hidden text-white">
<div class="relative max-w-xl">
<h2 class="font-display text-[24px] md:text-[28px] leading-tight">Masih ragu? Survei 10 menit lebih jelas dari 100 foto.</h2>
<p class="text-white/70 text-[13.5px] mt-2">Datang survei jam 08.00–17.00, sebut mau lihat tipe apa. Kalau penuh, kami kasih tahu kapan kosong.</p>
<div class="mt-4 flex flex-wrap gap-2.5">
<a href="/kamar" class="border border-white/30 text-white font-bold text-sm px-5 py-3 rounded-2xl hover:bg-white/10 transition">Cek status kamar</a>
</div>
</div>
</div>
</div>
</section>
@endsection
