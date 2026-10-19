@extends('layouts.admin')
@section('title','Stok Gudang')
@section('content')
@include('admin.partials.header', ['judul'=>'Stok Gudang', 'sub'=>'Unit dari pembelian yang masih di gudang — scan barcode, pilih kamar, transfer', 'ctaSlot'=>'<a href="'.route('admin.barcode.cetak').'" class="inline-flex items-center gap-2 bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 px-4 py-2 rounded-lg text-sm font-semibold shadow-sm transition"><i class="fa fa-print text-xs"></i>Cetak Barcode</a><button type="button" onclick="openTransferModal()" class="inline-flex items-center gap-2 bg-amber-500 hover:bg-amber-600 text-slate-900 px-4 py-2 rounded-lg text-sm font-semibold shadow-sm transition"><i class="fa fa-arrow-right-arrow-left text-xs"></i>Transfer Manual</button>'])
@include('admin.partials.search', ['placeholder'=>'Cari barcode / nama barang...'])
<div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-x-auto">
<table class="w-full text-sm">
<thead><tr class="bg-slate-50 text-left text-xs uppercase tracking-wider text-slate-500 border-b border-slate-200">
<th class="px-4 py-3">Barcode</th><th class="px-4 py-3">Barang</th><th class="px-4 py-3">PO</th><th class="px-4 py-3">Tgl Beli</th><th class="px-4 py-3">Nilai Buku</th><th class="px-4 py-3">Kondisi</th><th class="px-4 py-3 text-right">Aksi</th>
</tr></thead>
<tbody class="divide-y divide-slate-100">
@forelse($units as $u)
<tr class="hover:bg-slate-50 transition" data-barcode="{{ $u->kode_barcode }}">
<td class="px-4 py-3"><a href="{{ route('admin.assets.show',$u) }}" class="font-mono text-xs font-semibold text-indigo-600 hover:text-amber-600 transition">{{ $u->kode_barcode }}</a></td>
<td class="px-4 py-3">{{ optional($u->item)->nama }}<div class="text-[11px] text-slate-400">{{ optional(optional($u->item)->category)->nama ?? '-' }}</div></td>
<td class="px-4 py-3 font-mono text-xs">{{ optional($u->purchase)->nomor ?? '-' }}</td>
<td class="px-4 py-3 text-slate-500">{{ \Illuminate\Support\Carbon::parse($u->tanggal_beli)->translatedFormat('d M Y') }}</td>
<td class="px-4 py-3 font-semibold">Rp {{ number_format($u->nilai_buku,0,',','.') }}</td>
<td class="px-4 py-3"><span class="text-[11px] px-2 py-0.5 rounded-full border {{ $u->kondisi=='perlu_diganti' ? 'bg-rose-500 text-white border-rose-500' : ($u->kondisi=='baik' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-amber-50 text-amber-700 border-amber-200') }}">{{ $u->kondisi=='perlu_diganti' ? 'Perlu Diganti' : $u->kondisi }}</span></td>
<td class="px-4 py-3 text-right whitespace-nowrap"><button type="button" class="jsTransfer text-slate-400 hover:text-amber-600 transition" data-barcode="{{ $u->kode_barcode }}"><i class="fa fa-arrow-right-arrow-left mr-1"></i>Transfer</button></td>
</tr>
@empty
<tr><td colspan="7" class="px-4 py-10 text-center text-slate-400"><i class="fa fa-warehouse text-2xl mb-2 block"></i>Tidak ada unit di gudang. Barang dari pembelian akan muncul di sini.</td></tr>
@endforelse
</tbody>
</table>
</div>

@include('admin.partials.pager', ['paginator'=>$units])

