@extends('layouts.admin')
@section('title','Form Penghuni')
@section('content')
@include('admin.partials.header', ['judul'=>($tenant->exists ? 'Edit' : 'Tambah').' Penghuni', 'sub'=>$tenant->exists ? 'Ubah data identitas penghuni' : 'Saat disimpan, penempatan kamar dibuat otomatis', 'ctaHref'=>route('admin.tenants.index'), 'ctaText'=>'Kembali', 'ctaIcon'=>'fa-arrow-left'])
<form method="POST" action="{{ $tenant->exists ? route('admin.tenants.update',$tenant) : route('admin.tenants.store') }}" class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm max-w-lg">
@csrf @if($tenant->exists) @method('PUT') @endif
@include('admin.tenants._form-fields')

@if(!$tenant->exists)
<div class="border-t border-slate-100 pt-4">
<div class="text-xs font-semibold uppercase tracking-wider text-amber-600 mb-3"><i class="fa fa-key mr-1"></i>Penempatan Kamar</div>
<div class="space-y-4">
<div><label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">Kamar</label>
@if($rooms->isEmpty())
<div class="text-sm text-rose-600 bg-rose-50 border border-rose-200 rounded-lg px-3 py-2.5"><i class="fa fa-circle-exclamation mr-1"></i>Tidak ada kamar tersedia — semua kamar terisi / maintenance. Selesaikan penghuni keluar atau pindah dulu.</div>
<input type="hidden" name="room_id" value="">
@else
<select name="room_id" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm bg-white focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 outline-none">@foreach($rooms as $r)<option value="{{ $r->id }}">{{ $r->nomor }} - {{ optional($r->roomType)->nama }} ({{ $r->status }})</option>@endforeach</select>
@endif
@if($errors->any())<div class="text-xs text-rose-600 bg-rose-50 border border-rose-200 rounded-lg px-3 py-2 mt-2"><i class="fa fa-circle-exclamation mr-1"></i>{{ $errors->first('room_id') ?? $errors->first() }}</div>@endif
</div>
<div class="grid grid-cols-1 gap-4">
<div><label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">Tanggal Masuk</label><input type="date" name="tanggal_masuk" value="{{ date('Y-m-d') }}" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 outline-none"></div>
</div>
</div>
</div>
@endif
<div class="flex gap-2 mt-6 pt-4 border-t border-slate-100">
<button class="bg-amber-500 hover:bg-amber-600 text-slate-900 px-5 py-2 rounded-lg text-sm font-semibold shadow-sm transition disabled:opacity-50 disabled:cursor-not-allowed" {{ (!$tenant->exists && $rooms->isEmpty()) ? 'disabled' : '' }}><i class="fa fa-check mr-1 text-xs"></i>Simpan</button>
<a href="{{ route('admin.tenants.index') }}" class="px-5 py-2 rounded-lg text-sm border border-slate-200 text-slate-600 hover:bg-slate-50 transition">Batal</a>
</div>
</form>
{{-- JS tambah/hapus anggota ada di partial _form-fields --}}
@endsection
