<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OccupancySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $names = ['Rina','Budi','Sinta','Agus','Putri','Dimas','Nadia'];
        $i = 0;
        foreach (\App\Models\Room::where('status','terisi')->get() as $room) {
            if ($room->tenancies()->where('status','aktif')->exists()) continue;
            $tenant = \App\Models\Tenant::create(['nama'=>($names[$i%count($names)].' '.$room->nomor),
                'telepon'=>'0812'.rand(10000000,99999999),'pekerjaan'=>($i%2?'Mahasiswa':'Karyawan')]);
            $ten = \App\Models\Tenancy::create(['tenant_id'=>$tenant->id,'room_id'=>$room->id,
                'tanggal_masuk'=>now()->subMonths(rand(1,8))->toDateString(),
                'status'=>'aktif']);
            $i++;
        }
        $kat = \App\Models\ExpenseCategory::first();
        for ($m=0;$m<3;$m++) {
            \App\Models\Expense::firstOrCreate(['nomor'=>sprintf('EXP-%s-0%d', now()->subMonths($m)->format('Ym'), 1)], [
                'expense_category_id'=>$kat->id,'tanggal'=>now()->subMonths($m)->toDateString(),
                'jumlah'=>1500000,'keterangan'=>'Listrik bulan berjalan']);
        }
    }
}
