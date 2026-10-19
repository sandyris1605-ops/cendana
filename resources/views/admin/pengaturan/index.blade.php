@extends('layouts.admin')
@section('title','Pengaturan')
@section('content')
@include('admin.partials.header', ['judul'=>'Pengaturan', 'sub'=>'Kelola tipe kamar, gedung, info umum, dan backup database'])
@php $isAdmin = auth()->user()->role === 'admin'; @endphp

{{-- Tab navigasi --}}
<div class="border-b border-slate-200 mb-4">
<nav class="flex gap-1 overflow-x-auto" role="tablist">
<button type="button" data-tab="tipe" class="pengaturan-tab whitespace-nowrap px-4 py-2.5 text-sm font-semibold border-b-2 transition"><i class="fa fa-door-open mr-1.5"></i>Tipe Kamar</button>
<button type="button" data-tab="gedung" class="pengaturan-tab whitespace-nowrap px-4 py-2.5 text-sm font-semibold border-b-2 transition"><i class="fa fa-building mr-1.5"></i>Gedung</button>
<button type="button" data-tab="kategori" class="pengaturan-tab whitespace-nowrap px-4 py-2.5 text-sm font-semibold border-b-2 transition"><i class="fa fa-tags mr-1.5"></i>Kategori Barang</button>
<button type="button" data-tab="umum" class="pengaturan-tab whitespace-nowrap px-4 py-2.5 text-sm font-semibold border-b-2 transition"><i class="fa fa-sliders mr-1.5"></i>Pengaturan Umum</button>
@if($isAdmin)
<button type="button" data-tab="backup" class="pengaturan-tab whitespace-nowrap px-4 py-2.5 text-sm font-semibold border-b-2 transition"><i class="fa fa-database mr-1.5"></i>Backup Database</button>
@endif
</nav>
</div>

