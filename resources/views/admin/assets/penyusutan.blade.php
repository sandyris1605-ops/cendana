@extends('layouts.admin')
@section('title','Penyusutan')
@section('content')
@include('admin.partials.header', ['judul'=>'Penyusutan Garis Lurus', 'sub'=>'Nilai buku turun per bulan sesuai umur pakai barang', 'ctaSlot'=>'<form method="POST" action="'.route('admin.penyusutan.hitung').'">'.csrf_field().'<button class="inline-flex items-center gap-2 bg-slate-900 hover:bg-slate-800 text-white px-4 py-2 rounded-lg text-sm shadow-sm transition"><i class="fa fa-calculator text-xs"></i>Hitung Bulan '.now()->format('Y-m').'</button></form>'])
@include('admin.partials.search', ['placeholder'=>'Cari barcode / nama barang...'])
<style>
@keyframes simNaik { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }
.sim-baris { animation: simNaik .35s ease both; }
.sim-gradasi { background: linear-gradient(120deg, #4f46e5, #8b5cf6 55%, #d97706); -webkit-background-clip: text; background-clip: text; color: transparent; }
@media (prefers-reduced-motion: reduce) { .sim-baris { animation: none; } #simBar { transition: none !important; } }
</style>
<div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm mb-4 overflow-hidden">
<div class="flex items-center gap-3 mb-4">
<div class="w-10 h-10 rounded-xl bg-violet-50 text-violet-600 flex items-center justify-center"><i class="fa fa-wand-magic-sparkles"></i></div>
<div><div class="font-bold">Simulasi Penyusutan</div><div class="text-xs text-slate-400">Gambaran nilai buku setelah N bulan — murni hitungan, tidak menyimpan ke database</div></div>
</div>
<div><label class="block text-[11px] font-semibold uppercase tracking-wider text-slate-500 mb-1">Cari barang (isi otomatis)</label>
<div id="simDipilih" class="hidden items-center justify-between gap-2 w-full border border-emerald-200 bg-emerald-50 rounded-lg px-3 py-2 text-sm mb-2">
<span class="flex items-center gap-2 min-w-0"><i class="fa fa-circle-check text-emerald-500 text-xs shrink-0"></i><span id="simDipilihNama" class="font-medium text-emerald-800 truncate"></span></span>
<button type="button" id="simBersih" class="text-emerald-600 hover:text-rose-600 transition shrink-0" title="Ganti barang"><i class="fa fa-xmark"></i></button>
</div>
<div id="simKotakCari" class="relative">
<i class="fa fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
<input type="text" id="simCari" autocomplete="off" placeholder="Ketik nama barang atau kosongkan untuk manual..." class="w-full pl-9 pr-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 outline-none">
<div id="simHasil" class="hidden absolute z-10 left-0 right-0 mt-1 bg-white border border-slate-200 rounded-lg shadow-lg max-h-56 overflow-y-auto"></div>
</div>
</div>
<div class="grid grid-cols-3 gap-3 mt-3">
<div><label class="block text-[11px] font-semibold uppercase tracking-wider text-slate-500 mb-1">Harga (Rp)</label><input id="simHarga" type="number" min="0" value="1500000" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-amber-500"></div>
<div><label class="block text-[11px] font-semibold uppercase tracking-wider text-slate-500 mb-1">Residu (Rp)</label><input id="simResidu" type="number" min="0" value="0" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-amber-500"></div>
<div><label class="block text-[11px] font-semibold uppercase tracking-wider text-slate-500 mb-1">Umur (bln)</label><input id="simUmur" type="number" min="1" value="48" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-amber-500"></div>
</div>
<div class="mt-4">
<div class="flex items-center justify-between text-xs text-slate-500 mb-1"><span>Setelah berapa bulan?</span><span class="font-bold text-slate-800 text-sm"><span id="simBulanLabel">30</span> bulan</span></div>
<input id="simBulan" type="range" min="1" max="120" value="30" class="w-full accent-amber-500">
</div>
</div>
</div>
<div class="mt-5 rounded-xl bg-gradient-to-br from-violet-50 via-white to-amber-50 border border-slate-200 p-5">
<div class="text-[11px] uppercase tracking-widest text-slate-400 font-semibold">Nilai buku setelah <span id="simBulanLabel2">30</span> bulan</div>
<div class="flex flex-wrap items-center gap-3 mt-1">
<div id="simOutBuku" class="text-3xl font-bold tracking-tight sim-gradasi">Rp 0</div>
<span id="simOutPersen" class="inline-flex items-center gap-1 text-[11px] font-bold px-2.5 py-1 rounded-full bg-amber-500/15 text-amber-700 border border-amber-500/30">0% susut</span>
</div>
<div class="grid grid-cols-2 md:grid-cols-4 gap-3 mt-4">
<div class="bg-white rounded-lg border border-slate-200 p-3 shadow-sm"><div class="text-[11px] uppercase tracking-wider text-slate-400 font-semibold">Susut / bulan</div><div id="simOutSusut" class="font-bold mt-0.5">Rp 0</div></div>
<div class="bg-white rounded-lg border border-slate-200 p-3 shadow-sm"><div class="text-[11px] uppercase tracking-wider text-slate-400 font-semibold">Akumulasi</div><div id="simOutAkum" class="font-bold mt-0.5 text-rose-600">Rp 0</div></div>
<div class="bg-white rounded-lg border border-slate-200 p-3 shadow-sm"><div class="text-[11px] uppercase tracking-wider text-slate-400 font-semibold">Sisa menuju residu</div><div id="simOutSisa" class="font-bold mt-0.5">0 bln</div></div>
<div class="bg-white rounded-lg border border-slate-200 p-3 shadow-sm"><div class="text-[11px] uppercase tracking-wider text-slate-400 font-semibold">Status</div><div id="simOutStatus" class="font-bold mt-0.5 text-sm">—</div></div>
</div>
<div class="mt-4"><div class="w-full h-2.5 rounded-full bg-white border border-slate-200 overflow-hidden"><div id="simBar" class="h-full bg-gradient-to-r from-indigo-500 via-violet-500 to-amber-500 rounded-full transition-all duration-500" style="width:0%"></div></div><div id="simCatatan" class="text-xs text-slate-500 mt-1.5"></div></div>
<div class="mt-4 overflow-x-auto rounded-lg border border-slate-200 bg-white"><table class="w-full text-xs"><thead><tr class="text-left text-slate-400 uppercase tracking-wider bg-slate-50"><th class="px-3 py-2">Bulan</th><th class="px-3 py-2 text-right">Susut</th><th class="px-3 py-2 text-right">Nilai Buku</th></tr></thead><tbody id="simTabel" class="divide-y divide-slate-100"></tbody></table></div>
</div>
</div>
<div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-x-auto">
<table class="w-full text-sm">
<thead><tr class="bg-slate-50 text-left text-xs uppercase tracking-wider text-slate-500 border-b border-slate-200">
<th class="px-4 py-3">Barcode</th><th class="px-4 py-3">Barang</th><th class="px-4 py-3">Harga Perolehan</th><th class="px-4 py-3">Nilai Buku</th><th class="px-4 py-3">Susut</th>
</tr></thead>
<tbody class="divide-y divide-slate-100">
@forelse($units as $u)
@php $susut = max(0, $u->harga_perolehan - $u->nilai_buku); @endphp
<tr class="hover:bg-slate-50 transition">
<td class="px-4 py-3 font-mono text-xs font-semibold">{{ $u->kode_barcode }}</td>
<td class="px-4 py-3">{{ optional($u->item)->nama }}</td>
<td class="px-4 py-3">Rp {{ number_format($u->harga_perolehan,0,',','.') }}</td>
<td class="px-4 py-3 font-semibold">Rp {{ number_format($u->nilai_buku,0,',','.') }}</td>
<td class="px-4 py-3">
<div class="flex items-center gap-2">
<div class="w-20 h-1.5 rounded-full bg-slate-100 overflow-hidden"><div class="h-full bg-amber-500 rounded-full" style="width:{{ $u->harga_perolehan ? round($susut/$u->harga_perolehan*100) : 0 }}%"></div></div>
<span class="text-xs text-slate-500">{{ $u->harga_perolehan ? round($susut/$u->harga_perolehan*100) : 0 }}%</span>
</div>
</td>
</tr>
@empty
<tr><td colspan="5" class="px-4 py-10 text-center text-slate-400"><i class="fa fa-chart-line text-2xl mb-2 block"></i>Tidak ada unit yang cocok.</td></tr>
@endforelse
</tbody>
</table>
</div>
@include('admin.partials.pager', ['paginator'=>$units])
@endsection

@push('scripts')
<script>
(function () {
    var rp = function (n) { return 'Rp ' + Number(n || 0).toLocaleString('id-ID'); };
    var katalog = {!! $masterJson !!};
    var cari = document.getElementById('simCari'), hasil = document.getElementById('simHasil'),
        kotak = document.getElementById('simKotakCari'),
        dipilih = document.getElementById('simDipilih'), dipilihNama = document.getElementById('simDipilihNama'),
        harga = document.getElementById('simHarga'),
        residu = document.getElementById('simResidu'), umur = document.getElementById('simUmur'),
        bulan = document.getElementById('simBulan'), bulanLabel = document.getElementById('simBulanLabel'),
        bulanLabel2 = document.getElementById('simBulanLabel2');
    var gerakKecil = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var animBuku = null;
    function tampilBuku(target) {
        var el = document.getElementById('simOutBuku');
        if (animBuku) cancelAnimationFrame(animBuku);
        var awal = parseFloat(el.dataset.nilai || '0') || 0;
        el.dataset.nilai = target;
        if (gerakKecil || awal === target) { el.textContent = rp(target); return; }
        var mulai = null, durasi = 400;
        var langkah = function (t) {
            if (!mulai) mulai = t;
            var p = Math.min(1, (t - mulai) / durasi), e = 1 - Math.pow(1 - p, 3);
            el.textContent = rp(Math.round(awal + (target - awal) * e));
            if (p < 1) animBuku = requestAnimationFrame(langkah);
        };
        animBuku = requestAnimationFrame(langkah);
    }
    function hitung() {
        var h = Math.max(0, parseFloat(harga.value) || 0), r = Math.max(0, parseFloat(residu.value) || 0),
            u = Math.max(1, parseInt(umur.value) || 1), n = Math.max(1, parseInt(bulan.value) || 1);
        bulanLabel.textContent = n; bulanLabel2.textContent = n;
        var perBulan = Math.round(Math.max(0, h - r) / u);
        var buku = Math.max(r, h - perBulan * n), akum = h - buku, persen = h ? Math.round(akum / h * 100) : 0;
        document.getElementById('simOutSusut').textContent = rp(perBulan);
        tampilBuku(buku);
        document.getElementById('simOutAkum').textContent = rp(akum);
        document.getElementById('simOutPersen').textContent = persen + '% susut';
        document.getElementById('simBar').style.width = Math.min(100, persen) + '%';
        var sisa = perBulan > 0 && buku > r ? Math.ceil((buku - r) / perBulan) : 0;
        var mentok = buku <= r;
        document.getElementById('simOutSisa').textContent = mentok ? '0 bln' : sisa + ' bln';
        var statusEl = document.getElementById('simOutStatus');
        statusEl.textContent = mentok ? 'Mentok residu' : 'Menyusut';
        statusEl.className = 'font-bold mt-0.5 text-sm ' + (mentok ? 'text-slate-500' : 'text-emerald-600');
        document.getElementById('simCatatan').textContent = mentok
            ? 'Sudah mentok di nilai residu (' + rp(r) + ') — tidak susut lagi.'
            : 'Sisa ' + sisa + ' bulan lagi menuju nilai residu.';
        var tb = document.getElementById('simTabel'), html = '', batas = Math.min(n, 12), i, b;
        for (i = 1; i <= batas; i++) {
            b = Math.max(r, h - perBulan * i);
            var sorot = (i === n) ? ' bg-amber-50/60' : '';
            var tunda = gerakKecil ? '' : ' style="animation-delay:' + (i * 35) + 'ms"';
            html += '<tr class="sim-baris' + sorot + '"' + tunda + '><td class="px-3 py-1.5">Bulan ' + i + '</td><td class="px-3 py-1.5 text-right">' + rp(perBulan) + '</td><td class="px-3 py-1.5 text-right font-semibold">' + rp(b) + '</td></tr>';
        }
        if (n > 12) html += '<tr><td colspan="3" class="px-3 py-1.5 text-slate-400">… s/d bulan ' + n + ' → ' + rp(buku) + '</td></tr>';
        tb.innerHTML = html;
    }
    function pilih(m) {
        harga.value = m.harga; umur.value = m.umur || 12;
        dipilihNama.textContent = m.nama + ' — ' + rp(m.harga) + ' / ' + m.umur + ' bln';
        dipilih.classList.remove('hidden'); dipilih.classList.add('flex');
        kotak.classList.add('hidden'); hasil.classList.add('hidden');
        hitung();
    }
    function bersih() {
        dipilih.classList.add('hidden'); dipilih.classList.remove('flex');
        kotak.classList.remove('hidden');
        cari.value = ''; cari.focus();
    }
    document.getElementById('simBersih').addEventListener('click', bersih);
    cari.addEventListener('input', function () {
        var q = this.value.trim().toLowerCase();
        if (!q) { hasil.classList.add('hidden'); return; }
        var cocok = katalog.filter(function (m) { return m.nama.toLowerCase().indexOf(q) !== -1; }).slice(0, 8);
        hasil.innerHTML = cocok.length
            ? cocok.map(function (m) { return '<button type="button" data-id="' + m.id + '" class="w-full text-left px-3 py-2 text-sm hover:bg-amber-50 flex items-center justify-between gap-2"><span class="font-medium truncate">' + m.nama + '</span><span class="text-xs text-slate-400 shrink-0">' + rp(m.harga) + ' / ' + m.umur + ' bln</span></button>'; }).join('')
            : '<div class="px-3 py-2 text-sm text-slate-400">Tidak ada barang cocok — lanjutkan ketik manual.</div>';
        hasil.classList.remove('hidden');
    });
    hasil.addEventListener('click', function (e) {
        var btn = e.target.closest('button[data-id]');
        if (!btn) return;
        var m = katalog.find(function (x) { return String(x.id) === btn.dataset.id; });
        if (m) pilih(m);
    });
    document.addEventListener('click', function (e) { if (!kotak.contains(e.target)) hasil.classList.add('hidden'); });
    [harga, residu, umur, bulan].forEach(function (el) { el.addEventListener('input', hitung); });
    hitung();
})();
</script>
@endpush