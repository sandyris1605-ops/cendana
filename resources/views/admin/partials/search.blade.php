<form method="GET" class="flex flex-wrap items-center gap-2 mb-4">
<div class="relative flex-1" style="max-width:22rem">
<i class="fa fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
<input name="search" value="{{ request('search') }}" placeholder="{{ $placeholder ?? 'Cari...' }}" class="w-full pl-9 pr-3 py-2 border border-slate-200 rounded-lg text-sm bg-white focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 outline-none transition">
</div>
{!! $extra ?? '' !!}
<button class="bg-slate-900 hover:bg-slate-800 text-white px-4 py-2 rounded-lg text-sm transition">Cari</button>
@if(request('search'))
<a href="{{ url()->current() }}" class="px-3 py-2 text-sm text-slate-500 hover:text-slate-800 transition"><i class="fa fa-rotate-left mr-1"></i>Reset</a>
@endif
</form>
