<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index() {
        return view('admin.laporan.index');
    }
    protected function data($jenis, $r) {
        $dari = $r->dari ?? now()->startOfYear()->toDateString();
        $sampai = $r->sampai ?? now()->toDateString();
        switch ($jenis) {
            case 'keuangan':
                $masuk = \App\Models\TopUp::whereBetween('tanggal',[$dari,$sampai])->sum('jumlah');
                $keluar = \App\Models\Expense::whereBetween('tanggal',[$dari,$sampai])->sum('jumlah');
                $rows = \App\Models\TopUp::whereBetween('tanggal',[$dari,$sampai])->orderBy('tanggal')->get();
                $rows2 = \App\Models\Expense::with('category')->whereBetween('tanggal',[$dari,$sampai])->orderBy('tanggal')->get();
                $belanjaPerusahaan = \App\Models\Purchase::where('sumber_dana','perusahaan')->whereBetween('tanggal',[$dari,$sampai])->sum('total');
                $rows3 = \App\Models\Purchase::with('supplier')->where('sumber_dana','perusahaan')->whereBetween('tanggal',[$dari,$sampai])->orderBy('tanggal')->get();
                $jmlTransaksi = $rows->count() + $rows2->count();
                return compact('dari','sampai','masuk','keluar','rows','rows2','belanjaPerusahaan','rows3','jmlTransaksi');
            case 'okupansi':
                $rows = \App\Models\Room::with(['roomType','building','activeTenancy.tenant'])->orderBy('nomor')->get();
                $total = $rows->count();
                $terisi = $rows->where('status','terisi')->count();
                $tersedia = $rows->where('status','tersedia')->count();
                $maintenance = $rows->where('status','maintenance')->count();
                $persen = $total ? round($terisi/$total*100) : 0;
                $potensiBulanan = 0;
                return compact('rows','dari','sampai','total','terisi','tersedia','maintenance','persen','potensiBulanan');
            case 'inventaris':
                $q = \App\Models\AssetUnit::with(['item.category','activePlacement.room']);
                $q->whereDate('tanggal_beli', '>=', $dari)->whereDate('tanggal_beli', '<=', $sampai);
                $rows = $q->orderBy('kode_barcode')->get();
                $totalPerolehan = $rows->sum('harga_perolehan');
                $totalBuku = $rows->sum('nilai_buku');
                $jmlTerpasang = $rows->where('status','terpasang')->count();
                $jmlGudang = $rows->where('status','gudang')->count();
                $jmlRusak = $rows->where('kondisi','rusak')->where('status','!=','dibuang')->count();
                $jmlDibuang = $rows->where('status','dibuang')->count();
                return compact('rows','dari','sampai','totalPerolehan','totalBuku','jmlTerpasang','jmlGudang','jmlRusak','jmlDibuang');
            case 'pembelian':
                $rows = \App\Models\Purchase::with(['supplier','items.item'])->whereBetween('tanggal',[$dari,$sampai])->orderByDesc('tanggal')->get();
                $totalBelanja = $rows->sum('total');
                $totalPerusahaan = $rows->where('sumber_dana','perusahaan')->sum('total');
                $totalKas = $rows->where('sumber_dana','!=','perusahaan')->sum('total');
                $jmlItem = $rows->sum(fn ($p) => $p->items->sum('qty'));
                return compact('rows','dari','sampai','totalBelanja','totalPerusahaan','totalKas','jmlItem');
            case 'penyusutan':
                $rows = \App\Models\AssetUnit::with('item')->where('status','!=','dibuang')->orderByDesc('id')->get();
                $totalPerolehan = $rows->sum('harga_perolehan');
                $totalBuku = $rows->sum('nilai_buku');
                $akumulasiSusut = $totalPerolehan - $totalBuku;
                $persenSusut = $totalPerolehan ? round($akumulasiSusut/$totalPerolehan*100) : 0;
                $periode = now()->format('Y-m');
                $susutPeriode = \App\Models\Depreciation::where('periode',$periode)->sum('nilai_penyusutan');
                return compact('rows','dari','sampai','totalPerolehan','totalBuku','akumulasiSusut','persenSusut','susutPeriode','periode');
            default: // penggantian
                $rows = \App\Models\AssetUnit::with(['item','activePlacement.room'])
                    ->where('status','!=','dibuang')
                    ->whereDate('tanggal_ganti_rencana','<=',now()->addDays(365))
                    ->orderBy('tanggal_ganti_rencana')->get();
                $jmlPerlu = $rows->where('kondisi','perlu_diganti')->count();
                $jmlTerlambat = $rows->filter(fn ($u) => $u->kondisi !== 'perlu_diganti' && $u->tanggal_ganti_rencana && $u->tanggal_ganti_rencana <= now()->toDateString())->count();
                $jmlSegera = $rows->filter(fn ($u) => $u->kondisi === 'baik' && $u->tanggal_ganti_rencana && $u->tanggal_ganti_rencana > now()->toDateString() && $u->tanggal_ganti_rencana <= now()->addDays(90)->toDateString())->count();
                return compact('rows','dari','sampai','jmlPerlu','jmlTerlambat','jmlSegera');
        }
    }
    public function show($jenis, \Illuminate\Http\Request $r) {
        $data = $this->data($jenis, $r);
        return view('admin.laporan.show', ['jenis'=>$jenis] + $data);
    }
    public function pdf($jenis, \Illuminate\Http\Request $r) {
        $data = $this->data($jenis, $r);
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.laporan.pdf', ['jenis'=>$jenis] + $data)->setPaper('a4','landscape');
        return $pdf->download("laporan-$jenis.pdf");
    }
    public function csv($jenis, \Illuminate\Http\Request $r) {
        $data = $this->data($jenis, $r);
        $name = "laporan-$jenis.csv";
        $cols = [
            'keuangan' => ['Tanggal','Jenis','Keterangan','Jumlah'],
            'okupansi' => ['Kamar','Gedung','Tipe','Lantai','Penghuni','Status'],
            'inventaris' => ['Barcode','Barang','Lokasi','Kondisi','Status','Perolehan','Nilai Buku','Tanggal Beli'],
            'pembelian' => ['Tanggal','Nomor','Supplier','Dana','Item','Total'],
            'penyusutan' => ['Barcode','Barang','Perolehan','Residu','Akumulasi Susut','Nilai Buku','Persen Susut'],
            'penggantian' => ['Barcode','Barang','Kamar','Rencana Ganti','Kondisi','Status Jadwal'],
        ];
        return response()->streamDownload(function() use ($jenis,$data,$cols) {
            $o = fopen('php://output','w');
            fputcsv($o, ['Cendana Resident — Laporan '.ucfirst($jenis)]);
            fputcsv($o, ['Periode', $data['dari'] ?? '-', 's/d', $data['sampai'] ?? '-']);
            fputcsv($o, []);
            fputcsv($o, $cols[$jenis] ?? ['Data']);
            $rupiah = fn($n) => 'Rp '.number_format((float)$n,0,',','.');
            if ($jenis === 'keuangan') {
                foreach (($data['rows'] ?? []) as $x) fputcsv($o, [$x->tanggal, 'Masuk (Kas)', $x->keterangan ?? 'Top Up', $rupiah($x->jumlah)]);
                foreach (($data['rows2'] ?? []) as $x) fputcsv($o, [$x->tanggal, 'Keluar (Kas)', (optional($x->category)->nama ?? '').' - '.($x->keterangan ?? '-'), $rupiah($x->jumlah)]);
                foreach (($data['rows3'] ?? []) as $x) fputcsv($o, [$x->tanggal, $x->nomor, 'Belanja Perusahaan', optional($x->supplier)->nama ?? '-', 'Di luar saldo kas', $rupiah($x->total)]);
            } elseif ($jenis === 'okupansi') {
                foreach (($data['rows'] ?? []) as $rm) fputcsv($o, [$rm->nomor, optional($rm->building)->nama ?? '-', optional($rm->roomType)->nama ?? '-', $rm->lantai, optional(optional($rm->activeTenancy)->tenant)->nama ?? '-', $rm->status]);
            } elseif ($jenis === 'inventaris') {
                foreach (($data['rows'] ?? []) as $x) fputcsv($o, [$x->kode_barcode, optional($x->item)->nama, optional(optional($x->activePlacement)->room)->nomor ?? 'Gudang', $x->status=='dibuang' ? 'Dibuang' : ($x->kondisi ?? 'baik'), $x->status, $x->harga_perolehan, $x->nilai_buku, $x->tanggal_beli]);
            } elseif ($jenis === 'pembelian') {
                foreach (($data['rows'] ?? []) as $p) fputcsv($o, [$p->tanggal, $p->nomor, optional($p->supplier)->nama ?? '-', $p->sumber_dana ?? 'kas', $p->items->map(fn($i)=>$i->qty.'x '.optional($i->item)->nama)->implode('; '), $p->total]);
            } elseif ($jenis === 'penyusutan') {
                foreach (($data['rows'] ?? []) as $x) {
                    $susut = $x->harga_perolehan - $x->nilai_buku;
                    $pc = $x->harga_perolehan ? min(100, round($susut/$x->harga_perolehan*100)) : 0;
                    fputcsv($o, [$x->kode_barcode, optional($x->item)->nama, $x->harga_perolehan, $x->nilai_residu, $susut, $x->nilai_buku, $pc.'%']);
                }
            } else { // penggantian
                foreach (($data['rows'] ?? []) as $u) {
                    $lewat = $u->kondisi !== 'perlu_diganti' && $u->tanggal_ganti_rencana && $u->tanggal_ganti_rencana <= now()->toDateString();
                    $dekat = !$lewat && $u->kondisi === 'baik' && $u->tanggal_ganti_rencana && $u->tanggal_ganti_rencana <= now()->addDays(90)->toDateString();
                    fputcsv($o, [$u->kode_barcode, optional($u->item)->nama, optional(optional($u->activePlacement)->room)->nomor ?? 'Gudang', $u->tanggal_ganti_rencana, $u->kondisi ?? 'baik', $u->kondisi=='perlu_diganti' ? 'Perlu Diganti' : ($lewat ? 'Terlambat' : ($dekat ? 'Segera' : 'Terjadwal'))]);
                }
            }
            fclose($o);
        }, $name);
    }
}
