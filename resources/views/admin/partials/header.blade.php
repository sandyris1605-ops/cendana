<div class="flex flex-wrap items-center justify-between gap-3 mb-5">
<div>
<h1 class="text-xl font-bold tracking-tight">{{ $judul ?? '' }}</h1>
@if(!empty($sub))<p class="text-sm text-slate-500 mt-0.5">{{ $sub }}</p>@endif
</div>
<div class="flex flex-wrap gap-2">
@if(!empty($ctaHref))
<a href="{{ $ctaHref }}" class="inline-flex items-center gap-2 bg-amber-500 hover:bg-amber-600 text-slate-900 px-4 py-2 rounded-lg text-sm font-semibold shadow-sm transition"><i class="fa {{ $ctaIcon ?? 'fa-plus' }} text-xs"></i>{{ $ctaText ?? 'Tambah' }}</a>
@endif
{!! $ctaSlot ?? '' !!}
</div>
</div>