{{-- Panel: Tipe Kamar --}}
<div id="panel-tipe" class="pengaturan-panel">
<div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
<div class="flex items-center gap-3 mb-4">
<div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center"><i class="fa fa-door-open"></i></div>
<div><div class="font-bold">Tipe Kamar</div><div class="text-xs text-slate-400">{{ $types->count() }} tipe terdaftar — tipe yang masih dipakai kamar tidak bisa dihapus</div></div>
</div>
@php $editTipe = request('_edit_tipe', old('_edit_tipe')) ? $types->firstWhere('id', request('_edit_tipe', old('_edit_tipe'))) : null; @endphp
<form method="POST" action="{{ $editTipe ? route('admin.pengaturan.tipe.update', $editTipe) : route('admin.pengaturan.tipe.simpan') }}" class="mb-4 bg-slate-50 p-3 rounded-lg border border-slate-200">
@csrf @if($editTipe) @method('PUT') @endif
<div class="grid grid-cols-2 gap-2">
<div><label class="block text-[11px] font-semibold uppercase tracking-wider text-slate-500 mb-1">Nama</label><input name="nama" value="{{ old('nama', $editTipe->nama ?? '') }}" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm bg-white outline-none focus:border-amber-500" required></div>
<div><label class="block text-[11px] font-semibold uppercase tracking-wider text-slate-500 mb-1">Ukuran</label><input name="ukuran" value="{{ old('ukuran', $editTipe->ukuran ?? '') }}" placeholder="4x5 m" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm bg-white outline-none focus:border-amber-500"></div>
</div>
<div class="flex gap-2 mt-3">
<button class="bg-amber-500 hover:bg-amber-600 text-slate-900 px-4 py-1.5 rounded-lg text-sm font-semibold shadow-sm transition"><i class="fa fa-check mr-1 text-xs"></i>{{ $editTipe ? 'Simpan Perubahan' : 'Tambah' }}</button>
@if($editTipe)<a href="{{ route('admin.pengaturan') }}" class="px-4 py-1.5 rounded-lg text-sm border border-slate-200 text-slate-600 hover:bg-white transition">Batal Edit</a>@endif
</div>
</form>
<div class="overflow-x-auto">
<table class="w-full text-sm">
<thead><tr class="bg-slate-50 text-left text-xs uppercase tracking-wider text-slate-500 border-b border-slate-200">
<th class="px-3 py-2">Nama</th><th class="px-3 py-2">Ukuran</th><th class="px-3 py-2 text-center">Kamar</th><th class="px-3 py-2 text-right">Aksi</th>
</tr></thead>
<tbody class="divide-y divide-slate-100">
@forelse($types as $t)
<tr class="hover:bg-slate-50 transition {{ $editTipe && $editTipe->id == $t->id ? 'bg-amber-50/60' : '' }}">
<td class="px-3 py-2 font-semibold">{{ $t->nama }}</td>
<td class="px-3 py-2 text-slate-500">{{ $t->ukuran ?? '-' }}</td>
<td class="px-3 py-2 text-center"><span class="text-[11px] px-2 py-0.5 rounded-full bg-slate-100 text-slate-600 border border-slate-200">{{ $t->rooms_count }}</span></td>
<td class="px-3 py-2 text-right whitespace-nowrap">
<div class="inline-flex items-center gap-1">
<a href="{{ route('admin.pengaturan', ['_edit_tipe' => $t->id]) }}" title="Edit {{ $t->nama }}" class="w-8 h-8 inline-flex items-center justify-center rounded-lg text-slate-500 hover:bg-slate-100 hover:text-slate-700 transition"><i class="fa fa-pen text-xs"></i></a>
@if($t->rooms_count > 0)
<span title="Masih dipakai {{ $t->rooms_count }} kamar — tidak bisa dihapus" class="w-8 h-8 inline-flex items-center justify-center rounded-lg text-slate-300 cursor-not-allowed"><i class="fa fa-lock text-xs"></i></span>
@else
<form method="POST" action="{{ route('admin.pengaturan.tipe.hapus', $t) }}" onsubmit="return konfirmasiHapus('Hapus tipe {{ $t->nama }}?', this)">@csrf @method('DELETE')<button type="submit" title="Hapus {{ $t->nama }}" class="w-8 h-8 inline-flex items-center justify-center rounded-lg text-rose-500 hover:bg-rose-50 hover:text-rose-600 transition"><i class="fa fa-trash text-xs"></i></button></form>
@endif
</div>
</td>
</tr>
@empty
<tr><td colspan="5" class="px-3 py-8 text-center text-slate-400"><i class="fa fa-door-open text-2xl mb-2 block"></i>Belum ada tipe kamar.</td></tr>
@endforelse
</tbody>
</table>
</div>
</div>
</div>{{-- /panel-tipe --}}

