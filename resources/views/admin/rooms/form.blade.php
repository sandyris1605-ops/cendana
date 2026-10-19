@extends('layouts.admin')
@section('title', ($room->exists ? 'Edit' : 'Tambah').' Kamar')
@section('content')
@include('admin.partials.header', ['judul'=>($room->exists ? 'Edit' : 'Tambah').' Kamar', 'sub'=>'Lengkapi data kamar di bawah ini', 'ctaHref'=>route('admin.rooms.index'), 'ctaText'=>'Kembali', 'ctaIcon'=>'fa-arrow-left'])
<form method="POST" action="{{ $room->exists ? route('admin.rooms.update',$room) : route('admin.rooms.store') }}" class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm max-w-lg">
@csrf @if($room->exists) @method('PUT') @endif
@include('admin.rooms._form-fields')
<div class="flex gap-2 mt-6 pt-4 border-t border-slate-100">
<button class="bg-amber-500 hover:bg-amber-600 text-slate-900 px-5 py-2 rounded-lg text-sm font-semibold shadow-sm transition"><i class="fa fa-check mr-1 text-xs"></i>Simpan</button>
<a href="{{ route('admin.rooms.index') }}" class="px-5 py-2 rounded-lg text-sm border border-slate-200 text-slate-600 hover:bg-slate-50 transition">Batal</a>
</div>
</form>
@endsection
