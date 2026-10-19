<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class InventorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $items = [
            ['nama'=>'Kasur 120x200 + Dipan','kategori'=>'Furnitur','harga_estimasi'=>1500000,'umur_pakai_bulan'=>48],
            ['nama'=>'Lemari 2 Pintu','kategori'=>'Furnitur','harga_estimasi'=>1200000,'umur_pakai_bulan'=>60],
            ['nama'=>'Meja + Kursi Belajar','kategori'=>'Furnitur','harga_estimasi'=>650000,'umur_pakai_bulan'=>60],
            ['nama'=>'AC 0.5 PK','kategori'=>'Elektronik','harga_estimasi'=>3500000,'umur_pakai_bulan'=>60],
            ['nama'=>'Kipas Angin Dinding','kategori'=>'Elektronik','harga_estimasi'=>350000,'umur_pakai_bulan'=>36],
            ['nama'=>'Cermin Dinding','kategori'=>'Aksesori','harga_estimasi'=>150000,'umur_pakai_bulan'=>48],
            ['nama'=>'Gorden Blackout','kategori'=>'Aksesori','harga_estimasi'=>250000,'umur_pakai_bulan'=>24],
            ['nama'=>'Lampu LED + Stop Kontak','kategori'=>'Elektrikal','harga_estimasi'=>120000,'umur_pakai_bulan'=>36],
            ['nama'=>'Keset + Tempat Sampah','kategori'=>'Aksesori','harga_estimasi'=>100000,'umur_pakai_bulan'=>12],
            ['nama'=>'Ember + Gayung','kategori'=>'Aksesori','harga_estimasi'=>60000,'umur_pakai_bulan'=>12],
        ];
        foreach ($items as $it) {
            $category = \App\Models\Category::firstOrCreate(['nama' => $it['kategori']]);
            \App\Models\Item::firstOrCreate(['nama' => $it['nama']], array_merge($it, ['kategori_id' => $category->id]));
        }
        $allItems = \App\Models\Item::all();
        $sup = \App\Models\Supplier::first();
        $purchase = \App\Models\Purchase::firstOrCreate(['nomor'=>'PO-2026-0001'], [
            'supplier_id'=>$sup->id,'tanggal'=>now()->subMonths(14),'total'=>0,'status'=>'selesai',
        ]);
        if ($purchase->items()->count() === 0) {
            $total = 0; $thn = now()->format('Y'); $seqPerKata = [];
            foreach ($allItems as $it) {
                $qty = 26; $sub = $qty * $it->harga_estimasi; $total += $sub;
                \App\Models\PurchaseItem::create(['purchase_id'=>$purchase->id,'item_id'=>$it->id,
                    'qty'=>$qty,'harga_satuan'=>$it->harga_estimasi,'subtotal'=>$sub]);
                $kata = \App\Models\AssetUnit::kataBarcode($it->nama);
                if (!isset($seqPerKata[$kata])) {
                    $prefix = 'CND-'.$kata.'-'.$thn.'-';
                    $last = \App\Models\AssetUnit::where('kode_barcode','like',$prefix.'%')->orderByDesc('kode_barcode')->value('kode_barcode');
                    $seqPerKata[$kata] = $last ? ((int) substr($last, -4)) + 1 : 1;
                }
                for ($k=0;$k<$qty;$k++) {
                    $kode = sprintf('CND-%s-%s-%04d', $kata, $thn, $seqPerKata[$kata]++);
                    $tglBeli = now()->subMonths(rand(2,16))->toDateString();
                    $ganti = \Carbon\Carbon::parse($tglBeli)->addMonths($it->umur_pakai_bulan)->toDateString();
                    \App\Models\AssetUnit::create(['kode_barcode'=>$kode,'item_id'=>$it->id,
                        'purchase_id'=>$purchase->id,'tanggal_beli'=>$tglBeli,
                        'harga_perolehan'=>$it->harga_estimasi,'nilai_buku'=>$it->harga_estimasi,
                        'kondisi'=>'baik','status'=>'gudang','tanggal_ganti_rencana'=>$ganti]);
                }
            }
            $purchase->update(['total'=>$total]);
        }
        foreach (\App\Models\Room::all() as $room) {
            if ($room->activeAssets()->count() > 0) continue;
            $needAC = in_array(optional($room->roomType)->nama, ['Deluxe','Exclusive']);
            foreach ($allItems as $it) {
                if (str_contains($it->nama,'AC') && !$needAC) continue;
                if (str_contains($it->nama,'Kipas') && $needAC) continue;
                $unit = \App\Models\AssetUnit::where('item_id',$it->id)->where('status','gudang')->first();
                if (!$unit) continue;
                $unit->update(['status'=>'terpasang']);
                \App\Models\RoomAsset::create(['room_id'=>$room->id,'asset_unit_id'=>$unit->id,
                    'tanggal_pasang'=>now()->subMonths(6)->toDateString(),'status'=>'aktif']);
            }
        }
    }
}
