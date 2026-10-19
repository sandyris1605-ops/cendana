{{-- Menu aksi modern (kebab ⋮) untuk baris tabel.
  Param: $menu = array of:
    ['tipe'=>'link',  'label'=>'Detail', 'ikon'=>'fa-eye', 'url'=>route(...)],
    ['tipe'=>'hapus', 'label'=>'Hapus',  'ikon'=>'fa-trash', 'url'=>route(...), 'pesan'=>'Konfirmasi hapus...'],
    ['tipe'=>'info',  'label'=>'Terkunci', 'ikon'=>'fa-lock', 'title'=>'Alasan terkunci...'],
  Item 'hapus' otomatis diberi garis pemisah di atasnya (kecuali item pertama).
--}}
<div class="aksi-wrap relative inline-block text-left">
<button type="button" class="aksi-toggle w-8 h-8 inline-flex items-center justify-center rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition" aria-haspopup="true" aria-expanded="false" title="Aksi"><i class="fa fa-ellipsis-vertical"></i></button>
<div class="aksi-menu hidden w-48 bg-white border border-slate-200 rounded-xl shadow-lg py-1.5 text-sm" role="menu">
@foreach($menu as $m)
@if(($m['tipe'] ?? 'link') === 'hapus' && !$loop->first)
<div class="my-1.5 border-t border-slate-100"></div>
@endif
@if(($m['tipe'] ?? 'link') === 'hapus')
<form method="POST" action="{{ $m['url'] }}" onsubmit="return konfirmasiHapus({!! htmlspecialchars(json_encode($m['pesan'] ?? 'Hapus data ini?'), ENT_QUOTES) !!}, this)">
@csrf @method('DELETE')
<button type="submit" class="w-full flex items-center gap-2.5 px-4 py-2 text-left text-rose-600 hover:bg-rose-50 transition" role="menuitem"><i class="fa {{ $m['ikon'] ?? 'fa-trash' }} w-4 text-center text-xs"></i>{{ $m['label'] ?? 'Hapus' }}</button>
</form>
@elseif(($m['tipe'] ?? '') === 'info')
<span class="flex items-center gap-2.5 px-4 py-2 text-slate-400 cursor-default" @if(!empty($m['title'])) title="{{ $m['title'] }}" @endif role="menuitem"><i class="fa {{ $m['ikon'] ?? 'fa-lock' }} w-4 text-center text-xs"></i>{{ $m['label'] }}</span>
@else
<a href="{{ $m['url'] }}" class="flex items-center gap-2.5 px-4 py-2 text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition" role="menuitem"><i class="fa {{ $m['ikon'] ?? 'fa-eye' }} w-4 text-center text-xs text-slate-400"></i>{{ $m['label'] }}</a>
@endif
@endforeach
</div>
</div>
