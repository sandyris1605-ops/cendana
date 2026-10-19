<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AssetUnit;
use App\Models\Booking;
use App\Models\Expense;
use App\Models\Room;
use App\Models\TopUp;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index() {
        $totalKamar = Room::count();
        $terisi = Room::where('status','terisi')->count();
        $tersedia = Room::where('status','tersedia')->count();
        $okupansi = $totalKamar ? round($terisi/$totalKamar*100) : 0;
        $kasBulan = TopUp::whereMonth('tanggal', now()->month)->whereYear('tanggal', now()->year)->sum('jumlah');
        $keluarBulan = Expense::whereMonth('tanggal', now()->month)->whereYear('tanggal', now()->year)->sum('jumlah');
        $nilaiAset = AssetUnit::sum('nilai_buku');
        $ganti = AssetUnit::with('item')->where(function ($q) {
                $q->where('kondisi','perlu_diganti')
                  ->orWhere(fn ($qq) => $qq->whereNotNull('tanggal_ganti_rencana')->whereDate('tanggal_ganti_rencana','<=', now()->addDays(90)));
            })->orderByRaw("CASE WHEN kondisi='perlu_diganti' THEN 0 ELSE 1 END")->orderBy('tanggal_ganti_rencana')->limit(10)->get();
        $jmlPerluDiganti = AssetUnit::where('kondisi','perlu_diganti')->count();
        $bookBaru = Booking::where('status','baru')->orderByDesc('id')->limit(6)->get();

        // Data grafik: saldo kas berjalan 6 bulan terakhir (TopUp - Expense, sama dgn halaman Saldo Kas)
        $bulanLabel = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Ags','Sep','Okt','Nov','Des'];
        $label6 = $saldo6 = [];
        $mulai = now()->subMonths(5)->startOfMonth();
        // Saldo di awal jendela = seluruh riwayat sebelum bulan pertama, agar titik awal akurat
        $saldo = (float) TopUp::where('tanggal', '<', $mulai)->sum('jumlah')
               - (float) Expense::where('tanggal', '<', $mulai)->sum('jumlah');
        $masukPerBulan = TopUp::where('tanggal', '>=', $mulai)->get(['tanggal', 'jumlah'])
            ->groupBy(fn ($t) => $t->tanggal->format('Y-m'))
            ->map(fn ($g) => $g->sum('jumlah'));
        $keluarPerBulan = Expense::where('tanggal', '>=', $mulai)->get(['tanggal', 'jumlah'])
            ->groupBy(fn ($e) => $e->tanggal->format('Y-m'))
            ->map(fn ($g) => $g->sum('jumlah'));

        for ($i = 5; $i >= 0; $i--) {
            $d = now()->subMonths($i);
            $label6[] = $bulanLabel[$d->month - 1].' '.$d->format('y');
            $saldo += (float) ($masukPerBulan[$d->format('Y-m')] ?? 0)
                   - (float) ($keluarPerBulan[$d->format('Y-m')] ?? 0);
            $saldo6[] = (int) round($saldo);
        }

        $keluarBaru = Expense::with('category')->orderByDesc('tanggal')->orderByDesc('id')->limit(8)->get();

        return view('admin.dashboard', compact(
            'totalKamar','terisi','tersedia','okupansi','kasBulan','keluarBulan','nilaiAset',
            'ganti','jmlPerluDiganti','bookBaru','keluarBaru',
            'label6','saldo6'
        ));
    }
}
