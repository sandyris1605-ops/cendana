{{-- ===== Partial: Field form Supplier (dipakai di modal Tambah & Edit di halaman daftar) ===== --}}
{{-- Butuh variabel: $supplier. Bagian @csrf/@method & tombol ditangani pemanggil. --}}
<div class="space-y-4">
<div><label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">Nama</label><input name="nama" value="{{ old('nama',$supplier->nama ?? '') }}" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 outline-none" required></div>
<div class="grid grid-cols-2 gap-4">
<div><label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">Kontak / PIC</label><input name="kontak" value="{{ old('kontak',$supplier->kontak ?? '') }}" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 outline-none"></div>
<div><label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">Telepon</label><input name="telepon" value="{{ old('telepon',$supplier->telepon ?? '') }}" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 outline-none"></div>
</div>
<div><label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">No. Pajak (NPWP)</label><input name="no_pajak" value="{{ old('no_pajak',$supplier->no_pajak ?? '') }}" placeholder="00.000.000.0-000.000" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 outline-none"></div>
<div><label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">Alamat</label><textarea name="alamat" rows="3" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 outline-none">{{ old('alamat',$supplier->alamat ?? '') }}</textarea></div>
</div>
