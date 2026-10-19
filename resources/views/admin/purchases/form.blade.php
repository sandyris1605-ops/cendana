@extends('layouts.admin')
@section('title','Beli Barang')
@section('content')
@include('admin.partials.header', ['judul'=>'Beli Barang', 'sub'=>'Isi qty + harga — unit & barcode dibuat otomatis per qty', 'ctaHref'=>route('admin.purchases.index'), 'ctaText'=>'Kembali', 'ctaIcon'=>'fa-arrow-left'])
<div class="bg-indigo-50 border border-indigo-200 text-indigo-800 px-4 py-3 rounded-xl mb-4 text-sm max-w-3xl"><i class="fa fa-sack-dollar mr-1"></i>Saldo kas: <strong>Rp {{ number_format($saldo ?? 0,0,',','.') }}</strong> — pilih <strong>Kas</strong> untuk barang maintenance kecil (saldo berkurang), <strong>Perusahaan</strong> untuk belanja besar seperti matras/kulkas (saldo tidak berubah).</div>
<form method="POST" action="{{ route('admin.purchases.store') }}" id="formBeli" class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm max-w-3xl">
@csrf
<div class="mb-5">
<label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-2">Sumber Dana</label>
<div class="grid grid-cols-2 gap-2">
<label class="flex items-center gap-3 border rounded-xl px-4 py-3 cursor-pointer transition has-[:checked]:border-emerald-400 has-[:checked]:bg-emerald-50 border-slate-200">
<input type="radio" name="sumber_dana" value="kas" class="accent-emerald-600" @checked(old('sumber_dana','kas')=='kas')>
<span><span class="block text-sm font-semibold"><i class="fa fa-sack-dollar text-emerald-600 mr-1"></i>Saldo Kas</span><span class="block text-xs text-slate-400">Lampu, barang maintenance — saldo berkurang</span></span>
</label>
<label class="flex items-center gap-3 border rounded-xl px-4 py-3 cursor-pointer transition has-[:checked]:border-sky-400 has-[:checked]:bg-sky-50 border-slate-200">
<input type="radio" name="sumber_dana" value="perusahaan" class="accent-sky-600" @checked(old('sumber_dana')=='perusahaan')>
<span><span class="block text-sm font-semibold"><i class="fa fa-building text-sky-600 mr-1"></i>Uang Perusahaan</span><span class="block text-xs text-slate-400">Matras, kulkas — saldo tidak berubah</span></span>
</label>
</div>
@error('items')<p class="text-xs text-rose-600 mt-2"><i class="fa fa-circle-exclamation mr-1"></i>{{ $message }}</p>@enderror
</div>
<div class="grid md:grid-cols-2 gap-4 mb-5">
<div><label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">Tanggal</label><input type="date" name="tanggal" value="{{ date('Y-m-d') }}" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 outline-none" required></div>
<div class="relative" id="supplierBox">
<label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">Supplier <span class="text-slate-400 normal-case tracking-normal font-normal">(boleh dikosongkan)</span></label>
<input type="hidden" name="supplier_id" id="supplierId" value="{{ old('supplier_id') }}">
<div id="supplierChosen" class="hidden items-center justify-between gap-2 w-full border border-emerald-200 bg-emerald-50 rounded-lg px-3 py-2 text-sm">
<span class="flex items-center gap-2 min-w-0"><i class="fa fa-circle-check text-emerald-500 text-xs"></i><span id="supplierChosenNama" class="font-medium text-emerald-800 truncate"></span></span>
<button type="button" id="supplierClear" class="text-emerald-600 hover:text-rose-600 transition shrink-0" title="Ganti supplier"><i class="fa fa-xmark"></i></button>
</div>
<div id="supplierInputWrap" class="relative">
<i class="fa fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
<input type="text" id="supplierSearch" autocomplete="off" placeholder="Ketik nama supplier untuk mencari..." class="w-full pl-9 pr-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 outline-none">
<div id="supplierDropdown" class="hidden absolute z-10 left-0 right-0 mt-1 bg-white border border-slate-200 rounded-lg shadow-lg max-h-56 overflow-y-auto"></div>
</div>
<p class="text-xs text-slate-400 mt-1.5"><i class="fa fa-circle-info mr-1"></i>Cari dengan mengetik nama atau telepon supplier.</p>
</div>
</div>
<div class="text-xs font-semibold uppercase tracking-wider text-amber-600 mb-2 flex flex-wrap items-center gap-2">
<span><i class="fa fa-box-open mr-1"></i>Item Dibeli</span>
<span id="itemDipilih" class="text-[11px] px-2 py-0.5 rounded-full bg-slate-100 text-slate-500 border border-slate-200 font-normal normal-case tracking-normal">0 barang dipilih</span>
</div>
<div class="overflow-x-auto rounded-xl border border-slate-200 mt-2">
<table class="w-full text-sm">
<thead><tr class="bg-slate-50 text-left text-xs uppercase tracking-wider text-slate-500 border-b border-slate-200">
<th class="px-3 py-2 w-1/2">Barang</th><th class="px-3 py-2 w-24">Qty</th><th class="px-3 py-2 w-40">Harga Satuan (Rp)</th><th class="px-3 py-2 w-36 text-right">Subtotal</th><th class="px-3 py-2 w-12"></th>
</tr></thead>
<tbody id="itemRows">
@php
$barisAwal = old('items', [['item_id'=>'','qty'=>'','harga'=>'']]);
if (!is_array($barisAwal) || !$barisAwal) $barisAwal = [['item_id'=>'','qty'=>'','harga'=>'']];
@endphp
@foreach($barisAwal as $idx => $b)
@php $bid = (string) ($b['item_id'] ?? ''); $bIt = $bid !== '' ? $items->find($bid) : null; @endphp
<tr class="itemRow border-b last:border-0">
<td class="px-3 py-2 relative barangCell">
<input type="hidden" name="items[{{ $idx }}][item_id]" class="barangId" value="{{ $bid }}">
<input type="text" class="barangInput w-full border border-slate-200 rounded-lg px-3 py-2 text-sm bg-white focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 outline-none" autocomplete="off" placeholder="Ketik nama barang untuk mencari..." value="{{ $bIt ? $bIt->nama.' ('.optional($bIt->category)->nama.')' : '' }}">
<div class="barangDropdown hidden absolute z-20 left-0 right-0 top-full mt-1 bg-white border border-slate-200 rounded-lg shadow-lg max-h-56 overflow-y-auto"></div>
</td>
<td class="px-3 py-2"><input type="number" name="items[{{ $idx }}][qty]" value="{{ $b['qty'] ?? '' }}" min="1" placeholder="1" class="qtyInput w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 outline-none"></td>
<td class="px-3 py-2"><input type="number" name="items[{{ $idx }}][harga]" value="{{ $b['harga'] ?? '' }}" min="1" placeholder="otomatis" class="hargaInput w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 outline-none"></td>
<td class="px-3 py-2 text-right font-semibold text-slate-700 subtotalCell">Rp 0</td>
<td class="px-3 py-2 text-center"><button type="button" class="hapusBaris text-slate-300 hover:text-rose-600 transition" title="Hapus baris"><i class="fa fa-xmark"></i></button></td>
</tr>
@endforeach
</tbody>
<tfoot><tr class="bg-slate-50">
<td colspan="3" class="px-3 py-2 text-right text-xs font-semibold uppercase tracking-wider text-slate-500"><span id="jmlItem">0</span> barang - Total</td>
<td class="px-3 py-2 text-right font-bold text-amber-600" id="grandTotal">Rp 0</td>
<td></td>
</tr></tfoot>
</table>
</div>
<button type="button" id="tambahBaris" class="mt-3 inline-flex items-center gap-2 border border-dashed border-amber-400 text-amber-700 hover:bg-amber-50 px-4 py-2 rounded-lg text-sm font-semibold transition"><i class="fa fa-plus text-xs"></i>Tambah Barang</button>
<div class="flex gap-2 mt-6 pt-4 border-t border-slate-100">
<button class="bg-amber-500 hover:bg-amber-600 text-slate-900 px-5 py-2 rounded-lg text-sm font-semibold shadow-sm transition"><i class="fa fa-cart-plus mr-1 text-xs"></i>Simpan & Buat Barcode</button>
<a href="{{ route('admin.purchases.index') }}" class="px-5 py-2 rounded-lg text-sm border border-slate-200 text-slate-600 hover:bg-slate-50 transition">Batal</a>
</div>
</form>
@endsection
@push('scripts')
<script>
@php $supplierJson = $suppliers->map(fn($s) => ['id'=>$s->id,'nama'=>$s->nama,'telepon'=>$s->telepon ?? '','kontak'=>$s->kontak ?? ''])->values()->toJson(); @endphp
const supplierList = {!! $supplierJson !!};
const el = id => document.getElementById(id);
const supplierId = el('supplierId'), supplierSearch = el('supplierSearch'), supplierDropdown = el('supplierDropdown'),
      supplierChosen = el('supplierChosen'), supplierChosenNama = el('supplierChosenNama'), supplierInputWrap = el('supplierInputWrap');

