<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Masuk — Cendana Resident</title>
<meta name="robots" content="noindex, nofollow">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<script>
tailwind.config = { theme: { extend: {
fontFamily: { display: ['Fraunces','Georgia','serif'], sans: ['"Plus Jakarta Sans"','system-ui','sans-serif'] },
} } }
</script>
<style>
:root{--pine:#1A3C34;--pine-950:#0E2621;--fern:#2D6A4F;--leaf:#B7E4C7;--paper:#F6F3EC;--cream:#FFFDF7}
*{-webkit-tap-highlight-color:transparent}
html,body{height:100%}
body{font-family:'Plus Jakarta Sans',system-ui,sans-serif;background:var(--cream);color:#2b2b28;-webkit-font-smoothing:antialiased}
.font-display{font-family:'Fraunces',Georgia,serif;letter-spacing:-.015em}

@keyframes float{0%,100%{transform:translate3d(0,0,0)}50%{transform:translate3d(0,-26px,0)}}
@keyframes floatSlow{0%,100%{transform:translate3d(0,0,0) rotate(0)}50%{transform:translate3d(-16px,22px,0) rotate(7deg)}}
@keyframes pulseSoft{0%,100%{opacity:.5;transform:scale(1)}50%{opacity:.9;transform:scale(1.14)}}
@keyframes fall{0%{transform:translate3d(0,-14vh,0) rotate(0);opacity:0}12%{opacity:.9}88%{opacity:.75}100%{transform:translate3d(0,114vh,0) rotate(520deg);opacity:0}}
@keyframes sway{0%,100%{transform:translateX(-17px)}50%{transform:translateX(17px)}}
@keyframes fadeUp{from{opacity:0;transform:translate3d(0,24px,0)}to{opacity:1;transform:none}}
@keyframes popIn{0%{opacity:0;transform:scale(.6)}62%{transform:scale(1.07)}100%{opacity:1;transform:scale(1)}}
@keyframes shake{0%,100%{transform:translateX(0)}15%{transform:translateX(-12px)}30%{transform:translateX(10px)}45%{transform:translateX(-7px)}60%{transform:translateX(5px)}80%{transform:translateX(-3px)}}
@keyframes shine{0%{left:-130%}58%,100%{left:135%}}
@keyframes arrowNudge{0%,100%{transform:translateX(0)}50%{transform:translateX(6px)}}
@keyframes blinkEyes{0%,91%,100%{transform:scaleY(1)}95%{transform:scaleY(.07)}}
@keyframes squint{0%,100%{transform:scaleY(1)}50%{transform:scaleY(.4)}}
@keyframes jump{0%,100%{transform:translateY(0) rotate(0)}25%{transform:translateY(-24px) rotate(-6deg)}55%{transform:translateY(0) rotate(4deg)}78%{transform:translateY(-9px) rotate(-3deg)}}
@keyframes wag{0%,100%{transform:rotate(-10deg)}50%{transform:rotate(15deg)}}
@keyframes pop{0%{transform:scale(1)}45%{transform:scale(1.4)}100%{transform:scale(1)}}
@keyframes iconPop{0%{transform:scale(1)}50%{transform:scale(1.3)}100%{transform:scale(1)}}
@keyframes bubbleIn{0%{opacity:0;transform:translateY(7px) scale(.93)}100%{opacity:1;transform:none}}
@keyframes sweat{0%{opacity:0;transform:translateY(-5px) scale(.55)}35%{opacity:1;transform:translateY(2px) scale(1)}100%{opacity:0;transform:translateY(15px) scale(.9)}}
@keyframes keyHop{0%,80%,100%{transform:translateY(0);opacity:.45}40%{transform:translateY(-8px);opacity:1}}
@keyframes typeIn{from{max-width:0;opacity:.25}to{max-width:20ch;opacity:1}}
@keyframes bob{0%,100%{transform:translateY(0)}50%{transform:translateY(-7px)}}
@keyframes spin{to{transform:rotate(360deg)}}

.anim-up{opacity:0;animation:fadeUp .8s cubic-bezier(.16,.84,.44,1) forwards}
.anim-pop{opacity:0;animation:popIn .65s cubic-bezier(.34,1.56,.64,1) forwards}
.d1{animation-delay:.05s}.d2{animation-delay:.15s}.d3{animation-delay:.26s}.d4{animation-delay:.37s}
.d5{animation-delay:.48s}.d6{animation-delay:.6s}.d7{animation-delay:.72s}
.type-in{display:inline-block;overflow:hidden;white-space:nowrap;vertical-align:bottom;max-width:20ch;animation:typeIn 1s steps(18,end) .3s both}

.font-display{font-family:'Fraunces',Georgia,serif;letter-spacing:-.01em}
@media (prefers-reduced-motion: reduce){*,*::before,*::after{animation-duration:.001ms !important;animation-iteration-count:1 !important;transition-duration:.001ms !important}.anim-up,.anim-pop{opacity:1 !important}.daun{display:none}}
/* ---------- komponen ---------- */
.split{display:grid;grid-template-columns:1fr;min-height:100dvh}
@media (min-width:1024px){.split{grid-template-columns:minmax(0,1.05fr) minmax(0,1fr)}}
.panel-kiri{position:relative;overflow:hidden;color:#fff;background:linear-gradient(165deg,#234E44 0%,#1A3C34 48%,#0E2621 100%)}
.blob{position:absolute;border-radius:9999px;filter:blur(65px);pointer-events:none}
.blob-a{top:-110px;left:-90px;width:360px;height:360px;background:radial-gradient(circle at 35% 35%,rgba(183,228,199,.42),transparent 70%)}
.blob-b{bottom:-90px;right:-70px;width:300px;height:300px;background:radial-gradient(circle at 60% 40%,rgba(45,106,79,.65),transparent 72%)}
.daun{position:absolute;top:-10vh;color:rgba(183,228,199,.6);pointer-events:none;animation-name:fall;animation-timing-function:linear;animation-iteration-count:infinite}
.daun > i{display:block;animation:sway 5s ease-in-out infinite}

.kartu{background:#fff;border:1px solid #e9e9e0;border-radius:26px;
  box-shadow:0 40px 80px -46px rgba(14,38,33,.5), 0 6px 20px -12px rgba(14,38,33,.18)}
.wobble{animation:shake .6s cubic-bezier(.36,.07,.19,.97) both}
.garis-bawah{background-image:linear-gradient(var(--fern),var(--fern));background-size:0% 2px;background-repeat:no-repeat;background-position:0 100%;transition:background-size .35s ease}
.garis-bawah:hover{background-size:100% 2px}

.grp{border:1px solid #e4e4da;background:#fff;border-radius:16px;transition:border-color .2s ease,box-shadow .2s ease,transform .2s ease}
.grp:hover{border-color:rgba(45,106,79,.5);transform:translateY(-1px)}
.grp:focus-within{outline:none;border-color:var(--fern);transform:translateY(-1px);box-shadow:0 0 0 4px rgba(45,106,79,.15)}
.grp input{background:transparent}
.grp input:focus{outline:none}
.lbl{transition:color .2s ease,transform .2s ease}
.grp:focus-within .lbl{color:var(--fern);transform:translateX(3px)}
.grp:focus-within .ikon{color:var(--fern);animation:iconPop .35s ease}
input:-webkit-autofill{-webkit-box-shadow:0 0 0 40px #fff inset;-webkit-text-fill-color:#2b2b28}

.btn-masuk{position:relative;overflow:hidden;background:linear-gradient(135deg,#2D6A4F 0%,#1f4e39 100%);
  box-shadow:0 18px 32px -16px rgba(31,78,57,.8);transition:transform .18s ease,box-shadow .18s ease,filter .18s ease}
.btn-masuk:hover{transform:translateY(-2px);filter:brightness(1.08);box-shadow:0 24px 40px -18px rgba(31,78,57,.85)}
.btn-masuk:active{transform:translateY(1px) scale(.985)}
.btn-masuk:disabled{cursor:progress;transform:none;filter:none}
.btn-masuk::after{content:'';position:absolute;top:0;left:-130%;width:55%;height:100%;
  background:linear-gradient(90deg,transparent,rgba(255,255,255,.42),transparent);animation:shine 3.4s ease-in-out infinite}
.btn-masuk:hover .panah{animation:arrowNudge .7s ease-in-out infinite}
.titik{animation:keyHop 1s ease-in-out infinite}
.titik:nth-child(2){animation-delay:.15s}
.titik:nth-child(3){animation-delay:.3s}

.kotak{transition:background .18s ease,border-color .18s ease,transform .18s ease}
.cek{stroke-dasharray:22;stroke-dashoffset:22;transition:stroke-dashoffset .25s ease .05s}
#remember:checked + .kotak{background:var(--fern);border-color:var(--fern)}
#remember:checked + .kotak .cek{stroke-dashoffset:0}
#remember:focus-visible + .kotak{box-shadow:0 0 0 4px rgba(45,106,79,.25)}
/* ---------- maskot rumah-kos ---------- */
.maskot-wrap{position:relative}
#rumah-kecil{transform-origin:50% 88%;animation:float 5.5s ease-in-out infinite}
.maskot.lompat #rumah-kecil{animation:jump .85s cubic-bezier(.28,.84,.42,1) 2}
#ekor{transform-origin:70px 46px;animation:wag 1.5s ease-in-out infinite}
#mataGanda{transform-origin:center;animation:blinkEyes 5.2s ease-in-out infinite}
.pupil{transform:translate(var(--px,0px),var(--py,0px));transition:transform .2s cubic-bezier(.34,1.56,.64,1)}
#tangan,#sweat,#alisGelisah,#lihatSemu,#senyum,#senyumLebar,#kumat{opacity:0;transition:opacity .22s ease}
#senyum{opacity:1}
#daunKiri,#daunKanan{transition:transform .45s cubic-bezier(.34,1.56,.64,1);transform-origin:top center}
#daunKanan{transform:translateY(44px) rotate(4deg)}
#daunKiri{transform:translate(-56px,52px) rotate(-7deg)}

/* idle / mengetik */
[data-face="idle"] #senyum,[data-face="email"] #senyum,[data-face="lihat"] #senyum,[data-face="lihat"] #senyumLebar{opacity:1}
[data-face="email"] #senyum,[data-face="lihat"] #senyum{opacity:0}
[data-face="lihat"] #mataGanda{animation:squint 1.4s ease-in-out infinite}
[data-face="lihat"] #lihatSemu{opacity:1}
/* tutup mata pakai daun */
[data-face="tutup"] #mataGanda,[data-face="tutup"] #senyum{opacity:0}
[data-face="tutup"] #tangan,[data-face="tutup"] #senyumLebar{opacity:1}
[data-face="tutup"] #daunKiri{transform:translate(40px,4px) rotate(34deg)}
[data-face="tutup"] #daunKanan{transform:translate(-40px,4px) rotate(-34deg)}
/* gelisah / salah */
[data-face="sedih"] #senyum,[data-face="sedih"] #mataGanda{opacity:0}
[data-face="sedih"] #alisGelisah,[data-face="sedih"] #kumat{opacity:1}
[data-face="sedih"] #sweat{opacity:1;animation:sweat 1.1s ease-in infinite}
[data-face="sedih"] #daunKanan{transform:translate(-40px,4px) rotate(-34deg)}
/* senang */
[data-face="senang"] #senyum,[data-face="senang"] #mataGanda{opacity:0}
[data-face="senang"] #senyumLebar,[data-face="senang"] #mataTutupSenang{opacity:1}
[data-face="senang"] #pipi{opacity:1}
#mataTutupSenang{opacity:0;transition:opacity .22s ease;transform-origin:center}
#pipi{opacity:.75;transition:opacity .3s ease}

.balon{position:relative;background:#fff;color:#1f3d34;border-radius:18px;padding:.55rem .85rem;
  font-size:.78rem;font-weight:600;line-height:1.35;box-shadow:0 14px 28px -16px rgba(14,38,33,.6);
  animation:bubbleIn .35s cubic-bezier(.34,1.56,.64,1) both;max-width:15rem}
.balon::after{content:'';position:absolute;left:1.6rem;bottom:-7px;width:14px;height:14px;background:#fff;
  transform:rotate(45deg);border-radius:0 0 4px 0}
</style>
</head>
<body class="antialiased">

<main class="split">
  {{-- ================= PANEL KIRI ================= --}}
  <section class="panel-kiri flex flex-col justify-between px-6 py-7 sm:px-10 lg:px-14 lg:py-12">
    <div class="blob blob-a animate-float" aria-hidden="true"></div>
    <div class="blob blob-b animate-float-slow" aria-hidden="true"></div>

    <div aria-hidden="true" class="absolute inset-0 overflow-hidden">
      <span class="daun left-[6%] text-lg" style="animation-duration:16s;animation-delay:-3s"><i class="fa-solid fa-leaf" style="animation-duration:5.5s"></i></span>
      <span class="daun left-[17%] text-xs" style="animation-duration:20s;animation-delay:-9s"><i class="fa-solid fa-leaf" style="animation-duration:7s"></i></span>
      <span class="daun left-[28%] text-base" style="animation-duration:14s;animation-delay:-12s"><i class="fa-solid fa-seedling" style="animation-duration:4.5s"></i></span>
      <span class="daun left-[40%] text-sm" style="animation-duration:22s;animation-delay:-5s"><i class="fa-solid fa-leaf" style="animation-duration:7.5s"></i></span>
      <span class="daun left-[53%] text-lg" style="animation-duration:17s;animation-delay:-14s"><i class="fa-solid fa-leaf" style="animation-duration:6s"></i></span>
      <span class="daun left-[66%] text-xs" style="animation-duration:13s;animation-delay:-7s"><i class="fa-solid fa-seedling" style="animation-duration:4s"></i></span>
      <span class="daun left-[78%] text-base" style="animation-duration:19s;animation-delay:-10s"><i class="fa-solid fa-leaf" style="animation-duration:6.5s"></i></span>
      <span class="daun left-[89%] text-sm" style="animation-duration:23s;animation-delay:-2s"><i class="fa-solid fa-leaf" style="animation-duration:8s"></i></span>
      <span class="daun left-[95%] text-lg" style="animation-duration:15s;animation-delay:-16s"><i class="fa-solid fa-seedling" style="animation-duration:5s"></i></span>
    </div>

    {{-- brand --}}
    <div class="relative anim-up d1 flex items-center gap-3">
      <span id="logoKami" class="w-11 h-11 rounded-2xl bg-white/15 backdrop-blur flex items-center justify-center text-leaf text-lg cursor-pointer transition hover:bg-white/25"><i class="fa-solid fa-leaf"></i></span>
      <span class="font-display text-2xl sm:text-[1.7rem] font-semibold leading-none">Cendana Resident</span>
    </div>

    {{-- maskot + salam --}}
    <div class="relative anim-up d3 my-6 lg:my-8 flex flex-col items-center lg:items-start text-center lg:text-left">
      <div class="balon anim-up d6 mb-4 lg:ml-2" id="balon">Halo! Aku Si Cendana 👋</div>

      <div class="maskot-wrap" id="maskot" data-face="idle" aria-hidden="true">
        <svg viewBox="0 0 160 138" class="w-44 sm:w-56 lg:w-[18rem] h-auto relative" role="img" aria-label="Maskot rumah Cendana">
          <defs>
            <linearGradient id="gDinding" x1="0" y1="0" x2="0" y2="1">
              <stop offset="0" stop-color="#FFFDF7"/><stop offset="1" stop-color="#E9F4EC"/>
            </linearGradient>
            <linearGradient id="gAtap" x1="0" y1="0" x2="1" y2="1">
              <stop offset="0" stop-color="#3E9A6E"/><stop offset="1" stop-color="#22573F"/>
            </linearGradient>
            <linearGradient id="gDaun" x1="0" y1="0" x2="1" y2="1">
              <stop offset="0" stop-color="#CDEFDC"/><stop offset="1" stop-color="#7FCBA4"/>
            </linearGradient>
            <g id="tanggaDaun">
              <path d="M0 0C13 -14 34 -14 44 0 34 14 13 14 0 0Z" fill="url(#gDaun)"/>
              <path d="M2 0H42" stroke="#2D6A4F" stroke-width="2" stroke-linecap="round" opacity=".55"/>
            </g>
          </defs>

          <ellipse cx="80" cy="131" rx="47" ry="7" fill="#0E2621" opacity=".16"/>

          <g id="rumah-kecil">
            <g id="ekor">
              <path d="M60 46C44 44 38 30 46 22" stroke="#2D6A4F" stroke-width="7" stroke-linecap="round" fill="none"/>
              <path d="M46 22C44 14 52 8 60 12 60 20 54 25 46 22Z" fill="#B7E4C7"/>
            </g>
            <rect x="116" y="30" width="14" height="24" rx="4" fill="#22573F"/>
            <rect x="119" y="34" width="8" height="5" rx="2" fill="#0E2621" opacity=".45"/>

            <rect x="26" y="54" width="108" height="74" rx="21" fill="url(#gDinding)"/>
            <path d="M80 14 142 62H18Z" fill="url(#gAtap)"/>
            <path d="M80 14 142 62" stroke="#1B4633" stroke-width="3" stroke-linecap="round" opacity=".45" fill="none"/>

            <g id="mataGanda">
              <ellipse cx="60" cy="84" rx="8" ry="9" fill="#173B2E"/>
              <ellipse cx="100" cy="84" rx="8" ry="9" fill="#173B2E"/>
              <circle class="pupil" cx="62.5" cy="81" r="2.7" fill="#fff"/>
              <circle class="pupil" cx="102.5" cy="81" r="2.7" fill="#fff"/>
            </g>
            <g id="mataTutupSenang" fill="none" stroke="#173B2E" stroke-width="4.5" stroke-linecap="round">
              <path d="M52 86q8 -9 16 0"/><path d="M92 86q8 -9 16 0"/>
            </g>
            <path id="lihatSemu" d="M92 84q8 -6 16 0" fill="none" stroke="#173B2E" stroke-width="3.5" stroke-linecap="round"/>
            <g id="alisGelisah" fill="none" stroke="#173B2E" stroke-width="4" stroke-linecap="round">
              <path d="M50 72q10 -7 20 -3"/><path d="M110 72q-10 -7 -20 -3"/>
            </g>
            <ellipse id="pipi" cx="45" cy="96" rx="7.5" ry="4.5" fill="#F6A6A6"/>
            <ellipse cx="115" cy="96" rx="7.5" ry="4.5" fill="#F6A6A6"/>
            <path id="senyum" d="M70 97q10 9 20 0" stroke="#173B2E" stroke-width="4.5" fill="none" stroke-linecap="round"/>
            <path id="senyumLebar" d="M67 94q13 15 26 0" stroke="#173B2E" stroke-width="4.5" fill="none" stroke-linecap="round"/>
            <path id="kumat" d="M71 101q9 -7 18 0" stroke="#173B2E" stroke-width="4.5" fill="none" stroke-linecap="round"/>
            <path id="tangan" d="M62 92q10 10 20 0M78 92q10 10 20 0" stroke="#173B2E" stroke-width="4.5" fill="none" stroke-linecap="round"/>
            <path id="sweat" d="M132 70q7 9 0 13 -7 -4 0 -13Z" fill="#9AD7F3"/>

            <g id="daunKanan"><use href="#tanggaDaun" transform="translate(96 66) scale(.8) rotate(160)"/></g>
            <g id="daunKiri"><use href="#tanggaDaun" transform="translate(16 66) scale(.8) rotate(20)"/></g>
          </g>
        </svg>
      </div>
    </div>

    {{-- bagian bawah panel --}}
    <div class="relative">
      <p class="anim-up d4 font-display text-[1.7rem] sm:text-5xl leading-[1.08] mb-4">Kelola kos<br>jadi lebih ringan.</p>
      <p class="anim-up d5 text-white/70 text-sm leading-relaxed max-w-sm">Kamar, penghuni, barang berbarcode, laporan — satu sistem. Bu Anik pun pakai tanpa bingung.</p>
      <div class="anim-up d6 mt-5 space-y-2.5 text-sm text-white/85">
        <div class="flex items-center gap-3"><span class="w-7 h-7 rounded-lg bg-white/12 flex items-center justify-center text-leaf text-xs"><i class="fa-solid fa-video"></i></span>CCTV area bersama</div>
        <div class="flex items-center gap-3"><span class="w-7 h-7 rounded-lg bg-white/12 flex items-center justify-center text-leaf text-xs"><i class="fa-solid fa-qrcode"></i></span>Barcode tiap barang kos</div>
        <div class="flex items-center gap-3"><span class="w-7 h-7 rounded-lg bg-white/12 flex items-center justify-center text-leaf text-xs"><i class="fa-solid fa-broom"></i></span>Jadwal cleaning tercatat rapi</div>
      </div>
      <p class="anim-up d7 mt-6 text-[11px] text-white/45">© {{ date('Y') }} Cendana Resident • Malang</p>
    </div>
  </section>


  {{-- ================= PANEL FORM ================= --}}
  <section class="relative flex items-center justify-center px-5 py-10 sm:px-8 lg:px-12 lg:py-12">
    <div class="w-full max-w-md">

      <div class="lg:hidden flex items-center gap-2.5 mb-7 anim-up d1">
        <span class="w-9 h-9 rounded-xl bg-[#1A3C34] text-[#B7E4C7] flex items-center justify-center"><i class="fa-solid fa-leaf"></i></span>
        <span class="font-display text-xl text-[#1A3C34]">Cendana Resident</span>
      </div>

      <p class="anim-up d2 text-[11px] font-extrabold uppercase tracking-[.18em] text-[#2D6A4F]">Panel pengelola</p>
      <h1 class="anim-up d3 font-display text-4xl sm:text-5xl leading-[1.05] text-[#1A3C34] mt-2">
        <span id="salam">Halo</span>, <span class="type-in">selamat datang.</span>
      </h1>
      <p class="anim-up d4 text-[15px] text-[#6b6b62] mt-3">Masuk dulu buat ngurus kamar & penghuni.</p>

      @if($errors->any())
      <div class="anim-up d1 mt-5 flex items-start gap-3 bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-2xl text-sm wobble" id="kotakError">
        <i class="fa-solid fa-circle-exclamation mt-0.5"></i>
        <span>{{ $errors->first() }}</span>
      </div>
      @endif

      <div class="kartu anim-up d5 mt-6 p-5 sm:p-7">

        <form method="POST" action="{{ route('login.do') }}" id="loginForm" novalidate>
          @csrf

          <div class="grp flex items-center gap-3 px-4 py-3.5">
            <i class="fa-solid fa-envelope ikon text-[#9a9a90] text-sm transition-colors"></i>
            <div class="relative flex-1">
              <label for="email" class="lbl block text-[11px] font-bold uppercase tracking-wider text-[#8b8b81]">Email</label>
              <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="email"
                class="w-full text-[15px] text-[#2b2b28] placeholder:text-transparent pt-0.5" placeholder="nama@kos.id">
            </div>
          </div>

          <div class="grp flex items-center gap-3 px-4 py-3.5 mt-3.5">
            <i class="fa-solid fa-lock ikon text-[#9a9a90] text-sm transition-colors"></i>
            <div class="relative flex-1">
              <label for="password" class="lbl block text-[11px] font-bold uppercase tracking-wider text-[#8b8b81]">Password</label>
              <input id="password" name="password" type="password" required autocomplete="current-password"
                class="w-full text-[15px] text-[#2b2b28] placeholder:text-transparent pt-0.5 pr-7" placeholder="••••••••">
            </div>
            <button type="button" id="btnMata" aria-label="Tampilkan password"
              class="text-[#9a9a90] hover:text-[#2D6A4F] transition-colors p-1">
              <i id="pwIcon" class="fa-solid fa-eye"></i>
            </button>
          </div>

          <div class="flex items-center justify-between mt-4">
            <label class="flex items-center gap-2.5 text-sm text-[#4b4b45] cursor-pointer select-none">
              <input type="checkbox" name="remember" id="remember" class="sr-only">
              <span class="kotak w-5 h-5 rounded-md border border-[#d6d6cc] bg-white flex items-center justify-center">
                <svg viewBox="0 0 20 20" class="w-3.5 h-3.5" fill="none" stroke="#fff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path class="cek" d="M4 10.5l4 4 8-9"/></svg>
              </span>
              <span>Ingat saya</span>
            </label>
            <a href="/forgot-password" class="text-sm font-semibold text-[#2D6A4F] hover:text-[#1A3C34] garis-bawah">Lupa password?</a>
          </div>

          <button type="submit" id="btnMasuk" class="btn-masuk w-full mt-5 rounded-2xl px-5 py-4 text-white font-bold text-[15px] tracking-wide flex items-center justify-center gap-2.5">
            <span id="btnTeks">Masuk</span>
            <span id="btnTitik" style="display:none" class="gap-1.5"><span class="titik w-1.5 h-1.5 rounded-full bg-white"></span><span class="titik w-1.5 h-1.5 rounded-full bg-white"></span><span class="titik w-1.5 h-1.5 rounded-full bg-white"></span></span>
            <i class="panah fa-solid fa-arrow-right text-sm"></i>
          </button>
        </form>

        <p class="text-center text-[12.5px] text-[#8b8b81] mt-4">Belum punya akun? Hubungi pengelola kos.</p>
      </div>

      <a href="/" class="anim-up d6 mt-6 mx-auto w-fit flex items-center gap-2 text-[15px] font-semibold text-[#5b5b53] hover:text-[#2D6A4F] transition-colors group">
        <i class="fa-solid fa-arrow-left transition-transform group-hover:-translate-x-1.5"></i> Kembali ke website
      </a>
    </div>
  </section>
</main>

<script>
(function(){
  var maskot=document.getElementById('maskot'),
      balon=document.getElementById('balon'),
      wajahBaru=null, adaError=!!document.getElementById('kotakError');

  function wajah(f){ if(maskot) maskot.setAttribute('data-face',f); }
  function ucapan(t){ if(!balon||t===balon.textContent) return; balon.classList.remove('balon'); void balon.offsetWidth; balon.textContent=t; balon.classList.add('balon'); }

  /* --- sapaan sesuai waktu --- */
  var j=new Date().getHours(), salam=document.getElementById('salam');
  if(salam) salam.textContent = j<11?'Selamat pagi':(j<15?'Selamat siang':(j<18?'Selamat sore':'Selamat malam'));

  /* --- mata mengikuti kursor --- */
  function gerak(px,py){ if(!maskot) return; maskot.style.setProperty('--px',px+'px'); maskot.style.setProperty('--py',py+'px'); }
  window.addEventListener('mousemove',function(e){
    if(wajahBaru) return;
    var r=maskot.getBoundingClientRect(),
        dx=e.clientX-(r.left+r.width/2), dy=e.clientY-(r.top+r.height*0.58),
        m=Math.max(1,Math.hypot(dx,dy)), batas=4.5;
    gerak(dx/m*batas, dy/m*batas*0.8);
  },{passive:true});

  /* --- interaksi form --- */
  var email=document.getElementById('email'), pw=document.getElementById('password');
  if(email){
    email.addEventListener('focus',function(){ wajahBaru=null; wajah('email'); ucapan('Email-nya apa? Ketik ya ✍️'); });
    email.addEventListener('blur',function(){ if(!wajahBaru) wajah('idle'); });
  }
  if(pw){
    pw.addEventListener('focus',function(){ wajahBaru=null; wajah('tutup'); ucapan('Aku nggak ngintip kok 🙈'); });
    pw.addEventListener('blur',function(){ if(!wajahBaru){ wajah('idle'); ucapan('Halo! Aku Si Cendana 👋'); } });
  }

  /* --- lihat password --- */
  var btnMata=document.getElementById('btnMata'), pwIcon=document.getElementById('pwIcon');
  if(btnMata) btnMata.addEventListener('click',function(){
    if(pw.type==='password'){
      pw.type='text'; pwIcon.classList.replace('fa-eye','fa-eye-slash'); wajah('lihat'); ucapan('Lho, kelihatan 👀');
    }else{
      pw.type='password'; pwIcon.classList.replace('fa-eye-slash','fa-eye'); wajah('tutup'); ucapan('Aku nggak ngintip kok 🙈');
    }
  });

  /* --- state error / sedih --- */
  if(adaError){
    wajahBaru='sedih'; wajah('sedih');
    ucapan((pw&&pw.value)?'Ups, passwordnya salah 🥺':'Aduh, datanya belum cocok 🥺');
    gerak(0,0);
  }

  /* --- klik logo: lompat --- */
  var logo=document.getElementById('logoKami'), konyol=[
    'Easter egg! Klik-klik lagi ya 🍃','Halo dari Si Cendana 🏡','Ayo kerja, kas kos nunggu ✨'
  ], iKonyol=0;
  if(logo) logo.addEventListener('click',function(){
    wajahBaru='senang'; wajah('senang'); ucapan(konyol[iKonyol++%konyol.length]);
    maskot.classList.remove('lompat'); void maskot.offsetWidth; maskot.classList.add('lompat');
    setTimeout(function(){ maskot.classList.remove('lompat');
      if(!adaError){ wajahBaru=null; wajah('idle'); ucapan('Halo! Aku Si Cendana 👋'); gerak(0,0); } },1800);
  });

  /* --- submit --- */
  var form=document.getElementById('loginForm');
  if(form) form.addEventListener('submit',function(e){
    if(!form.checkValidity()){ e.preventDefault(); e.stopPropagation();
      wajahBaru='sedih'; wajah('sedih'); ucapan('Isi email & passwordnya dulu ya 🥺');
      form.reportValidity(); return; }
    var b=document.getElementById('btnMasuk'),t=document.getElementById('btnTeks'),d=document.getElementById('btnTitik');
    b.disabled=true; t.style.display='none'; d.style.display='flex';
    b.querySelector('.panah').style.display='none';
    wajahBaru='senang'; wajah('senang'); ucapan('Bentar ya, aku bukakan pintunya… 🔑');
  });

  /* --- animasi wajah saat kartu error bergetar --- */
  var kotak=document.getElementById('kotakError');
  if(kotak) kotak.addEventListener('animationend',function(){ kotak.classList.remove('wobble'); },{once:true});
})();
</script>
</body>
</html>
