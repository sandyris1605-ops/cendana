{{-- ===== Partial: Field form Penghuni (dipakai di modal Edit & halaman edit terpisah) ===== --}}
{{-- Butuh variabel: $tenant. Bagian @csrf/@method & tombol ditangani pemanggil. --}}
<div class="space-y-4">
<div class="flex items-center gap-2 mb-1">
<span class="text-[11px] font-semibold uppercase tracking-wider text-indigo-600 bg-indigo-50 px-2.5 py-1 rounded-full"><i class="fa fa-crown mr-1"></i>Penghuni Utama / Kepala Hunian</span>
</div>
<div><label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">Nama</label><input name="nama" value="{{ old('nama',$tenant->nama) }}" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 outline-none" required></div>
<div><label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">No. Identitas</label><input name="nik" value="{{ old('nik',$tenant->nik) }}" placeholder="Nomor identitas" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 outline-none"></div>
<div class="grid grid-cols-2 gap-4">
<div><label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">Telepon</label><input name="telepon" value="{{ old('telepon',$tenant->telepon) }}" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 outline-none"></div>
<div><label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">Pekerjaan</label><input name="pekerjaan" value="{{ old('pekerjaan',$tenant->pekerjaan) }}" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 outline-none"></div>
</div>

{{-- Anggota Keluarga (dinamis) --}}
<div class="border-t border-slate-100 pt-4">
<div class="flex items-center justify-between mb-3">
<div class="text-xs font-semibold uppercase tracking-wider text-indigo-600"><i class="fa fa-people-roof mr-1"></i>Anggota Keluarga <span class="text-slate-400 font-normal normal-case">(istri / anak / saudara, opsional)</span></div>
<button type="button" id="btnTambahAnggota" class="inline-flex items-center gap-1.5 text-xs font-semibold text-indigo-600 hover:text-indigo-500 border border-indigo-200 hover:border-indigo-400 px-2.5 py-1.5 rounded-lg transition"><i class="fa fa-plus text-[10px]"></i>Tambah Anggota</button>
</div>
<div id="daftarAnggota" class="space-y-2">
@forelse(($tenant->anggota ?? collect()) as $i => $ag)
<div class="anggota-baris grid grid-cols-12 gap-2 items-center bg-slate-50 border border-slate-200 rounded-lg p-2">
<input type="hidden" name="anggota[{{ $i }}][id]" value="{{ $ag->id }}">
<div class="col-span-12 sm:col-span-5"><input name="anggota[{{ $i }}][nama]" value="{{ $ag->nama }}" placeholder="Nama anggota" class="w-full border border-slate-200 rounded-lg px-3 py-1.5 text-sm bg-white outline-none focus:border-indigo-500"></div>
<div class="col-span-7 sm:col-span-4"><select name="anggota[{{ $i }}][hubungan]" class="w-full border border-slate-200 rounded-lg px-3 py-1.5 text-sm bg-white outline-none focus:border-indigo-500">
<option value="istri" @selected($ag->hubungan=='istri')>Istri</option>
<option value="suami" @selected($ag->hubungan=='suami')>Suami</option>
<option value="anak" @selected($ag->hubungan=='anak')>Anak</option>
<option value="saudara" @selected($ag->hubungan=='saudara')>Saudara</option>
<option value="cucu" @selected($ag->hubungan=='cucu')>Cucu</option>
<option value="orang_tua" @selected($ag->hubungan=='orang_tua')>Orang Tua</option>
<option value="lainnya" @selected($ag->hubungan=='lainnya')>Lainnya</option>
</select></div>
<div class="col-span-5 sm:col-span-2"><input name="anggota[{{ $i }}][umur]" value="{{ $ag->umur }}" type="number" min="0" max="120" placeholder="Umur" class="w-full border border-slate-200 rounded-lg px-3 py-1.5 text-sm bg-white outline-none focus:border-indigo-500"></div>
<div class="col-span-12 sm:col-span-1 flex sm:justify-center"><button type="button" class="hapus-anggota w-full sm:w-8 h-8 inline-flex items-center justify-center rounded-lg text-rose-500 hover:bg-rose-50 transition"><i class="fa fa-trash text-xs"></i></button></div>
</div>
@empty
@endforelse
</div>
<p id="anggotaKosong" class="text-xs text-slate-400 {{ ($tenant->anggota ?? collect())->count() ? 'hidden' : '' }}"><i class="fa fa-circle-info mr-1"></i>Belum ada anggota. Klik "Tambah Anggota" bila hunian dihuni lebih dari satu orang.</p>
</div>
</div>
<script>
(function () {
  var daftar = document.getElementById('daftarAnggota');
  var btnTambah = document.getElementById('btnTambahAnggota');
  var infoKosong = document.getElementById('anggotaKosong');
  if (!daftar || !btnTambah) return;
  var idx = daftar.querySelectorAll('.anggota-baris').length;
  function cekKosong() {
    var n = daftar.querySelectorAll('.anggota-baris').length;
    if (infoKosong) infoKosong.classList.toggle('hidden', n > 0);
  }
  function barisBaru() {
    var el = document.createElement('div');
    el.className = 'anggota-baris grid grid-cols-12 gap-2 items-center bg-slate-50 border border-slate-200 rounded-lg p-2';
    el.innerHTML =
      '<div class="col-span-12 sm:col-span-5"><input name="anggota[' + idx + '][nama]" placeholder="Nama anggota" class="w-full border border-slate-200 rounded-lg px-3 py-1.5 text-sm bg-white outline-none focus:border-indigo-500"></div>' +
      '<div class="col-span-7 sm:col-span-4"><select name="anggota[' + idx + '][hubungan]" class="w-full border border-slate-200 rounded-lg px-3 py-1.5 text-sm bg-white outline-none focus:border-indigo-500">' +
      '<option value="istri">Istri</option><option value="suami">Suami</option><option value="anak">Anak</option><option value="saudara">Saudara</option><option value="cucu">Cucu</option><option value="orang_tua">Orang Tua</option><option value="lainnya">Lainnya</option></select></div>' +
      '<div class="col-span-5 sm:col-span-2"><input name="anggota[' + idx + '][umur]" type="number" min="0" max="120" placeholder="Umur" class="w-full border border-slate-200 rounded-lg px-3 py-1.5 text-sm bg-white outline-none focus:border-indigo-500"></div>' +
      '<div class="col-span-12 sm:col-span-1 flex sm:justify-center"><button type="button" class="hapus-anggota w-full sm:w-8 h-8 inline-flex items-center justify-center rounded-lg text-rose-500 hover:bg-rose-50 transition"><i class="fa fa-trash text-xs"></i></button></div>';
    daftar.appendChild(el);
    idx++;
    el.querySelector('.hapus-anggota').addEventListener('click', function () { el.remove(); cekKosong(); });
    el.querySelector('input[name*="[nama]"]').focus();
    cekKosong();
  }
  btnTambah.addEventListener('click', barisBaru);
  daftar.querySelectorAll('.hapus-anggota').forEach(function (b) {
    b.addEventListener('click', function () { b.closest('.anggota-baris').remove(); cekKosong(); });
  });
  cekKosong();
})();
</script>

