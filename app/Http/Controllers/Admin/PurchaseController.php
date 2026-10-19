<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PurchaseController extends Controller
{
    public function index(Request $r) {
        $q = \App\Models\Purchase::with('supplier');
        if ($r->search) $q->where('nomor','like','%'.$r->search.'%')->orWhereHas('supplier', fn ($s) => $s->where('nama','like','%'.$r->search.'%'));
        if ($r->sumber_dana && in_array($r->sumber_dana, ['kas','perusahaan'])) $q->where('sumber_dana', $r->sumber_dana);
        $data = $q->orderByDesc('id')->paginate(15)->withQueryString();
        return view('admin.purchases.index', ['purchases'=>$data]);
    }
    public function create() {
        return view('admin.purchases.form', ['purchase'=>new \App\Models\Purchase(),'suppliers'=>\App\Models\Supplier::all(),'items'=>\App\Models\Item::with('category')->orderBy('nama')->get(),'saldo'=>\App\Models\TopUp::saldo()]);
    }
    public function store(Request $r) {
        $r->validate(['tanggal'=>'required|date','supplier_id'=>'nullable|exists:suppliers,id','items'=>'required|array','sumber_dana'=>'required|in:kas,perusahaan']);
        \DB::beginTransaction();
        try {
            $nomor = 'PO-'.date('Ymd-His');
            while (\App\Models\Purchase::where('nomor',$nomor)->exists()) {
                $nomor = 'PO-'.date('Ymd-His').'-'.substr(md5(uniqid()),0,4);
            }
            $p = \App\Models\Purchase::create(['nomor'=>$nomor,'supplier_id'=>$r->supplier_id ?: null,'tanggal'=>$r->tanggal,'total'=>0,'status'=>'selesai','catatan'=>$r->catatan]);

            // Barcode baru: CND-{KataNamaBarang}-{Tahun}-{urutan 4 digit} — urutan PER BARANG per tahun
            // (mis. CND-Kasur-2026-0001, CND-Kasur-2026-0002; barang lain mulai urutannya sendiri).
            $thn = date('Y');
            $seqPerKata = [];

            $total = 0;
            foreach ($r->items as $row) {
                if (empty($row['item_id']) || empty($row['qty'])) continue;
                $it = \App\Models\Item::find($row['item_id']); if (!$it) continue;
                $harga = $row['harga'] ?? $it->harga_estimasi; $qty = (int) $row['qty'];
                $sub = $harga*$qty; $total += $sub;
                \App\Models\PurchaseItem::create(['purchase_id'=>$p->id,'item_id'=>$it->id,'qty'=>$qty,'harga_satuan'=>$harga,'subtotal'=>$sub]);
                for ($i=0;$i<$qty;$i++) {
                    $kata = \App\Models\AssetUnit::kataBarcode($it->nama);
                    if (!isset($seqPerKata[$kata])) {
                        $prefix = 'CND-'.$kata.'-'.$thn.'-';
                        $last = \App\Models\AssetUnit::where('kode_barcode','like',$prefix.'%')->orderByDesc('kode_barcode')->value('kode_barcode');
                        $seqPerKata[$kata] = $last ? ((int) substr($last, -4)) + 1 : 1;
                    }
                    do {
                        $kode = sprintf('CND-%s-%s-%04d', $kata, $thn, $seqPerKata[$kata]++);
                    } while (\App\Models\AssetUnit::where('kode_barcode',$kode)->exists());
                    $ganti = \Carbon\Carbon::parse($r->tanggal)->addMonths($it->umur_pakai_bulan)->toDateString();
                    \App\Models\AssetUnit::create(['kode_barcode'=>$kode,'item_id'=>$it->id,'purchase_id'=>$p->id,'tanggal_beli'=>$r->tanggal,'harga_perolehan'=>$harga,'nilai_buku'=>$harga,'tanggal_ganti_rencana'=>$ganti]);
                }
            }
            if ($total <= 0) {
                \DB::rollBack();
                return back()->withInput()->withErrors(['items'=>'Pilih minimal 1 barang dan isi qty-nya sebelum menyimpan.']);
            }
            $p->update(['total'=>$total]);
            if ($r->sumber_dana === 'kas') {
                $saldo = \App\Models\TopUp::saldo();
                if ($total > $saldo) {
                    \DB::rollBack();
                    return back()->withInput()->withErrors(['items'=>'Saldo kas tidak cukup untuk pembelian ini (total Rp '.number_format($total,0,',','.').', sisa Rp '.number_format($saldo,0,',','.').'). Pilih sumber dana Perusahaan atau top up dulu di menu Saldo Kas.']);
                }
                $kat = \App\Models\ExpenseCategory::firstOrCreate(['nama'=>'Pembelian']);
                $ringkas = \App\Models\PurchaseItem::with('item')->where('purchase_id',$p->id)->get()
                    ->map(fn ($pi) => (optional($pi->item)->nama ?? 'Barang').' x'.$pi->qty)->implode(', ');
                if (mb_strlen($ringkas) > 120) $ringkas = mb_substr($ringkas, 0, 117).'...';
                $exp = \App\Models\Expense::create(['nomor'=>'EXP-'.date('Ymd-His').'-'.$p->id,'expense_category_id'=>$kat->id,'tanggal'=>$r->tanggal,'jumlah'=>$total,'keterangan'=>'Pembelian '.$nomor.($ringkas ? ': '.$ringkas : '')]);
                \App\Models\ExpenseItem::create(['expense_id'=>$exp->id,'uraian'=>'Pembelian '.$nomor.($ringkas ? ': '.$ringkas : ''),'qty'=>1,'harga_satuan'=>$total,'subtotal'=>$total]);
                $p->update(['sumber_dana'=>'kas','expense_id'=>$exp->id,'top_up_id'=>\App\Models\TopUp::orderByDesc('tanggal')->orderByDesc('id')->value('id')]);
            } else {
                $p->update(['sumber_dana'=>'perusahaan','expense_id'=>null]);
            }
            \DB::commit();
        } catch (\Throwable $e) {
            \DB::rollBack();
            throw $e;
        }
        return redirect()->route('admin.purchases.index')->with('ok','Pembelian + unit barcode dibuat.'.($r->sumber_dana==='kas' ? ' Saldo kas berkurang Rp '.number_format($total,0,',','.').'.' : ' (dana perusahaan, saldo kas tidak berubah).'));
    }
    public function show(\App\Models\Purchase $purchase) {
        $purchase->load(['supplier','items.item']);
        return view('admin.purchases.show', compact('purchase'));
    }
    public function destroy(\App\Models\Purchase $purchase) {
        // Tolak bila ada unit yang sudah terpasang di kamar — PO hanya boleh dihapus
        // selama semua barangnya masih di gudang. (Unit 'dibuang' tidak menghalangi.)
        $terpasang = \App\Models\AssetUnit::where('purchase_id', $purchase->id)->where('status', 'terpasang')->get();
        if ($terpasang->isNotEmpty()) {
            $contoh = $terpasang->first();
            $kamar = optional(optional($contoh->activePlacement)->room)->nomor ?? '-';
            return back()->withErrors(['kunci' => 'PO ' . $purchase->nomor . ' tidak bisa dihapus — ' . $terpasang->count() . ' unit masih terpasang di kamar (mis. ' . $contoh->kode_barcode . ' di Kamar ' . $kamar . '). Lepas dulu asetnya ke gudang (menu Aset), baru hapus PO.']);
        }
        \DB::transaction(function () use ($purchase) {
            \App\Models\AssetUnit::where('purchase_id', $purchase->id)->delete();
            $expenseId = $purchase->expense_id;
            $purchase->delete();
            if ($expenseId) \App\Models\Expense::where('id', $expenseId)->delete();
        });
        return redirect()->route('admin.purchases.index')->with('ok','Pembelian + unit barcode terkait dihapus.'.($purchase->sumber_dana==='kas' ? ' Saldo kas dikembalikan.' : ''));
    }
}
