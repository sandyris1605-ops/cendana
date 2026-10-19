@extends('layouts.admin')
@section('title','Riwayat Supplier')
@section('content')
@php
$jmlBeli = (int) ($supplier->purchases_count ?? $supplier->purchases->count());
$ctaSupplier = '<a href="'.route('admin.suppliers.edit',$supplier).'" class="inline-flex items-center gap-2 bg-amber-500 hover:bg-amber-600 text-slate-900 px-4 py-2 rounded-lg text-sm font-semibold shadow-sm transition"><i class="fa fa-pen text-xs"></i>Edit</a>';
if($jmlBeli == 0) $ctaSupplier .= '<form method="POST" action="'.route('admin.suppliers.destroy',$supplier).'" class="inline" onsubmit="return konfirmasiHapus(\'Hapus supplier ini?\', this)"><input type="hidden" name="_token" value="'.csrf_token().'"><input type="hidden" name="_method" value="DELETE"><button class="inline-flex items-center gap-2 bg-rose-500 hover:bg-rose-600 text-white px-4 py-2 rounded-lg text-sm font-semibold shadow-sm transition"><i class="fa fa-trash text-xs"></i>Hapus</button></form>';
@endphp
@include('admin.partials.header', ['judul'=>$supplier->nama, 'sub'=>($supplier->kontak ?? '-').' • '.($supplier->telepon ?? '-').' • '.$jmlBeli.'x pembelian', 'ctaSlot'=>'<div class="flex gap-2">'.$ctaSupplier.'</div>'])
<div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 mb-4">
<div class="grid grid-cols-2 md:grid-cols-4 gap-x-4 gap-y-3">
  <div><div class="text-[11px] uppercase tracking-wider text-slate-400 font-semibold mb-0.5">Kontak / PIC</div><div class="text-sm font-medium">{{ $supplier->kontak ?? '-' }}</div></div>
  <div><div class="text-[11px] uppercase tracking-wider text-slate-400 font-semibold mb-0.5">Telepon</div><div class="text-sm font-medium">{{ $supplier->telepon ?? '-' }}</div></div>
  <div><div class="text-[11px] uppercase tracking-wider text-slate-400 font-semibold mb-0.5">No. Pajak (NPWP)</div><div class="text-sm font-medium">{{ $supplier->no_pajak ?? '-' }}</div></div>
  <div><div class="text-[11px] uppercase tracking-wider text-slate-400 font-semibold mb-0.5">Alamat</div><div class="text-sm font-medium">{{ $supplier->alamat ?? '-' }}</div></div>
</div>
</div>
<div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-x-auto">
<table class="w-full text-sm">
<thead><tr class="bg-slate-50 text-left text-xs uppercase tracking-wider text-slate-500 border-b border-slate-200">
<th class="px-4 py-3">Nomor PO</th><th class="px-4 py-3">Tanggal</th><th class="px-4 py-3">Item</th><th class="px-4 py-3">Total</th>
</tr></thead>
<tbody class="divide-y divide-slate-100">
@forelse($supplier->purchases as $p)
<tr class="hover:bg-amber-50/40 transition">
<td class="px-4 py-3 font-mono text-xs font-semibold"><a href="{{ route('admin.purchases.show',$p) }}" class="text-indigo-600 hover:text-amber-600 transition">{{ $p->nomor }}</a></td>
<td class="px-4 py-3 text-slate-500">{{ \Illuminate\Support\Carbon::parse($p->tanggal)->translatedFormat('d M Y') }}</td>
<td class="px-4 py-3">{{ $p->items->sum('qty') }} unit</td>
<td class="px-4 py-3 font-semibold">Rp {{ number_format($p->total,0,',','.') }}</td>
</tr>
@empty
<tr><td colspan="4" class="px-4 py-10 text-center text-slate-400">Belum ada riwayat pembelian.</td></tr>
@endforelse
</tbody>
</table>
</div>
@endsection
