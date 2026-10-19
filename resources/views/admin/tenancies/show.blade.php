@extends('layouts.admin')
@section('title','Kelola Hunian')
@section('content')
@include('admin.partials.header', ['judul'=>optional($tenancy->tenant)->nama ?? 'Penempatan', 'sub'=>'Kamar '.(optional($tenancy->room)->nomor ?? '-').' • Masuk '.\Illuminate\Support\Carbon::parse($tenancy->tanggal_masuk)->translatedFormat('d M Y').($tenancy->tanggal_keluar ? ' • Keluar '.\Illuminate\Support\Carbon::parse($tenancy->tanggal_keluar)->translatedFormat('d M Y') : '').' • '.ucfirst($tenancy->status), 'ctaSlot'=>$tenancy->status=='aktif' ? '<form method="POST" action="'.route('admin.tenancies.destroy',$tenancy).'" class="inline" onsubmit="return konfirmasiHapus(\'Penghuni keluar dari kamar ini?\', this)"><input type="hidden" name="_token" value="'.csrf_token().'"><input type="hidden" name="_method" value="DELETE"><button class="inline-flex items-center gap-2 bg-rose-500 hover:bg-rose-600 text-white px-4 py-2 rounded-lg text-sm font-semibold shadow-sm transition"><i class="fa fa-right-from-bracket text-xs"></i>Penghuni Keluar</button></form>' : ''])
<div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
<div class="flex items-center gap-3 mb-4">
<div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center"><i class="fa fa-door-open"></i></div>
<div><div class="font-bold">Info Penempatan</div><div class="text-xs text-slate-400">Penghuni tidak dikenakan tagihan</div></div>
</div>
<div class="grid md:grid-cols-2 gap-3 text-sm">
<div class="bg-slate-50 rounded-lg px-4 py-3"><div class="text-xs text-slate-400 uppercase tracking-wider">Penghuni</div><div class="font-semibold">{{ optional($tenancy->tenant)->nama ?? '-' }}</div></div>
<div class="bg-slate-50 rounded-lg px-4 py-3"><div class="text-xs text-slate-400 uppercase tracking-wider">Kamar</div><div class="font-semibold">{{ optional($tenancy->room)->nomor ?? '-' }} ({{ optional(optional($tenancy->room)->roomType)->nama ?? '-' }})</div></div>
<div class="bg-slate-50 rounded-lg px-4 py-3"><div class="text-xs text-slate-400 uppercase tracking-wider">Tanggal Masuk</div><div class="font-semibold">{{ \Illuminate\Support\Carbon::parse($tenancy->tanggal_masuk)->translatedFormat('d M Y') }}</div></div>
<div class="bg-slate-50 rounded-lg px-4 py-3"><div class="text-xs text-slate-400 uppercase tracking-wider">Tanggal Keluar</div><div class="font-semibold">{{ $tenancy->tanggal_keluar ? \Illuminate\Support\Carbon::parse($tenancy->tanggal_keluar)->translatedFormat('d M Y') : '—' }}</div></div>
</div>
</div>
@endsection