{{-- ===== Modal Transfer ke Kamar ===== --}}
<div id="modalTransfer" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
<div class="absolute inset-0 bg-slate-900/60" onclick="closeTransferModal()"></div>
<div class="relative bg-white rounded-2xl shadow-xl w-full max-w-lg max-h-[90vh] overflow-y-auto p-6">
<div class="flex items-center justify-between mb-4">
<div>
<div class="font-bold text-lg">Transfer ke Kamar</div>
<div class="text-xs text-slate-400">Pindahkan unit dari gudang ke kamar tujuan</div>
</div>
<button type="button" onclick="closeTransferModal()" class="w-8 h-8 inline-flex items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition"><i class="fa fa-xmark"></i></button>
</div>
<form id="transferForm" method="POST" action="{{ route('admin.gudang.transfer') }}">
@csrf
<div class="mb-3">
<label class="block text-[11px] font-semibold uppercase tracking-wider text-slate-500 mb-1">Barcode unit</label>
<div class="relative">
<i class="fa fa-barcode absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
<input type="text" name="kode_barcode" id="transferBarcode" placeholder="Scan / ketik barcode..." autocomplete="off" class="w-full pl-9 pr-3 py-2 border border-slate-200 rounded-lg text-sm font-mono outline-none focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500">
</div>
@error('kode_barcode')<p class="text-xs text-rose-600 mt-1.5"><i class="fa fa-circle-exclamation mr-1"></i>{{ $message }}</p>@enderror
</div>
<div class="relative mb-3">
<i class="fa fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
<input type="text" id="transferSearch" placeholder="Cari kamar... (mis. 12, A, terisi)" autocomplete="off" class="w-full pl-9 pr-3 py-2 border border-slate-200 rounded-lg text-sm outline-none focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500">
</div>
<div id="transferRoomList" class="border border-slate-200 rounded-xl overflow-y-auto divide-y divide-slate-100" style="max-height:16rem">
@foreach($rooms as $rm)
<label class="transfer-room flex items-center gap-3 px-3 py-2.5 cursor-pointer hover:bg-amber-50 transition" data-search="kamar {{ $rm->nomor }} {{ $rm->status }}">
<input type="radio" name="room_id" value="{{ $rm->id }}" class="accent-amber-500 shrink-0" @checked(old('room_id')==$rm->id)>
<span class="font-semibold text-sm">Kamar {{ $rm->nomor }}</span>
<span class="ml-auto text-[11px] px-2 py-0.5 rounded-full border font-medium {{ $rm->status=='terisi' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : ($rm->status=='tersedia' ? 'bg-indigo-50 text-indigo-700 border-indigo-200' : 'bg-amber-50 text-amber-700 border-amber-200') }}">{{ ucfirst($rm->status) }}</span>
</label>
@endforeach
</div>
<p id="transferEmpty" class="hidden text-center text-sm text-slate-400 py-6"><i class="fa fa-bed text-2xl mb-2 block"></i>Tidak ada kamar yang cocok.</p>
<div class="flex gap-2 mt-5 pt-4 border-t border-slate-100">
<button type="submit" id="transferSubmit" class="bg-amber-500 hover:bg-amber-600 disabled:opacity-50 disabled:cursor-not-allowed text-slate-900 px-5 py-2 rounded-lg text-sm font-semibold shadow-sm transition"><i class="fa fa-truck mr-1 text-xs"></i>Transfer</button>
<button type="button" onclick="closeTransferModal()" class="px-5 py-2 rounded-lg text-sm border border-slate-200 text-slate-600 hover:bg-slate-50 transition">Batal</button>
</div>
</form>
</div>
</div>
@endsection
@push('scripts')
<script>
function openTransferModal(barcode){
    var inputBarcode = document.getElementById('transferBarcode');
    var modal = document.getElementById('modalTransfer');
    if (!inputBarcode || !modal) return;
    inputBarcode.value = barcode || '';
    inputBarcode.readOnly = !!barcode;
    filterTransferRooms('');
    perbaruiTransferSubmit();
    modal.classList.remove('hidden');
    modal.style.display = 'flex';
    document.body.classList.add('overflow-hidden');
    setTimeout(function(){ (barcode ? document.getElementById('transferSearch') : inputBarcode).focus(); }, 50);
}
function perbaruiTransferSubmit(){
    var dipilih = document.querySelector('#transferRoomList input[name=room_id]:checked');
    var submit = document.getElementById('transferSubmit');
    var barcodeEl = document.getElementById('transferBarcode');
    if (!submit || !barcodeEl) return;
    submit.disabled = !((barcodeEl.value || '').trim() && dipilih);
}
function filterTransferRooms(query){
    var q = (query || '').toLowerCase().trim();
    var labels = document.querySelectorAll('#transferRoomList .transfer-room');
    labels.forEach(function (label){
        var text = (label.getAttribute('data-search') || '').toLowerCase();
        var isMatch = q === '' || text.indexOf(q) !== -1;
        label.classList.toggle('hidden', !isMatch);
        label.classList.toggle('bg-amber-50', q !== '' && isMatch);
    });
}
function closeTransferModal(){
    var modal = document.getElementById('modalTransfer');
    if (modal) { modal.classList.add('hidden'); modal.style.display = 'none'; }
    document.body.classList.remove('overflow-hidden');
}
window.openTransferModal = openTransferModal;
window.closeTransferModal = closeTransferModal;
window.filterTransferRooms = filterTransferRooms;
window.perbaruiTransferSubmit = perbaruiTransferSubmit;
document.addEventListener('DOMContentLoaded', function(){
    var inputBarcode = document.getElementById('transferBarcode');
    if (inputBarcode) inputBarcode.addEventListener('input', function () { perbaruiTransferSubmit(); });
    var search = document.getElementById('transferSearch');
    if (search) search.addEventListener('input', function () { filterTransferRooms(this.value); });
    document.querySelectorAll('#transferRoomList input[name=room_id]').forEach(function (r) {
        r.addEventListener('change', function () { perbaruiTransferSubmit(); });
    });
    document.querySelectorAll('.jsTransfer').forEach(function (b) { b.onclick = function () { openTransferModal(b.dataset.barcode); }; });
    document.addEventListener('keydown', function (e){ if (e.key === 'Escape') closeTransferModal(); });
    perbaruiTransferSubmit();
    @if($errors->any())
    openTransferModal(@json(old('kode_barcode', '')));
    @endif
});
</script>
@endpush
