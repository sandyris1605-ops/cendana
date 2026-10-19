<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>@yield('title','Cendana Resident — Kos Terawat di Lowokwaru, Malang')</title>
<meta name="description" content="Cendana Resident — kos terawat di Lowokwaru Malang. Kamar siap huni, cleaning rutin, CCTV lorong, admin fast respon.">
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<script>
tailwind.config = { theme: { extend: {
fontFamily: { display: ['Fraunces','Georgia','serif'], sans: ['"Plus Jakarta Sans"','system-ui','sans-serif'] },
colors: { pine: { DEFAULT:'#1A3C34', 800:'#234E44', 950:'#0E2621' }, fern: { DEFAULT:'#2D6A4F' }, mint: '#EDF4EE', paper: '#F6F3EC', cream: '#FFFDF7', clay: '#C97A2B', leaf: '#B7E4C7' },
boxShadow: { card: '0 10px 30px -16px rgba(26,60,52,.28)' }
} } }
</script>
<style>
html{scroll-behavior:smooth}
body{font-family:'Plus Jakarta Sans',system-ui,sans-serif;background:#F6F3EC;color:#2b2b28}
.font-display{font-family:'Fraunces',Georgia,serif;letter-spacing:-.01em}
.squiggle{position:relative;display:inline-block;z-index:0}
.squiggle>svg{position:absolute;left:-2%;bottom:-.32em;width:104%;height:.5em;z-index:-1}
.card-hover{transition:transform .25s ease,box-shadow .25s ease}
.card-hover:hover{transform:translateY(-4px)}
.btn-pine{background:#1A3C34;color:#fff;transition:.2s}
.btn-pine:hover{background:#234E44}
.btn-wa{background:#1FA855;color:#fff;transition:.2s}
.btn-wa:hover{background:#178241}
.nav-link{color:#3d4a45}.nav-link:hover{color:#1A3C34}
.nav-link.active{color:#1A3C34;font-weight:700}
.dot-live{position:relative}
.dot-live::after{content:"";position:absolute;inset:-4px;border-radius:99px;border:2px solid #2D6A4F;opacity:.35;animation:ping 1.8s ease-out infinite}
@keyframes ping{0%{transform:scale(.7);opacity:.5}100%{transform:scale(1.15);opacity:0}}
details summary::-webkit-details-marker{display:none}
.paper-grain{background-image:radial-gradient(rgba(26,60,52,.05) 1px,transparent 1.2px);background-size:16px 16px}
.blob{position:absolute;pointer-events:none;max-width:100%}
</style>
@stack('head')
</head>
<body class="antialiased">
@php
$alamatTxt = isset($alamat) ? $alamat : \App\Models\Setting::get('alamat','Jl. Cendana No. 88, Lowokwaru, Malang');
@endphp
<div class="bg-pine-950 text-[13px] text-[#D8E8DC]">
<div class="max-w-6xl mx-auto px-4 py-1.5 flex flex-wrap gap-x-5 gap-y-1 items-center justify-between">
<div class="flex items-center gap-2">
<span class="w-2 h-2 rounded-full bg-[#7BD99A] dot-live"></span>
<span>Pengelola jaga <b class="text-white">08.00–21.00</b> — survei langsung jam 08.00–17.00</span>
</div>
<div class="hidden sm:flex items-center gap-4">
<span><i class="fa-solid fa-location-dot mr-1"></i>{{ $alamatTxt }}</span>
</div>
</div>
</div>
<header class="sticky top-0 z-50 bg-cream/95 backdrop-blur border-b border-[#1A3C34]/10">
<div class="max-w-6xl mx-auto px-4 py-3 flex items-center justify-between gap-3">
<a href="/" class="flex items-center gap-2.5">
<span class="w-10 h-10 rounded-2xl bg-pine text-leaf flex items-center justify-center text-lg shadow-card shrink-0"><i class="fa-solid fa-leaf"></i></span>
<span class="leading-tight">
<span class="font-display font-semibold text-[19px] text-pine block">Cendana Resident</span>
</span>
</a>
<nav class="hidden md:flex items-center gap-7 text-[14.5px] font-medium">
<a href="/" class="nav-link {{ request()->is('/') ? 'active':'' }}">Beranda</a>
<a href="/kamar" class="nav-link {{ request()->is('kamar*') ? 'active':'' }}">Kamar</a>
<a href="/#fasilitas" class="nav-link">Fasilitas</a>
<a href="/#cara-survei" class="nav-link">Cara survei</a>
<a href="/#faq" class="nav-link">Tanya jawab</a>
</nav>
<div class="flex items-center gap-2">
<a href="/kamar" class="hidden sm:inline-flex btn-pine items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold"><i class="fa-solid fa-bed text-sm"></i> Lihat kamar</a>
<button id="btnNav" class="md:hidden w-10 h-10 rounded-xl border border-pine/15 text-pine flex items-center justify-center"><i class="fa fa-bars"></i></button>
</div>
</div>
<div id="navMobile" class="hidden md:hidden border-t border-pine/10 bg-cream px-4 py-3">
<div class="grid gap-1 text-[15px]">
<a href="/" class="py-2 border-b border-stone-100">Beranda</a>
<a href="/kamar" class="py-2 border-b border-stone-100">Kamar & status</a>
<a href="/#fasilitas" class="py-2 border-b border-stone-100">Fasilitas</a>
<a href="/#cara-survei" class="py-2 border-b border-stone-100">Cara survei</a>
<a href="/#faq" class="py-2">Tanya jawab</a>
<a href="/kamar" class="mt-2 btn-pine rounded-xl py-3 text-center font-semibold">Lihat kamar tersedia</a>
</div>
</div>
</header>
@yield('content')
<footer id="kontak" class="mt-10 bg-pine-950 text-[#C9D9CF]">
<div class="max-w-6xl mx-auto px-4 pt-8 pb-5">
<div class="grid md:grid-cols-3 gap-8">
<div>
<div class="font-display text-xl text-white">Cendana Resident</div>
<p class="text-sm leading-relaxed mt-3 max-w-sm">Kos bulanan di Lowokwaru, Malang. Dikelola harian — kalau ada bocor, lampu mati, atau kunci nyangkut, tinggal lapor ke grup penghuni.</p>
<div class="mt-4 text-sm space-y-1.5">
<div><i class="fa-solid fa-location-dot w-5"></i>{{ $alamatTxt }}</div>
<div><i class="fa-regular fa-clock w-5"></i>Jaga 08.00-21.00 WIB, Senin-Minggu</div>
<div><i class="fa-solid fa-hands-holding-child w-5"></i>Survei langsung jam 08.00–17.00 — datang saja ke lokasi</div>
</div>
</div>
<div>
<div class="text-white font-semibold mb-3">Lihat-lihat</div>
<div class="grid gap-2 text-sm">
<a href="/" class="hover:text-white">Beranda</a>
<a href="/kamar" class="hover:text-white">Status kamar hari ini</a>
<a href="/#fasilitas" class="hover:text-white">Fasilitas & aturan</a>
<a href="/#faq" class="hover:text-white">Tanya jawab</a>
</div>
</div>
<div>
<div class="text-white font-semibold mb-3">Patokan lokasi</div>
<ul class="text-sm space-y-2 list-none">
<li>• Sekitar 10 mnt ke UB & UIN Maliki</li>
<li>• Gang masuk motor + mobil</li>
<li>• Parkir motor di dalam, mobil di depan</li>
<li>• Warung & laundry jalan kaki</li>
</ul>
</div>
</div>
<div class="border-t border-white/10 mt-6 pt-4 flex flex-col sm:flex-row gap-2 items-center justify-between text-xs">
<div>© {{ date('Y') }} Cendana Resident</div>
<div class="flex items-center gap-4">
@if(!request()->is('/'))
<a href="/login" class="hover:text-white">Login pengelola</a>
@endif
</div>
</div>
</div>
</footer>
<script>
(function(){var b=document.getElementById('btnNav'),m=document.getElementById('navMobile');if(b&&m){b.addEventListener('click',function(){m.classList.toggle('hidden')})}})();
</script>
</body>
</html>
