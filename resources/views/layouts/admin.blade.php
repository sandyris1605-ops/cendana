<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>@yield('title','Admin') - Cendana Resident</title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
 body{font-family:'Plus Jakarta Sans',system-ui,sans-serif}
 #sidebar{transition:transform .3s ease}
 ::-webkit-scrollbar{width:6px}::-webkit-scrollbar-thumb{background:rgba(255,255,255,.15);border-radius:3px}
</style>
</head>
<body class="bg-slate-100">
<div class="flex min-h-screen">
<aside id="sidebar" class="w-64 bg-slate-950 text-slate-300 fixed h-full overflow-y-auto z-40 -translate-x-full md:translate-x-0">
<div class="p-5 border-b border-white/10 flex items-center gap-3">
<i class="fa fa-house-chimney text-amber-500"></i>
<div><div class="font-bold text-white leading-tight">Cendana <span class="text-amber-400">Resident</span></div><div class="text-[11px] text-slate-500 tracking-widest uppercase">Admin Panel</div></div>
</div>
<nav class="p-3 text-sm space-y-0.5">
@php
$menu = [
    ['admin.dashboard','admin.dashboard','fa-gauge','Dashboard'],
    ['admin.rooms.*','admin.rooms.index','fa-bed','Kamar'],
    ['admin.items.*','admin.items.index','fa-box','Barang'],
    ['admin.assets.*','admin.assets.index','fa-boxes-stacked','Inventaris & Aset'],
    ['admin.penggantian','admin.penggantian','fa-rotate','Penggantian'],
    ['admin.penyusutan*','admin.penyusutan','fa-chart-line','Penyusutan'],
    ['admin.barcode.*','admin.barcode.index','fa-barcode','Barcode'],
    ['admin.purchases.*','admin.purchases.index','fa-cart-shopping','Pembelian'],
    ['admin.gudang.*','admin.gudang.index','fa-warehouse','Stok Gudang'],
    ['admin.saldo-kas.*','admin.saldo-kas.index','fa-sack-dollar','Saldo Kas'],
    ['admin.expenses.*','admin.expenses.index','fa-wallet','Pengeluaran'],
    ['admin.tenants.*','admin.tenants.index','fa-users','Penghuni'],
    ['admin.employees.*','admin.employees.index','fa-id-card','Karyawan'],
    ['admin.suppliers.*','admin.suppliers.index','fa-truck','Supplier'],
    ['admin.laporan.*','admin.laporan.index','fa-print','Laporan'],
    ['admin.pengaturan*','admin.pengaturan','fa-gear','Pengaturan'],
];
@endphp
@foreach($menu as $m)
@php $aktif = request()->routeIs($m[0]); @endphp
<a href="{{ route($m[1]) }}" class="flex items-center gap-3 px-3 py-2 rounded-lg border-l-2 transition {{ $aktif ? 'border-amber-500 bg-white/10 text-white font-semibold' : 'border-transparent hover:bg-white/5 hover:text-white' }}">
<i class="fa {{ $m[2] }} w-5 text-center {{ $aktif ? 'text-amber-400' : 'text-slate-500' }}"></i> {{ $m[3] }}
</a>
@endforeach
</nav>
</aside>
<div id="overlay" class="fixed inset-0 bg-black/50 z-30 hidden md:hidden"></div>
<div class="flex-1 md:ml-64">
<header class="bg-white/90 backdrop-blur border-b border-slate-200 px-4 md:px-6 py-3 flex items-center gap-3 sticky top-0 z-20">
<button id="btnMenu" class="md:hidden text-slate-600 hover:text-slate-900 text-lg"><i class="fa fa-bars"></i></button>
<form action="{{ route('admin.cari') }}" method="GET" class="flex-1 max-w-md">
<div class="relative">
<i class="fa fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
<input name="q" value="{{ request('q') }}" placeholder="Cari kamar, barang, barcode, penghuni..." class="w-full pl-9 pr-3 py-2 bg-slate-100 border border-transparent focus:bg-white focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 rounded-lg text-sm outline-none transition">
</div>
</form>
<div class="ml-auto flex items-center gap-3">
<div class="hidden sm:flex items-center gap-3">
<div class="w-9 h-9 rounded-full bg-slate-900 text-amber-400 flex items-center justify-center text-sm font-bold">{{ strtoupper(substr(auth()->user()->name ?? 'A',0,1)) }}</div>
<div class="text-sm leading-tight"><div class="font-semibold text-slate-800">{{ auth()->user()->name ?? '' }}</div><div class="text-xs text-slate-400 capitalize">{{ auth()->user()->role ?? '' }}</div></div>
</div>
<form method="POST" action="{{ route('logout') }}">@csrf<button class="bg-slate-100 hover:bg-slate-200 text-slate-600 px-3 py-2 rounded-lg text-sm transition"><i class="fa fa-right-from-bracket mr-1"></i><span class="hidden sm:inline">Logout</span></button></form>
</div>
</header>
<main class="p-4 md:p-6">
@if(session('ok'))<div class="flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl mb-4 text-sm"><i class="fa fa-circle-check text-emerald-500"></i>{{ session('ok') }}</div>@endif
@if($errors->any())<div class="flex items-center gap-3 bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded-xl mb-4 text-sm"><i class="fa fa-circle-exclamation text-rose-500"></i>{{ $errors->first() }}</div>@endif
@yield('content')
</main>
</div>
</div>
<script>
const sb=document.getElementById('sidebar'),ov=document.getElementById('overlay');
document.getElementById('btnMenu').onclick=()=>{sb.classList.toggle('-translate-x-full');ov.classList.toggle('hidden')};
ov.onclick=()=>{sb.classList.add('-translate-x-full');ov.classList.add('hidden')};
</script>
<script>
// Menu aksi kebab (⋮) per baris tabel: dropdown fixed agar tidak terpotong overflow-x-auto.
(function () {
    function tutupSemua(kecuali) {
        document.querySelectorAll('.aksi-menu').forEach(function (m) {
            if (m !== kecuali) { m.classList.add('hidden'); m.style.position = ''; m.style.top = ''; m.style.left = ''; }
        });
        document.querySelectorAll('.aksi-toggle').forEach(function (b) { b.setAttribute('aria-expanded', 'false'); });
    }
    function posisikan(btn, menu) {
        var r = btn.getBoundingClientRect();
        menu.style.position = 'fixed';
        menu.classList.remove('hidden');
        var mw = menu.offsetWidth, mh = menu.offsetHeight, pad = 8;
        var kiri = Math.min(r.right - mw, window.innerWidth - mw - pad);
        var atas = (r.bottom + pad + mh > window.innerHeight) ? Math.max(pad, r.top - mh - pad) : r.bottom + pad;
        menu.style.left = Math.max(pad, kiri) + 'px';
        menu.style.top = atas + 'px';
    }
    document.addEventListener('click', function (e) {
        var btn = e.target.closest('.aksi-toggle');
        if (btn) {
            var menu = btn.parentElement.querySelector('.aksi-menu');
            var terbuka = !menu.classList.contains('hidden');
            tutupSemua();
            if (!terbuka) { posisikan(btn, menu); btn.setAttribute('aria-expanded', 'true'); }
            return;
        }
        if (!e.target.closest('.aksi-menu')) tutupSemua();
    });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') tutupSemua(); });
    window.addEventListener('scroll', function () { tutupSemua(); }, true);
    window.addEventListener('resize', function () { tutupSemua(); });
})();
</script>

