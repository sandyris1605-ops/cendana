{{-- ===== Partial: Field form Barang (dipakai di modal Tambah & halaman form terpisah) ===== --}}
{{-- Butuh variabel: $item. Bagian @csrf/@method & tombol ditangani pemanggil. --}}
<div class="space-y-4">
<div><label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">Nama</label><input name="nama" value="{{ old('nama',$item->nama ?? '') }}" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 outline-none" required></div>
<div><label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">Kategori</label><select name="kategori_id" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 outline-none"><option value="">-- Pilih Kategori --</option>@foreach(\App\Models\Category::orderBy('nama')->get() as $c)<option value="{{ $c->id }}" {{ old('kategori_id',$item->kategori_id ?? '')==$c->id ? 'selected' : '' }}>{{ $c->nama }}</option>@endforeach</select></div>
<div class="grid grid-cols-2 gap-4">
<div><label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">Harga Estimasi (Rp)</label><input type="number" name="harga_estimasi" value="{{ old('harga_estimasi',$item->harga_estimasi ?? '') }}" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 outline-none"></div>
<div><label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">Umur Pakai (bulan)</label><input type="number" name="umur_pakai_bulan" value="{{ old('umur_pakai_bulan',$item->umur_pakai_bulan ?? 12) }}" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 outline-none"></div>
</div>
</div>