{{-- Panel: Gedung --}}
<div id="panel-gedung" class="pengaturan-panel">
<div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
<div class="flex items-center gap-3 mb-4">
<div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center"><i class="fa fa-building"></i></div>
<div><div class="font-bold">Gedung</div><div class="text-xs text-slate-400">{{ $buildings->count() }} gedung terdaftar — gedung yang masih memuat kamar tidak bisa dihapus</div></div>
</div>
@php $editGedung = request('_edit_gedung', old('_edit_gedung')) ? $buildings->firstWhere('id', request('_edit_gedung', old('_edit_gedung'))) : null; @endphp
<form method="POST" action="{{ $editGedung ? route('admin.pengaturan.gedung.update', $editGedung) : route('admin.pengaturan.gedung.simpan') }}" class="mb-4 bg-slate-50 p-3 rounded-lg border border-slate-200">
@csrf @if($editGedung) @method('PUT') @endif
<div class="grid grid-cols-3 gap-2">
<div><label class="block text-[11px] font-semibold uppercase tracking-wider text-slate-500 mb-1">Nama Gedung</label><input name="nama" value="{{ old('nama', $editGedung->nama ?? '') }}" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm bg-white outline-none focus:border-amber-500" required></div>
<div class="col-span-2"><label class="block text-[11px] font-semibold uppercase tracking-wider text-slate-500 mb-1">Alamat</label><input name="alamat" value="{{ old('alamat', $editGedung->alamat ?? '') }}" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm bg-white outline-none focus:border-amber-500"></div>
<div class="col-span-3"><label class="block text-[11px] font-semibold uppercase tracking-wider text-slate-500 mb-1">Keterangan</label><input name="keterangan" value="{{ old('keterangan', $editGedung->keterangan ?? '') }}" placeholder="Opsional" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm bg-white outline-none focus:border-amber-500"></div>
</div>
<div class="flex gap-2 mt-3">
<button class="bg-amber-500 hover:bg-amber-600 text-slate-900 px-4 py-1.5 rounded-lg text-sm font-semibold shadow-sm transition"><i class="fa fa-check mr-1 text-xs"></i>{{ $editGedung ? 'Simpan Perubahan' : 'Tambah' }}</button>
@if($editGedung)<a href="{{ route('admin.pengaturan') }}" class="px-4 py-1.5 rounded-lg text-sm border border-slate-200 text-slate-600 hover:bg-white transition">Batal Edit</a>@endif
</div>
</form>
<div class="overflow-x-auto">
<table class="w-full text-sm">
<thead><tr class="bg-slate-50 text-left text-xs uppercase tracking-wider text-slate-500 border-b border-slate-200">
<th class="px-3 py-2">Nama</th><th class="px-3 py-2">Alamat</th><th class="px-3 py-2 text-center">Kamar</th><th class="px-3 py-2 text-right">Aksi</th>
</tr></thead>
<tbody class="divide-y divide-slate-100">
@forelse($buildings as $b)
<tr class="hover:bg-slate-50 transition {{ $editGedung && $editGedung->id == $b->id ? 'bg-amber-50/60' : '' }}">
<td class="px-3 py-2 font-semibold">{{ $b->nama }}</td>
<td class="px-3 py-2 text-slate-500">{{ $b->alamat ?? '-' }}<div class="text-xs text-slate-400">{{ $b->keterangan }}</div></td>
<td class="px-3 py-2 text-center"><span class="text-[11px] px-2 py-0.5 rounded-full bg-slate-100 text-slate-600 border border-slate-200">{{ $b->rooms_count }}</span></td>
<td class="px-3 py-2 text-right whitespace-nowrap">
<div class="inline-flex items-center gap-1">
<a href="{{ route('admin.pengaturan', ['_edit_gedung' => $b->id]) }}" title="Edit {{ $b->nama }}" class="w-8 h-8 inline-flex items-center justify-center rounded-lg text-slate-500 hover:bg-slate-100 hover:text-slate-700 transition"><i class="fa fa-pen text-xs"></i></a>
@if($b->rooms_count > 0)
<span title="Masih memuat {{ $b->rooms_count }} kamar — tidak bisa dihapus" class="w-8 h-8 inline-flex items-center justify-center rounded-lg text-slate-300 cursor-not-allowed"><i class="fa fa-lock text-xs"></i></span>
@else
<form method="POST" action="{{ route('admin.pengaturan.gedung.hapus', $b) }}" onsubmit="return konfirmasiHapus('Hapus gedung {{ $b->nama }}?', this)">@csrf @method('DELETE')<button type="submit" title="Hapus {{ $b->nama }}" class="w-8 h-8 inline-flex items-center justify-center rounded-lg text-rose-500 hover:bg-rose-50 hover:text-rose-600 transition"><i class="fa fa-trash text-xs"></i></button></form>
@endif
</div>
</td>
</tr>
@empty
<tr><td colspan="4" class="px-3 py-8 text-center text-slate-400"><i class="fa fa-building text-2xl mb-2 block"></i>Belum ada gedung.</td></tr>
@endforelse
</tbody>
</table>
</div>
</div>
</div>{{-- /panel-gedung --}}