function renderChosen(id) {
    const s = supplierList.find(x => x.id == id);
    if (s) {
        supplierId.value = s.id;
        supplierChosenNama.textContent = s.nama;
        supplierChosen.classList.remove('hidden'); supplierChosen.classList.add('flex');
        supplierInputWrap.classList.add('hidden');
        supplierDropdown.classList.add('hidden');
    }
}
function clearSupplier() {
    supplierId.value = '';
    supplierChosen.classList.add('hidden'); supplierChosen.classList.remove('flex');
    supplierInputWrap.classList.remove('hidden');
    supplierSearch.value = ''; supplierSearch.focus();
}
el('supplierClear').onclick = clearSupplier;

supplierSearch.addEventListener('input', function () {
    const q = this.value.trim().toLowerCase();
    if (!q) { supplierDropdown.classList.add('hidden'); return; }
    const hasil = supplierList.filter(s =>
        s.nama.toLowerCase().includes(q) || s.telepon.toLowerCase().includes(q) || s.kontak.toLowerCase().includes(q)
    ).slice(0, 8);
    supplierDropdown.innerHTML = hasil.length
        ? hasil.map(s => `<button type="button" data-id="${s.id}" class="w-full text-left px-3 py-2 text-sm hover:bg-amber-50 flex items-center justify-between gap-2"><span class="font-medium truncate">${s.nama}</span><span class="text-xs text-slate-400 truncate">${s.telepon || s.kontak || ''}</span></button>`).join('')
        : '<div class="px-3 py-2 text-sm text-slate-400">Tidak ada supplier cocok — boleh lanjut tanpa supplier.</div>';
    supplierDropdown.classList.remove('hidden');
});
supplierDropdown.addEventListener('click', function (e) {
    const btn = e.target.closest('button[data-id]');
    if (btn) renderChosen(btn.dataset.id);
});
document.addEventListener('click', function (e) {
    if (!el('supplierBox').contains(e.target)) supplierDropdown.classList.add('hidden');
});
// pulihkan pilihan lama setelah validasi gagal
if (supplierId.value) renderChosen(supplierId.value);

