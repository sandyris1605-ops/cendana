@extends('layouts.admin')
@section('title','Pembelian')
@section('content')
@include('admin.partials.header', ['judul'=>'Pembelian Barang', 'sub'=>'Setiap pembelian otomatis membuat unit + barcode baru', 'ctaHref'=>route('admin.purchases.create'), 'ctaText'=>'Beli Barang', 'ctaIcon'=>'fa-cart-plus'])
@include('admin.partials.search', ['placeholder'=>'Cari nomor PO / nama supplier...', 'extra'=>'<select name="sumber_dana" class="border border-slate-200 rounded-lg px-3 py-2 text-sm bg-white outline-none focus:border-amber-500" onchange="this.form.submit()"><option value="">Semua sumber dana</option><option value="kas" '.(request('sumber_dana')=='kas'?'selected':'').'>Saldo Kas</option><option value="perusahaan" '.(request('sumber_dana')=='perusahaan'?'selected':'').'>Perusahaan</option></select>'])
<div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-x-auto">
<table class="w-full text-sm">
<thead><tr class="bg-slate-50 text-left text-xs uppercase tracking-wider text-slate-500 border-b border-slate-200">
<th class="px-4 py-3">Nomor</th><th class="px-4 py-3">Supplier</th><th class="px-4 py-3">Tanggal</th><th class="px-4 py-3">Sumber Dana</th><th class="px-4 py-3">Total</th><th class="px-4 py-3 text-right">Aksi</th>
</tr></thead>
<tbody class="divide-y divide-slate-100">
@forelse($purchases as $p)
<tr class="hover:bg-slate-50 transition">
<td class="px-4 py-3 font-mono text-xs font-semibold">{{ $p->nomor }}</td>
<td class="px-4 py-3">{{ optional($p->supplier)->nama ?? '-' }}</td>
<td class="px-4 py-3">{{ \Illuminate\Support\Carbon::parse($p->tanggal)->translatedFormat('d M Y') }}</td>
<td class="px-4 py-3"><span class="text-[11px] px-2 py-0.5 rounded-full border {{ ($p->sumber_dana ?? 'kas')=='kas' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-sky-50 text-sky-700 border-sky-200' }}"><i class="fa {{ ($p->sumber_dana ?? 'kas')=='kas' ? 'fa-sack-dollar' : 'fa-building' }} mr-1"></i>{{ ($p->sumber_dana ?? 'kas')=='kas' ? 'Saldo Kas' : 'Perusahaan' }}</span></td>
<td class="px-4 py-3 font-semibold">Rp {{ number_format($p->total,0,',','.') }}</td>
<td class="px-4 py-3 text-right whitespace-nowrap">
@php $menuPO = [
['tipe'=>'link','label'=>'Detail','ikon'=>'fa-eye','url'=>route('admin.purchases.show',$p)],
['tipe'=>'hapus','label'=>'Hapus','ikon'=>'fa-trash','url'=>route('admin.purchases.destroy',$p),'pesan'=>'Hapus PO '.$p->nomor.' (Rp '.number_format($p->total,0,',','.').")?\n\nItem, unit barcode, penempatan & riwayat penyusutannya ikut terhapus PERMANEN."],
]; @endphp
@include('admin.partials.aksi',['menu'=>$menuPO])
</td>
</tr>
@empty
<tr><td colspan="6" class="px-4 py-10 text-center text-slate-400"><i class="fa fa-cart-shopping text-2xl mb-2 block"></i>Tidak ada pembelian yang cocok.</td></tr>
@endforelse
</tbody>
</table>
</div>
@include('admin.partials.pager', ['paginator'=>$purchases])
@endsection