{{-- Panel: Kategori Barang --}}
<div id="panel-kategori" class="pengaturan-panel">
<div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
<div class="flex items-center gap-3 mb-4">
<div class="w-10 h-10 rounded-xl bg-violet-50 text-violet-600 flex items-center justify-center"><i class="fa fa-tags"></i></div>
<div><div class="font-bold">Kategori Barang</div><div class="text-xs text-slate-400">{{ $categories->count() }} kategori terdaftar — kategori yang masih dipakai barang tidak bisa dihapus</div></div>
</div>
@php $editKategori = request('_edit_kategori', old('_edit_kategori')) ? $categories->firstWhere('id', request('_edit_kategori', old('_edit_kategori'))) : null; @endphp
<form method="POST" action="{{ $editKategori ? route('admin.pengaturan.kategori.update', $editKategori) : route('admin.pengaturan.kategori.simpan') }}" class="mb-4 bg-slate-50 p-3 rounded-lg border border-slate-200">
@csrf @if($editKategori) @method('PUT') @endif
<div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
<div class="sm:col-span-1"><label class="block text-[11px] font-semibold uppercase tracking-wider text-slate-500 mb-1">Nama</label><input name="nama" value="{{ old('nama', $editKategori->nama ?? '') }}" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm bg-white outline-none focus:border-amber-500" required></div>
<div class="sm:col-span-2"><label class="block text-[11px] font-semibold uppercase tracking-wider text-slate-500 mb-1">Deskripsi</label><input name="deskripsi" value="{{ old('deskripsi', $editKategori->deskripsi ?? '') }}" placeholder="Opsional" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm bg-white outline-none focus:border-amber-500"></div>
</div>
<div class="flex gap-2 mt-3">
<button class="bg-amber-500 hover:bg-amber-600 text-slate-900 px-4 py-1.5 rounded-lg text-sm font-semibold shadow-sm transition"><i class="fa fa-check mr-1 text-xs"></i>{{ $editKategori ? 'Simpan Perubahan' : 'Tambah' }}</button>
@if($editKategori)<a href="{{ route('admin.pengaturan') }}#kategori" class="px-4 py-1.5 rounded-lg text-sm border border-slate-200 text-slate-600 hover:bg-white transition">Batal Edit</a>@endif
</div>
</form>
<div class="overflow-x-auto">
<table class="w-full text-sm">
<thead><tr class="bg-slate-50 text-left text-xs uppercase tracking-wider text-slate-500 border-b border-slate-200">
<th class="px-3 py-2">Nama</th><th class="px-3 py-2">Deskripsi</th><th class="px-3 py-2 text-center">Barang</th><th class="px-3 py-2 text-right">Aksi</th>
</tr></thead>
<tbody class="divide-y divide-slate-100">
@forelse($categories as $c)
<tr class="hover:bg-slate-50 transition {{ $editKategori && $editKategori->id == $c->id ? 'bg-amber-50/60' : '' }}">
<td class="px-3 py-2 font-semibold">{{ $c->nama }}</td>
<td class="px-3 py-2 text-slate-500">{{ $c->deskripsi ?? '-' }}</td>
<td class="px-3 py-2 text-center"><span class="text-[11px] px-2 py-0.5 rounded-full bg-slate-100 text-slate-600 border border-slate-200">{{ $c->items_count }}</span></td>
<td class="px-3 py-2 text-right whitespace-nowrap">
<div class="inline-flex items-center gap-1">
<a href="{{ route('admin.pengaturan', ['_edit_kategori' => $c->id]) }}#kategori" title="Edit {{ $c->nama }}" class="w-8 h-8 inline-flex items-center justify-center rounded-lg text-slate-500 hover:bg-slate-100 hover:text-slate-700 transition"><i class="fa fa-pen text-xs"></i></a>
@if($c->items_count > 0)
<span title="Masih dipakai {{ $c->items_count }} barang — tidak bisa dihapus" class="w-8 h-8 inline-flex items-center justify-center rounded-lg text-slate-300 cursor-not-allowed"><i class="fa fa-lock text-xs"></i></span>
@else
<form method="POST" action="{{ route('admin.pengaturan.kategori.hapus', $c) }}" onsubmit="return konfirmasiHapus('Hapus kategori {{ $c->nama }}?', this)">@csrf @method('DELETE')<button type="submit" title="Hapus {{ $c->nama }}" class="w-8 h-8 inline-flex items-center justify-center rounded-lg text-rose-500 hover:bg-rose-50 hover:text-rose-600 transition"><i class="fa fa-trash text-xs"></i></button></form>
@endif
</div>
</td>
</tr>
@empty
<tr><td colspan="4" class="px-3 py-8 text-center text-slate-400"><i class="fa fa-tags text-2xl mb-2 block"></i>Belum ada kategori.</td></tr>
@endforelse
</tbody>
</table>
</div>
</div>
</div>{{-- /panel-kategori --}}

