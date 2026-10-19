@extends('layouts.admin')
@section('title','Detail Pembelian')
@section('content')
@include('admin.partials.header', ['judul'=>$purchase->nomor, 'sub'=>(optional($purchase->supplier)->nama ?? '-').' • Sumber dana: '.(($purchase->sumber_dana ?? 'kas')=='kas' ? 'Saldo Kas' : 'Uang Perusahaan') /* . • tanggal */, 'ctaSlot'=>'<span class="inline-flex items-center px-3 py-2 rounded-lg text-sm bg-white border border-slate-200 text-slate-600"><i class="fa fa-calendar mr-1 text-xs"></i>'.\Illuminate\Support\Carbon::parse($purchase->tanggal)->translatedFormat('d M Y').'</span><form method="POST" action="'.route('admin.purchases.destroy',$purchase).'" class="inline" onsubmit="return konfirmasiHapus(\'Hapus PO '.$purchase->nomor.'?\n\nItem, unit barcode, penempatan & riwayat penyusutannya ikut terhapus PERMANEN.\', this)">'.csrf_field().method_field('DELETE').'<button class="inline-flex items-center gap-2 bg-white border border-rose-200 hover:bg-rose-500 hover:text-white hover:border-rose-500 text-rose-600 px-4 py-2 rounded-lg text-sm shadow-sm transition"><i class="fa fa-trash text-xs"></i>Hapus PO</button></form>'])
<div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-x-auto">
<table class="w-full text-sm">
<thead><tr class="bg-slate-50 text-left text-xs uppercase tracking-wider text-slate-500 border-b border-slate-200">
<th class="px-4 py-3">Barang</th><th class="px-4 py-3">Qty</th><th class="px-4 py-3">Harga Satuan</th><th class="px-4 py-3">Subtotal</th>
</tr></thead>
<tbody class="divide-y divide-slate-100">
@php $totQty = 0; @endphp
@forelse($purchase->items as $i)
@php $totQty += $i->qty; @endphp
<tr class="hover:bg-slate-50 transition">
<td class="px-4 py-3 font-medium">{{ optional($i->item)->nama }}</td>
<td class="px-4 py-3">{{ $i->qty }} unit</td>
<td class="px-4 py-3 text-slate-500">Rp {{ number_format($i->subtotal / max(1,$i->qty),0,',','.') }}</td>
<td class="px-4 py-3 font-semibold">Rp {{ number_format($i->subtotal,0,',','.') }}</td>
</tr>
@empty
<tr><td colspan="4" class="px-4 py-10 text-center text-slate-400">Tidak ada item.</td></tr>
@endforelse
</tbody>
<tfoot><tr class="bg-slate-50 border-t border-slate-200">
<td class="px-4 py-3 font-semibold" colspan="2">Total {{ $totQty }} unit</td>
<td class="px-4 py-3"></td>
<td class="px-4 py-3 font-bold text-amber-600">Rp {{ number_format($purchase->total,0,',','.') }}</td>
</tr></tfoot>
</table>
</div>
@endsection
