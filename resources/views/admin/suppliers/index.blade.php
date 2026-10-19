@extends('layouts.admin')
@section('title','Supplier')
@section('content')
@include('admin.partials.header', ['judul'=>'Supplier', 'sub'=>'Mitra pemasok barang & jasa', 'ctaSlot'=>'<button type="button" onclick="bukaTambah()" class="inline-flex items-center gap-2 bg-amber-500 hover:bg-amber-600 text-slate-900 px-4 py-2 rounded-lg text-sm font-semibold shadow-sm transition"><i class="fa fa-plus text-xs"></i>Tambah Supplier</button>'])
@include('admin.partials.search', ['placeholder'=>'Cari nama / telepon supplier...'])
<div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-x-auto">
<table class="w-full text-sm">
<thead><tr class="bg-slate-50 text-left text-xs uppercase tracking-wider text-slate-500 border-b border-slate-200">
<th class="px-4 py-3">Nama</th><th class="px-4 py-3">Kontak</th><th class="px-4 py-3">Telepon</th><th class="px-4 py-3">No. Pajak</th><th class="px-4 py-3">Riwayat Beli</th>
</tr></thead>
<tbody class="divide-y divide-slate-100">
@forelse($suppliers as $s)
<tr class="hover:bg-amber-50/40 transition group">
<td class="px-4 py-3"><div class="flex items-center gap-3"><div class="w-9 h-9 rounded-xl bg-gradient-to-br from-amber-400 to-orange-500 text-white flex items-center justify-center text-xs font-bold shrink-0">{{ strtoupper(substr($s->nama,0,1)) }}</div><button type="button" onclick="bukaEdit({{ $s->id }})" class="font-semibold text-slate-800 hover:text-amber-600 transition text-left" title="Klik untuk edit">{{ $s->nama }}</button></div></td>
<td class="px-4 py-3 text-slate-500">{{ $s->kontak ?? '-' }}</td>
<td class="px-4 py-3 text-slate-500">{{ $s->telepon ?? '-' }}</td>
<td class="px-4 py-3 text-slate-500">{{ $s->no_pajak ?? '-' }}</td>
<td class="px-4 py-3"><a href="{{ route('admin.suppliers.show',$s) }}" class="inline-flex items-center gap-1 text-[11px] px-2.5 py-1 rounded-full bg-indigo-50 text-indigo-600 border border-indigo-200 font-semibold hover:bg-indigo-100 transition" title="Lihat riwayat pembelian"><i class="fa fa-clock-rotate-left text-[9px]"></i>{{ $s->purchases_count }}x pembelian</a></td>
</tr>
@empty
<tr><td colspan="5" class="px-4 py-10 text-center text-slate-400"><i class="fa fa-truck text-2xl mb-2 block"></i>Tidak ada supplier yang cocok.</td></tr>
@endforelse
</tbody>
</table>
</div>
@include('admin.partials.pager', ['paginator'=>$suppliers])

{{-- ===== Modal Tambah / Edit Supplier ===== --}}
<div id="modalSupplier" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
<div class="absolute inset-0 bg-slate-900/60" onclick="tutupSupplier()"></div>
<div class="relative bg-white rounded-2xl shadow-xl w-full max-w-lg max-h-[90vh] overflow-y-auto p-6">
<div class="flex items-center justify-between mb-4">
<div class="font-bold text-lg" id="judulModalSupplier">Tambah Supplier</div>
<button type="button" onclick="tutupSupplier()" class="w-8 h-8 inline-flex items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition"><i class="fa fa-xmark"></i></button>
</div>
<form method="POST" action="{{ route('admin.suppliers.store') }}" id="formSupplier">
@csrf
<input type="hidden" name="_method" value="POST">
@include('admin.suppliers._form-fields', ['supplier'=>new \App\Models\Supplier()])
<div class="flex gap-2 mt-5 pt-4 border-t border-slate-100">
<button class="bg-amber-500 hover:bg-amber-600 text-slate-900 px-5 py-2 rounded-lg text-sm font-semibold shadow-sm transition"><i class="fa fa-check mr-1 text-xs"></i>Simpan</button>
<button type="button" onclick="tutupSupplier()" class="px-5 py-2 rounded-lg text-sm border border-slate-200 text-slate-600 hover:bg-slate-50 transition">Batal</button>
</div>
</form>
</div>
</div>

@php
$supplierJson = $suppliers->getCollection()->map(fn($s)=>['id'=>$s->id,'nama'=>$s->nama,'kontak'=>$s->kontak ?? '','telepon'=>$s->telepon ?? '','no_pajak'=>$s->no_pajak ?? '','alamat'=>$s->alamat ?? '']);
$ruteStore = route('admin.suppliers.store');
$ruteUpdateTemplate = route('admin.suppliers.update', ['supplier'=>'___ID___']);
@endphp
<script>
const supplierList = @json($supplierJson);
const ruteStore = @json($ruteStore);
const ruteUpdateTemplate = @json($ruteUpdateTemplate);
function bukaSupplier(){document.getElementById('modalSupplier').classList.remove('hidden');document.body.classList.add('overflow-hidden');}
function tutupSupplier(){document.getElementById('modalSupplier').classList.add('hidden');document.body.classList.remove('overflow-hidden');}
function kosongkanForm(){const f=document.getElementById('formSupplier');f.nama.value='';f.kontak.value='';f.telepon.value='';f.no_pajak.value='';f.alamat.value='';}
function bukaTambah(){
  document.getElementById('judulModalSupplier').textContent='Tambah Supplier';
  const f=document.getElementById('formSupplier');
  f.action=ruteStore; f._method.value='POST'; kosongkanForm(); bukaSupplier();
}
function bukaEdit(id){
  const s=supplierList.find(x=>x.id===id); if(!s) return;
  document.getElementById('judulModalSupplier').textContent='Edit Supplier';
  const f=document.getElementById('formSupplier');
  f.action=ruteUpdateTemplate.replace('___ID___', id); f._method.value='PUT';
  f.nama.value=s.nama||''; f.kontak.value=s.kontak||''; f.telepon.value=s.telepon||''; f.no_pajak.value=s.no_pajak||''; f.alamat.value=s.alamat||'';
  bukaSupplier();
}
document.addEventListener('keydown',function(e){if(e.key==='Escape')tutupSupplier();});
</script>
@if($errors->any())
<script>document.addEventListener('DOMContentLoaded',bukaTambah);</script>
@endif
@endsection