{{-- Panel: Pengaturan Umum --}}
<div id="panel-umum" class="pengaturan-panel">
<div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
<div class="flex items-center gap-3 mb-4">
<div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center"><i class="fa fa-sliders"></i></div>
<div><div class="font-bold">Pengaturan Umum</div><div class="text-xs text-slate-400">Info kos dipakai di website depan & laporan</div></div>
</div>
<form method="POST" action="{{ route('admin.pengaturan.umum') }}" class="grid md:grid-cols-5 gap-3">
@csrf
<div><label class="block text-[11px] font-semibold uppercase tracking-wider text-slate-500 mb-1">Nama Kos</label><input name="nama" value="{{ old('nama', $umum['nama'] ?? '') }}" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-amber-500" required></div>
<div class="md:col-span-2"><label class="block text-[11px] font-semibold uppercase tracking-wider text-slate-500 mb-1">Alamat</label><input name="alamat" value="{{ old('alamat', $umum['alamat'] ?? '') }}" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-amber-500"></div>
<div><label class="block text-[11px] font-semibold uppercase tracking-wider text-slate-500 mb-1">Telepon</label><input name="telepon" value="{{ old('telepon', $umum['telepon'] ?? '') }}" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-amber-500"></div>
<div><label class="block text-[11px] font-semibold uppercase tracking-wider text-slate-500 mb-1">Email</label><input name="email" type="email" value="{{ old('email', $umum['email'] ?? '') }}" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-amber-500"></div>
<div class="md:col-span-2"><label class="block text-[11px] font-semibold uppercase tracking-wider text-slate-500 mb-1">No. WhatsApp (format 62...)</label><input name="wa" value="{{ old('wa', $umum['wa'] ?? '') }}" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-amber-500"></div>
<div class="md:col-span-3 flex items-end">
<button class="bg-amber-500 hover:bg-amber-600 text-slate-900 px-5 py-2 rounded-lg text-sm font-semibold shadow-sm transition"><i class="fa fa-check mr-1 text-xs"></i>Simpan Pengaturan</button>
</div>
</form>
</div>
</div>{{-- /panel-umum --}}


