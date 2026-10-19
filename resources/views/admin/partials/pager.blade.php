<div class="mt-4 flex flex-wrap items-center justify-between gap-3">
<div class="text-sm text-slate-500">Menampilkan <span class="font-medium text-slate-700">{{ $paginator->firstItem() ?? 0 }}</span>–<span class="font-medium text-slate-700">{{ $paginator->lastItem() ?? 0 }}</span> dari <span class="font-medium text-slate-700">{{ $paginator->total() }}</span> data</div>
<div>{{ $paginator->links() }}</div>
</div>
