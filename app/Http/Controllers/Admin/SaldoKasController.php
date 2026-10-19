<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SaldoKasController extends Controller
{
    public function index(Request $r) {
        $saldo = \App\Models\TopUp::saldo();
        $totalMasuk = (float) \App\Models\TopUp::sum('jumlah');
        $totalKeluar = (float) \App\Models\Expense::sum('jumlah');

        $masuk = \App\Models\TopUp::orderByDesc('tanggal')->orderByDesc('id')->get()->map(fn ($t) => [
            'tanggal' => $t->tanggal, 'jenis' => 'masuk', 'uraian' => $t->keterangan ?? 'Top up saldo kas',
            'jumlah' => (float) $t->jumlah, 'sort' => $t->tanggal->toDateString().'-'.str_pad($t->id, 10, '0', STR_PAD_LEFT),
            'ref' => null, 'ref_url' => null, 'topup_id' => $t->id,
        ]);
        $poNomor = \App\Models\Purchase::whereNotNull('expense_id')->pluck('nomor', 'expense_id');
        $poId = \App\Models\Purchase::whereNotNull('expense_id')->pluck('id', 'expense_id');
        $keluar = \App\Models\Expense::with('category')->orderByDesc('tanggal')->orderByDesc('id')->get()->map(function ($e) use ($poNomor, $poId) {
            $ref = $poNomor[$e->id] ?? null;
            return [
                'tanggal' => $e->tanggal, 'jenis' => 'keluar',
                'uraian' => ($e->keterangan ?? '').(optional($e->category)->nama ? ' ('.optional($e->category)->nama.')' : ''),
                'jumlah' => (float) $e->jumlah, 'sort' => $e->tanggal->toDateString().'-'.str_pad($e->id, 10, '0', STR_PAD_LEFT),
                'ref' => $ref, 'ref_url' => $ref ? route('admin.purchases.show', $poId[$e->id]) : null,
            ];
        });
        $riwayat = $masuk->concat($keluar)->sortByDesc('sort')->values();

        if ($r->jenis && in_array($r->jenis, ['masuk', 'keluar'])) {
            $riwayat = $riwayat->where('jenis', $r->jenis)->values();
        }
        if ($r->search) {
            $riwayat = $riwayat->filter(fn ($x) => stripos($x['uraian'], $r->search) !== false || ($x['ref'] && stripos($x['ref'], $r->search) !== false))->values();
        }
        $perPage = 20;
        $page = max(1, (int) $r->get('page', 1));
        $mutasi = new \Illuminate\Pagination\LengthAwarePaginator(
            $riwayat->forPage($page, $perPage), $riwayat->count(), $perPage, $page,
            ['path' => $r->url(), 'query' => $r->query()]
        );

        $running = $totalMasuk - $totalKeluar;
        $collection = $mutasi->getCollection()->values();
        $collection->transform(function ($x) use (&$running) {
            $x['saldo_sesudah'] = $running;
            $running += $x['jenis'] === 'keluar' ? $x['jumlah'] : -$x['jumlah'];
            return $x;
        });
        $mutasi->setCollection($collection);

        return view('admin.saldo-kas.index', compact('saldo', 'totalMasuk', 'totalKeluar', 'mutasi'));
    }

    public function topup(Request $r) {
        $r->validate(['tanggal' => 'required|date', 'jumlah' => 'required|numeric|min:1', 'keterangan' => 'nullable|string|max:255']);
        \App\Models\TopUp::create(['tanggal' => $r->tanggal, 'jumlah' => $r->jumlah, 'keterangan' => $r->keterangan]);
        return back()->with('ok', 'Saldo kas ditambah Rp '.number_format($r->jumlah, 0, ',', '.').'.');
    }

    public function destroyTopup(\App\Models\TopUp $topup) {
        $used = \App\Models\Purchase::where('top_up_id', $topup->id)->exists();
        if ($used) {
            return back()->withErrors(['kunci' => 'Top up Rp '.number_format($topup->jumlah,0,',','.').' tidak bisa dihapus ??? sudah dipakai untuk pembelian saldo kas. Hapus dulu pembelian terkait atau gunakan sumber dana lain.']);
        }
        $jumlah = (float) $topup->jumlah;
        $topup->delete();
        $saldo = \App\Models\TopUp::saldo();
        return redirect()->route('admin.saldo-kas.index')->with('ok','Top up Rp '.number_format($jumlah,0,',','.').' dihapus. Saldo kas saat ini Rp '.number_format($saldo,0,',','.').'.');
    }
}