// --- Item Dibeli: grid dinamis + pencarian barang dari katalog ---
const itemRowsBox = el('itemRows'), itemDipilih = el('itemDipilih');
@php $barangJson = $items->map(fn($i) => ['id'=>$i->id,'nama'=>$i->nama,'kategori'=>optional($i->category)->nama ?? '-','harga'=>$i->harga_estimasi])->values(); @endphp
const barangList = @json($barangJson);
const fmtRp = v => 'Rp ' + new Intl.NumberFormat('id-ID').format(v || 0);
let rowSeq = Math.max(0, ...Array.from(itemRowsBox.querySelectorAll('.barangId')).map(x => parseInt(x.name.replace(/\D/g,'')) || 0)) + 1;
function hitungBeli() {
    let total = 0, n = 0;
    itemRowsBox.querySelectorAll('.itemRow').forEach(r => {
        const q = parseInt(r.querySelector('.qtyInput').value) || 0;
        const h = parseFloat(r.querySelector('.hargaInput').value) || 0;
        total += q * h;
        if (r.querySelector('.barangId').value !== '' && q > 0) n++;
        r.querySelector('.subtotalCell').textContent = fmtRp(q * h);
    });
    el('grandTotal').textContent = fmtRp(total);
    el('jmlItem').textContent = n;
    itemDipilih.textContent = n + ' barang dipilih';
    itemDipilih.classList.toggle('bg-amber-100', n > 0);
    itemDipilih.classList.toggle('text-amber-700', n > 0);
    itemDipilih.classList.toggle('border-amber-200', n > 0);
    itemDipilih.classList.toggle('bg-slate-100', n === 0);
    itemDipilih.classList.toggle('text-slate-500', n === 0);
}
function tutupDropdown() { document.querySelectorAll('.barangDropdown').forEach(d => d.classList.add('hidden')); }
function renderDropdown(cell, q) {
    const dd = cell.querySelector('.barangDropdown');
    q = (q || '').trim().toLowerCase();
    const hasil = barangList.filter(b => b.nama.toLowerCase().includes(q) || b.kategori.toLowerCase().includes(q)).slice(0, 8);
    dd.innerHTML = hasil.length
        ? hasil.map(b => '<button type="button" data-id="' + b.id + '" class="w-full text-left px-3 py-2 text-sm hover:bg-amber-50 flex items-center justify-between gap-2"><span class="font-medium truncate">' + b.nama + ' <span class="text-xs text-slate-400">(' + b.kategori + ')</span></span><span class="text-xs text-slate-400">' + fmtRp(b.harga) + '</span></button>').join('')
        : '<div class="px-3 py-2 text-sm text-slate-400">Tidak ada barang yang cocok.</div>';
    const r = cell.querySelector('.barangInput').getBoundingClientRect();
    dd.style.position = 'fixed';
    dd.style.left = r.left + 'px';
    dd.style.top = (r.bottom + 4) + 'px';
    dd.style.width = r.width + 'px';
    tutupDropdown();
    dd.classList.remove('hidden');
}
function pilihBarang(cell, b) {
    const idEl = cell.querySelector('.barangId');
    const kembar = Array.from(itemRowsBox.querySelectorAll('.barangId')).some(x => x !== idEl && x.value === String(b.id));
    if (kembar) { alert('Barang "' + b.nama + '" sudah dipilih di baris lain - ubah qty di baris itu saja.'); return; }
    idEl.value = b.id;
    cell.querySelector('.barangInput').value = b.nama + ' (' + b.kategori + ')';
    const hInput = cell.closest('.itemRow').querySelector('.hargaInput');
    if (b.harga && !hInput.value) hInput.value = b.harga;
    tutupDropdown();
    hitungBeli();
}
el('tambahBaris').onclick = () => {
    const i = rowSeq++;
    const tr = document.createElement('tr');
    tr.className = 'itemRow border-b last:border-0';
    tr.innerHTML = '<td class="px-3 py-2 relative barangCell">'
        + '<input type="hidden" name="items[' + i + '][item_id]" class="barangId" value="">'
        + '<input type="text" class="barangInput w-full border border-slate-200 rounded-lg px-3 py-2 text-sm bg-white focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 outline-none" autocomplete="off" placeholder="Ketik nama barang untuk mencari..." value="">'
        + '<div class="barangDropdown hidden absolute z-20 left-0 right-0 top-full mt-1 bg-white border border-slate-200 rounded-lg shadow-lg max-h-56 overflow-y-auto"></div></td>'
        + '<td class="px-3 py-2"><input type="number" name="items[' + i + '][qty]" min="1" placeholder="1" class="qtyInput w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 outline-none"></td>'
        + '<td class="px-3 py-2"><input type="number" name="items[' + i + '][harga]" min="1" placeholder="otomatis" class="hargaInput w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 outline-none"></td>'
        + '<td class="px-3 py-2 text-right font-semibold text-slate-700 subtotalCell">Rp 0</td>'
        + '<td class="px-3 py-2 text-center"><button type="button" class="hapusBaris text-slate-300 hover:text-rose-600 transition" title="Hapus baris"><i class="fa fa-xmark"></i></button></td>';
    itemRowsBox.appendChild(tr);
    tr.querySelector('.barangInput').focus();
    hitungBeli();
};
itemRowsBox.addEventListener('input', e => {
    if (!e.target.classList.contains('barangInput')) { hitungBeli(); return; }
    const cell = e.target.closest('.barangCell');
    cell.querySelector('.barangId').value = '';
    renderDropdown(cell, e.target.value);
    hitungBeli();
});
itemRowsBox.addEventListener('focusin', e => {
    if (e.target.classList.contains('barangInput')) renderDropdown(e.target.closest('.barangCell'), e.target.value);
});
itemRowsBox.addEventListener('click', e => {
    const op = e.target.closest('.barangDropdown button[data-id]');
    if (op) { const b = barangList.find(x => x.id === parseInt(op.dataset.id)); if (b) pilihBarang(op.closest('.barangCell'), b); return; }
    const b = e.target.closest('.hapusBaris');
    if (!b) return;
    if (itemRowsBox.querySelectorAll('.itemRow').length <= 1) { itemRowsBox.querySelectorAll('.itemRow .barangId, .itemRow .barangInput, .itemRow input[type=number]').forEach(i => i.value = ''); tutupDropdown(); hitungBeli(); return; }
    b.closest('.itemRow').remove(); hitungBeli();
});
document.addEventListener('click', e => { if (!e.target.closest('.barangCell')) tutupDropdown(); });
window.addEventListener('scroll', tutupDropdown, true);
el('formBeli').addEventListener('submit', e => {
    const terisi = [], belumPilih = [], tanpaQty = [];
    itemRowsBox.querySelectorAll('.itemRow').forEach((r, n) => {
        const id = r.querySelector('.barangId').value, txt = r.querySelector('.barangInput').value.trim();
        const q = parseInt(r.querySelector('.qtyInput').value) || 0;
        if (id === '' && txt === '' && !q) return;
        if (id === '') belumPilih.push(n + 1); else if (!q) tanpaQty.push(n + 1); else terisi.push(1);
    });
    if (belumPilih.length) { e.preventDefault(); alert('Baris ' + belumPilih.join(', ') + ': barang belum dipilih dari daftar pencarian - klik salah satu hasil.'); return; }
    if (tanpaQty.length) { e.preventDefault(); alert('Baris ' + tanpaQty.join(', ') + ': qty minimal 1.'); return; }
    if (!terisi.length) { e.preventDefault(); alert('Pilih minimal 1 barang.'); }
});
hitungBeli();
</script>
@endpush
