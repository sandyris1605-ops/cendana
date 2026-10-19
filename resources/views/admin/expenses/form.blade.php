@extends('layouts.admin')
@section('title','Form Pengeluaran')
@section('content')
@include('admin.partials.header', ['judul'=>($expense->exists ? 'Edit' : 'Catat').' Pengeluaran', 'sub'=>'Ketik uraian + qty + nominal per baris — 1 transaksi bisa banyak item', 'ctaHref'=>route('admin.expenses.index'), 'ctaText'=>'Kembali', 'ctaIcon'=>'fa-arrow-left'])
<div class="bg-indigo-50 border border-indigo-200 text-indigo-800 px-4 py-3 rounded-xl mb-4 text-sm max-w-3xl"><i class="fa fa-sack-dollar mr-1"></i>Saldo kas tersedia: <strong>Rp {{ number_format($saldo ?? 0,0,',','.') }}</strong> — <a href="{{ route('admin.saldo-kas.index') }}" class="underline font-semibold">top up di Saldo Kas</a></div>
<form method="POST" action="{{ $expense->exists ? route('admin.expenses.update',$expense) : route('admin.expenses.store') }}" class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm max-w-3xl">
@csrf @if($expense->exists) @method('PUT') @endif
<div class="mb-5 max-w-xs"><label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">Tanggal</label><input type="date" name="tanggal" value="{{ old('tanggal',$expense->tanggal ?? date('Y-m-d')) }}" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 outline-none"></div>
@php
$barisAwal = old('items');
if (!$barisAwal && $expense->exists && $expense->relationLoaded('items') && $expense->items->count()) {
    $barisAwal = $expense->items->map(fn($i)=>['uraian'=>$i->uraian,'qty'=>$i->qty,'harga'=>$i->harga_satuan])->all();
}
if (!$barisAwal) $barisAwal = [['uraian'=>'','qty'=>'','harga'=>'']];
@endphp
<div class="overflow-x-auto rounded-xl border border-slate-200">
<table class="w-full text-sm">
<thead><tr class="bg-slate-50 text-left text-xs uppercase tracking-wider text-slate-500 border-b border-slate-200">
<th class="px-3 py-2">Uraian</th><th class="px-3 py-2 w-20">Qty</th><th class="px-3 py-2 w-40">Nominal (Rp)</th><th class="px-3 py-2 w-36 text-right">Subtotal</th><th class="px-3 py-2 w-12"></th>
</tr></thead>
<tbody id="itemRows">
@foreach($barisAwal as $idx => $b)
<tr class="itemRow border-b last:border-0">
<td class="px-3 py-2"><input name="items[{{ $idx }}][uraian]" value="{{ $b['uraian'] ?? '' }}" placeholder="mis. Beras 5 kg" class="uraianInput w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 outline-none"></td>
<td class="px-3 py-2"><input type="number" name="items[{{ $idx }}][qty]" value="{{ $b['qty'] ?? '' }}" min="1" placeholder="1" class="qtyInput w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 outline-none"></td>
<td class="px-3 py-2"><input type="number" name="items[{{ $idx }}][harga]" value="{{ $b['harga'] ?? '' }}" min="1" placeholder="50000" class="hargaInput w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 outline-none"></td>
<td class="px-3 py-2 text-right font-semibold text-slate-700 subtotalCell">Rp 0</td>
<td class="px-3 py-2 text-center"><button type="button" class="hapusBaris text-slate-300 hover:text-rose-600 transition" title="Hapus baris"><i class="fa fa-xmark"></i></button></td>
</tr>
@endforeach
</tbody>
<tfoot><tr class="bg-slate-50">
<td colspan="3" class="px-3 py-2 text-right text-xs font-semibold uppercase tracking-wider text-slate-500"><span id="jmlItem">0</span> item • Total</td>
<td class="px-3 py-2 text-right font-bold text-rose-600" id="grandTotal">Rp 0</td>
<td></td>
</tr></tfoot>
</table>
</div>
<button type="button" id="tambahBaris" class="mt-3 inline-flex items-center gap-2 border border-dashed border-amber-400 text-amber-700 hover:bg-amber-50 px-4 py-2 rounded-lg text-sm font-semibold transition"><i class="fa fa-plus text-xs"></i>Tambah Item</button>
@error('items')<p class="text-xs text-rose-600 mt-2"><i class="fa fa-circle-exclamation mr-1"></i>{{ $message }}</p>@enderror
<div class="flex gap-2 mt-6 pt-4 border-t border-slate-100">
<button class="bg-amber-500 hover:bg-amber-600 text-slate-900 px-5 py-2 rounded-lg text-sm font-semibold shadow-sm transition"><i class="fa fa-check mr-1 text-xs"></i>Simpan</button>
@if($expense->exists)
<button type="submit" form="formHapusPengeluaran" class="px-5 py-2 rounded-lg text-sm border border-rose-200 text-rose-600 hover:bg-rose-50 transition" onclick="return konfirmasiHapus('Hapus pengeluaran {{ $expense->nomor }}?\n\nSaldo kas otomatis bertambah kembali Rp {{ number_format($expense->jumlah,0,',','.') }}.', this)"><i class="fa fa-trash mr-1"></i>Hapus</button>
@endif
<a href="{{ route('admin.expenses.index') }}" class="px-5 py-2 rounded-lg text-sm border border-slate-200 text-slate-600 hover:bg-slate-50 transition">Batal</a>
</div>
</form>
@if($expense->exists)
<form method="POST" action="{{ route('admin.expenses.destroy',$expense) }}" id="formHapusPengeluaran">@csrf @method('DELETE')</form>
@endif
@push('scripts')
<script>
const tbody = document.getElementById('itemRows');
const fmtRp = v => 'Rp ' + new Intl.NumberFormat('id-ID').format(v || 0);
let rowSeq = Math.max(0, ...Array.from(tbody.querySelectorAll('.qtyInput')).map(x => parseInt((x.name.match(/items\[(\d+)\]/) || [])[1]) || 0)) + 1;
function hitung() {
    let total = 0, n = 0;
    tbody.querySelectorAll('.itemRow').forEach(r => {
        const q = parseInt(r.querySelector('.qtyInput').value) || 0;
        const h = parseFloat(r.querySelector('.hargaInput').value) || 0;
        total += q * h;
        if ((r.querySelector('.uraianInput').value || '').trim() !== '' && q > 0 && h > 0) n++;
        r.querySelector('.subtotalCell').textContent = fmtRp(q * h);
    });
    document.getElementById('grandTotal').textContent = fmtRp(total);
    document.getElementById('jmlItem').textContent = n;
}
document.getElementById('tambahBaris').onclick = () => {
    const i = rowSeq++;
    const tr = document.createElement('tr');
    tr.className = 'itemRow border-b last:border-0';
    tr.innerHTML = '<td class="px-3 py-2"><input name="items[' + i + '][uraian]" placeholder="mis. Beras 5 kg" class="uraianInput w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 outline-none"></td>'
        + '<td class="px-3 py-2"><input type="number" name="items[' + i + '][qty]" min="1" placeholder="1" class="qtyInput w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 outline-none"></td>'
        + '<td class="px-3 py-2"><input type="number" name="items[' + i + '][harga]" min="1" placeholder="50000" class="hargaInput w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 outline-none"></td>'
        + '<td class="px-3 py-2 text-right font-semibold text-slate-700 subtotalCell">Rp 0</td>'
        + '<td class="px-3 py-2 text-center"><button type="button" class="hapusBaris text-slate-300 hover:text-rose-600 transition" title="Hapus baris"><i class="fa fa-xmark"></i></button></td>';
    tbody.appendChild(tr);
    tr.querySelector('.uraianInput').focus();
    hitung();
};
tbody.addEventListener('input', hitung);
tbody.addEventListener('click', e => {
    const b = e.target.closest('.hapusBaris');
    if (!b) return;
    if (tbody.querySelectorAll('.itemRow').length <= 1) { tbody.querySelectorAll('.itemRow input').forEach(i => i.value = ''); hitung(); return; }
    b.closest('.itemRow').remove(); hitung();
});
hitung();
</script>
@endpush
@endsection