{{-- ===== Modal Konfirmasi Hapus (modern, terpusat) ===== --}}
<div id="konfirmasiModal" class="hidden fixed inset-0 z-[60] flex items-center justify-center p-4">
  <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="tutupKonfirmasi()"></div>
  <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-sm p-6 text-center" role="dialog" aria-modal="true" aria-labelledby="konfirmasiJudul">
    <div class="w-14 h-14 mx-auto rounded-full bg-rose-50 text-rose-500 flex items-center justify-center mb-4"><i class="fa fa-triangle-exclamation text-2xl"></i></div>
    <h3 id="konfirmasiJudul" class="text-lg font-bold text-slate-800 mb-1">Konfirmasi Hapus</h3>
    <p id="konfirmasiPesan" class="text-sm text-slate-500 leading-relaxed whitespace-pre-line"></p>
    <div class="flex gap-2 mt-6">
      <button type="button" onclick="tutupKonfirmasi()" class="flex-1 px-4 py-2.5 rounded-lg text-sm border border-slate-200 text-slate-600 hover:bg-slate-50 transition font-semibold">Batal</button>
      <button type="button" id="konfirmasiYa" class="flex-1 px-4 py-2.5 rounded-lg text-sm bg-rose-500 hover:bg-rose-600 text-white font-semibold shadow-sm transition"><i class="fa fa-trash mr-1 text-xs"></i>Ya, Hapus</button>
    </div>
  </div>
</div>
<script>
// Ganti native confirm() bawaan browser dengan modal modern di tengah.
// Pakai: onsubmit="return konfirmasiHapus('pesan...', this)"  (atau onclick untuk tombol form=).
(function () {
    var formAktif = null;
    var modal = document.getElementById('konfirmasiModal');
    var pesanEl = document.getElementById('konfirmasiPesan');
    var btnYa = document.getElementById('konfirmasiYa');

    function tutup() {
        modal.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
        formAktif = null;
    }
    function dapatkanForm(el) {
        if (!el) return null;
        if (el.tagName === 'FORM') return el;
        return el.form || null;
    }
    window.tutupKonfirmasi = tutup;
    window.konfirmasiHapus = function (pesan, el) {
        formAktif = dapatkanForm(el);
        pesanEl.textContent = pesan;
        modal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
        return false; // cegah submit langsung; form dikirim saat user menekan "Ya, Hapus"
    };
    btnYa.addEventListener('click', function () {
        var f = formAktif;
        tutup();
        if (f) f.submit();
    });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && !modal.classList.contains('hidden')) tutup(); });
})();
</script>
@stack('scripts')
</body>
</html>
