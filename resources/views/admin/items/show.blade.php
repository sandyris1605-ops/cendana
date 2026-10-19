@extends('layouts.admin')
@section('title','Detail Barang')
@section('content')
@include('admin.partials.header', ['judul'=>$item->nama, 'sub'=>($item->category->nama ?? '-').' • Umur pakai '.$item->umur_pakai_bulan.' bulan • '.$item->units->count().' unit terdaftar', 'ctaSlot'=>'<a href="'.route('admin.items.edit',$item).'" class="inline-flex items-center gap-2 bg-amber-500 hover:bg-amber-600 text-slate-900 px-4 py-2 rounded-lg text-sm font-semibold shadow-sm transition"><i class="fa fa-pen text-xs"></i>Edit</a><form method="POST" action="'.route('admin.items.destroy',$item).'" class="inline" onsubmit="return konfirmasiHapus(\'Hapus barang '.e($item->nama, ENT_QUOTES).'? Barang hanya bisa dihapus jika belum punya unit / riwayat pembelian.\', this)">'.csrf_field().method_field('DELETE').'<button class="inline-flex items-center gap-2 bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 px-4 py-2 rounded-lg text-sm font-semibold shadow-sm transition"><i class="fa fa-trash text-xs"></i>Hapus</button></form>'])
<div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-x-auto">
<table class="w-full text-sm">
<thead><tr class="bg-slate-50 text-left text-xs uppercase tracking-wider text-slate-500 border-b border-slate-200">
<th class="px-4 py-3">Barcode</th><th class="px-4 py-3">Lokasi</th><th class="px-4 py-3">Tgl Beli</th><th class="px-4 py-3">Nilai Buku</th>
</tr></thead>
<tbody class="divide-y divide-slate-100">
@forelse($item->units as $u)
<tr class="hover:bg-slate-50 transition">
<td class="px-4 py-3"><a href="{{ route('admin.assets.show',$u) }}" class="font-mono text-xs font-semibold text-indigo-600 hover:text-amber-600 transition">{{ $u->kode_barcode }}</a></td>
<td class="px-4 py-3"><span class="text-[11px] px-2 py-0.5 rounded-full bg-slate-100 text-slate-600 border border-slate-200">{{ optional(optional($u->activePlacement)->room)->nomor ?? 'Gudang' }}</span></td>
<td class="px-4 py-3 text-slate-500">{{ \Illuminate\Support\Carbon::parse($u->tanggal_beli)->translatedFormat('d M Y') }}</td>
<td class="px-4 py-3 font-semibold">Rp {{ number_format($u->nilai_buku,0,',','.') }}</td>
</tr>
@empty
<tr><td colspan="4" class="px-4 py-10 text-center text-slate-400">Belum ada unit. Beli barang di menu Pembelian untuk membuat unit + barcode.</td></tr>
@endforelse
</tbody>
</table>
</div>
@endsection
