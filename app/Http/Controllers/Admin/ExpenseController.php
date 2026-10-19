<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function index(Request $r) {
        $q = \App\Models\Expense::with(['category','items','purchase']);
        if ($r->search) $q->where('keterangan','like','%'.$r->search.'%')->orWhere('nomor','like','%'.$r->search.'%')->orWhereHas('items', fn ($w) => $w->where('uraian','like','%'.$r->search.'%'));
        $data = $q->orderByDesc('tanggal')->paginate(20)->withQueryString();
        return view('admin.expenses.index', ['expenses'=>$data]);
    }
    public function create() {
        return view('admin.expenses.form', ['expense'=>new \App\Models\Expense(),'saldo'=>\App\Models\TopUp::saldo()]);
    }
    protected function validasiItem(Request $r) {
        // Buang baris yang benar-benar kosong dulu (baris default yang tak diisi user)
        // agar validasi items.*.uraian tidak memblokir baris lain yang terisi.
        if (is_array($r->input('items'))) {
            $rapih = collect($r->input('items'))
                ->filter(fn ($row) => is_array($row) && collect($row)->contains(fn ($v) => trim((string) $v) !== ''))
                ->values();
            $r->merge(['items' => $rapih->all()]);
        }
        $r->validate([
            'tanggal' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.uraian' => 'required|string|max:255',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.harga' => 'required|numeric|min:1',
        ], [
            'items.required' => 'Tambahkan minimal 1 item pengeluaran.',
            'items.*.uraian.required' => 'Setiap baris wajib diisi uraian.',
            'items.*.qty.min' => 'Qty minimal 1.',
            'items.*.harga.min' => 'Nominal minimal Rp 1.',
        ]);
        $bersih = collect($r->items)
            ->filter(fn ($row) => trim((string) ($row['uraian'] ?? '')) !== '')
            ->map(fn ($row) => ['uraian'=>trim($row['uraian']),'qty'=>(int) $row['qty'],'harga'=>(float) $row['harga']])
            ->values();
        if ($bersih->isEmpty()) abort(back()->withInput()->withErrors(['items'=>'Tambahkan minimal 1 item pengeluaran.']));
        return $bersih;
    }
    protected function ringkasan($items) {
        $ringkas = $items->map(fn ($i) => $i['uraian'].' x'.$i['qty'])->implode(', ');
        if (mb_strlen($ringkas) > 120) $ringkas = mb_substr($ringkas, 0, 117).'...';
        return $ringkas;
    }
    public function store(Request $r) {
        $items = $this->validasiItem($r);
        $total = (int) $items->sum(fn ($i) => $i['qty'] * $i['harga']);
        $saldo = \App\Models\TopUp::saldo();
        if ($total > $saldo) {
            return back()->withInput()->withErrors(['items'=>'Saldo kas tidak cukup (total Rp '.number_format($total,0,',','.').', sisa Rp '.number_format($saldo,0,',','.').'). Top up dulu di menu Saldo Kas.']);
        }
        \DB::transaction(function () use ($r, $items, $total) {
            $kat = \App\Models\ExpenseCategory::firstOrCreate(['nama'=>'Lainnya']);
            $e = \App\Models\Expense::create(['nomor'=>'EXP-'.date('Ymd-His'),'tanggal'=>$r->tanggal,'expense_category_id'=>$kat->id,'jumlah'=>$total,'keterangan'=>$this->ringkasan($items)]);
            foreach ($items as $i) {
                \App\Models\ExpenseItem::create(['expense_id'=>$e->id,'uraian'=>$i['uraian'],'qty'=>$i['qty'],'harga_satuan'=>$i['harga'],'subtotal'=>$i['qty']*$i['harga']]);
            }
        });
        return redirect()->route('admin.expenses.index')->with('ok','Pengeluaran '.$items->count().' item dicatat. Sisa saldo kas Rp '.number_format(\App\Models\TopUp::saldo(),0,',','.').'.');
    }
    protected function terkunci(\App\Models\Expense $expense): ?string {
        if (\App\Models\Purchase::where('expense_id', $expense->id)->exists()) {
            $po = \App\Models\Purchase::where('expense_id', $expense->id)->value('nomor');
            return 'Pengeluaran ini otomatis dari '.$po.' — ubah/hapus lewat menu Pembelian agar saldo konsisten.';
        }
        return null;
    }
    public function edit(\App\Models\Expense $expense) {
        if ($pesan = $this->terkunci($expense)) return redirect()->route('admin.expenses.index')->withErrors(['kunci'=>$pesan]);
        $expense->load('items');
        return view('admin.expenses.form', ['expense'=>$expense,'saldo'=>\App\Models\TopUp::saldo() + $expense->jumlah]);
    }
    public function update(Request $r, \App\Models\Expense $expense) {
        if ($pesan = $this->terkunci($expense)) return redirect()->route('admin.expenses.index')->withErrors(['kunci'=>$pesan]);
        $items = $this->validasiItem($r);
        $total = (int) $items->sum(fn ($i) => $i['qty'] * $i['harga']);
        $tersedia = \App\Models\TopUp::saldo() + $expense->jumlah;
        if ($total > $tersedia) {
            return back()->withInput()->withErrors(['items'=>'Saldo kas tidak cukup (total Rp '.number_format($total,0,',','.').', tersedia Rp '.number_format($tersedia,0,',','.').'). Top up dulu di menu Saldo Kas.']);
        }
        \DB::transaction(function () use ($r, $expense, $items, $total) {
            $expense->update(['tanggal'=>$r->tanggal,'jumlah'=>$total,'keterangan'=>$this->ringkasan($items)]);
            $expense->items()->delete();
            foreach ($items as $i) {
                \App\Models\ExpenseItem::create(['expense_id'=>$expense->id,'uraian'=>$i['uraian'],'qty'=>$i['qty'],'harga_satuan'=>$i['harga'],'subtotal'=>$i['qty']*$i['harga']]);
            }
        });
        return redirect()->route('admin.expenses.index')->with('ok','Diupdate.');
    }
    public function destroy(\App\Models\Expense $expense) {
        if ($pesan = $this->terkunci($expense)) return back()->withErrors(['kunci'=>$pesan]);
        $expense->delete();
        return redirect()->route('admin.expenses.index')->with('ok','Pengeluaran dihapus. Saldo kas bertambah kembali Rp '.number_format($expense->jumlah,0,',','.').'.');
    }
}