{{-- Panel: Backup Database (khusus admin) --}}
@if($isAdmin)
<div id="panel-backup" class="pengaturan-panel">
<div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
<div class="flex items-center gap-3 mb-4">
<div class="w-10 h-10 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center"><i class="fa fa-database"></i></div>
<div><div class="font-bold">Backup Database</div><div class="text-xs text-slate-400">Unduh salinan seluruh data (.sql) — file juga tersimpan di storage, khusus admin</div></div>
</div>
<form method="POST" action="{{ route('admin.pengaturan.backup') }}" class="mb-4" onsubmit="return konfirmasiHapus('Backup seluruh database sekarang?\n\nFile .sql akan langsung diunduh.', this)">
@csrf
<button class="bg-sky-500 hover:bg-sky-600 text-white px-5 py-2 rounded-lg text-sm font-semibold shadow-sm transition"><i class="fa fa-download mr-1 text-xs"></i>Backup Sekarang</button>
</form>
<div class="overflow-x-auto">
<table class="w-full text-sm">
<thead><tr class="bg-slate-50 text-left text-xs uppercase tracking-wider text-slate-500 border-b border-slate-200">
<th class="px-3 py-2">File</th><th class="px-3 py-2">Ukuran</th><th class="px-3 py-2">Waktu</th><th class="px-3 py-2 text-right">Aksi</th>
</tr></thead>
<tbody class="divide-y divide-slate-100">
@forelse($backups as $b)
<tr class="hover:bg-slate-50 transition">
<td class="px-3 py-2 font-mono text-xs">{{ $b['nama'] }}</td>
<td class="px-3 py-2 text-slate-500">{{ number_format($b['ukuran']/1024,1,',','.') }} KB</td>
<td class="px-3 py-2 text-slate-500">{{ date('d M Y H:i', $b['waktu']) }}</td>
<td class="px-3 py-2 text-right whitespace-nowrap">
@php $menuBackup = [
['tipe'=>'link','label'=>'Unduh','ikon'=>'fa-download','url'=>route('admin.pengaturan.backup.download', $b['nama'])],
['tipe'=>'hapus','label'=>'Hapus','ikon'=>'fa-trash','url'=>route('admin.pengaturan.backup.hapus', $b['nama']),'pesan'=>'Hapus file '.$b['nama'].'?'],
]; @endphp
@include('admin.partials.aksi',['menu'=>$menuBackup])
</td>
</tr>
@empty
<tr><td colspan="4" class="px-3 py-8 text-center text-slate-400"><i class="fa fa-database text-2xl mb-2 block"></i>Belum ada file backup — klik "Backup Sekarang".</td></tr>
@endforelse
</tbody>
</table>
</div>
</div>
@endif
</div>{{-- /panel-backup --}}

<style>
.pengaturan-panel { display: none; }
.pengaturan-panel.active { display: block; animation: panelMuncul .3s ease both; }
@keyframes panelMuncul { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }
.pengaturan-tab { color: #64748b; border-color: transparent; }
.pengaturan-tab:hover { color: #b45309; }
.pengaturan-tab.active { color: #b45309; border-color: #f59e0b; }
@media (prefers-reduced-motion: reduce) { .pengaturan-panel.active { animation: none; } }
</style>
<script>
(function () {
  var tabs = document.querySelectorAll('.pengaturan-tab');
  var panels = document.querySelectorAll('.pengaturan-panel');
  function aktifkan(nama) {
    tabs.forEach(function (t) { t.classList.toggle('active', t.dataset.tab === nama); });
    panels.forEach(function (p) { p.classList.toggle('active', p.id === 'panel-' + nama); });
  }
  tabs.forEach(function (t) {
    t.addEventListener('click', function () {
      aktifkan(t.dataset.tab);
      history.replaceState(null, '', '#' + t.dataset.tab);
    });
  });
  // Tentukan tab awal: hash URL → query edit (_edit_tipe/_edit_gedung) → default 'tipe'
  var awal = (location.hash || '').replace('#', '');
  var valid = ['tipe', 'gedung', 'kategori', 'umum', 'backup'];
  if (valid.indexOf(awal) === -1) {
    var params = new URLSearchParams(location.search);
    if (params.get('_edit_tipe')) awal = 'tipe';
    else if (params.get('_edit_gedung')) awal = 'gedung';
    else if (params.get('_edit_kategori')) awal = 'kategori';
    else awal = 'tipe';
  }
  aktifkan(awal);
})();
</script>

@endsection

