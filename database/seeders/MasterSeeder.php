<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MasterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\User::updateOrCreate(['email' => 'admin@cendana.test'], [
            'name' => 'Admin Cendana',
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
            'role' => 'admin', 'telepon' => '081234567890',
        ]);
        \App\Models\Setting::updateOrCreate(['key'=>'nama'],['value'=>'Cendana Resident']);
        \App\Models\Setting::updateOrCreate(['key'=>'alamat'],['value'=>'Jl. Cendana No. 88, Lowokwaru, Malang 65141']);
        \App\Models\Setting::updateOrCreate(['key'=>'telepon'],['value'=>'0812-3456-7890']);
        \App\Models\Setting::updateOrCreate(['key'=>'wa'],['value'=>'6281234567890']);
        \App\Models\Setting::updateOrCreate(['key'=>'email'],['value'=>'halo@cendana.test']);
        $gedung = \App\Models\Building::firstOrCreate(['nama' => 'Gedung Utama'],
            ['alamat' => 'Jl. Cendana No. 88 Malang', 'keterangan' => '3 lantai, 24 kamar']);
        $types = [
            ['nama' => 'Standar', 'ukuran' => '3x3 m'],
            ['nama' => 'Deluxe', 'ukuran' => '3.5x4 m'],
            ['nama' => 'Exclusive', 'ukuran' => '4x5 m'],
        ];
        foreach ($types as $t) { \App\Models\RoomType::firstOrCreate(['nama'=>$t['nama']], $t); }
        $standar = \App\Models\RoomType::where('nama','Standar')->first();
        $deluxe = \App\Models\RoomType::where('nama','Deluxe')->first();
        $excl = \App\Models\RoomType::where('nama','Exclusive')->first();
        $defs = [];
        for ($i=1;$i<=12;$i++) $defs[] = ['type'=>$standar,'lantai'=>'1','nomor'=>sprintf('A-%02d',$i)];
        for ($i=1;$i<=8;$i++) $defs[] = ['type'=>$deluxe,'lantai'=>'2','nomor'=>sprintf('B-%02d',$i)];
        for ($i=1;$i<=4;$i++) $defs[] = ['type'=>$excl,'lantai'=>'3','nomor'=>sprintf('C-%02d',$i)];
        foreach ($defs as $idx => $d) {
            \App\Models\Room::firstOrCreate(['nomor'=>$d['nomor']], [
                'building_id'=>$gedung->id,'room_type_id'=>$d['type']->id,
                'lantai'=>$d['lantai'],'status'=>($idx%10<7)?'terisi':'tersedia',
            ]);
        }
        $sups = [
            ['nama'=>'Sinar Jaya Furniture','kontak'=>'Bpk. Harto','telepon'=>'081233344455'],
            ['nama'=>'Electro Malang','kontak'=>'Ibu Sari','telepon'=>'081255566677'],
            ['nama'=>'Berkah Mebel','kontak'=>'Bpk. Slamet','telepon'=>'081277788899'],
        ];
        foreach ($sups as $s) { \App\Models\Supplier::firstOrCreate(['nama'=>$s['nama']], $s); }
        foreach (['Listrik & Air','Gaji','Perawatan','Kebersihan','ATK & Operasional','Lainnya'] as $c) {
            \App\Models\ExpenseCategory::firstOrCreate(['nama'=>$c]);
        }
        $emps = [
            ['nama'=>'Dewi Lestari','jabatan'=>'Manager','gaji'=>5000000],
            ['nama'=>'Andi Pratama','jabatan'=>'Admin','gaji'=>3500000],
            ['nama'=>'Sutrisno','jabatan'=>'OB / Cleaning','gaji'=>2500000],
            ['nama'=>'Bambang','jabatan'=>'Teknisi','gaji'=>3000000],
        ];
        foreach ($emps as $e) {
            \App\Models\Employee::firstOrCreate(['nama'=>$e['nama']], $e + ['status'=>'aktif']);
        }
    }
}
