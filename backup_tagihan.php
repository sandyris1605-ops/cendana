<?php

use App\Models\RentPayment;
use App\Models\RoomType;
use App\Models\Tenancy;

require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

@mkdir(__DIR__.'/storage/backups', 0777, true);

$rows = Tenancy::orderBy('id')->get();
$csv = "id,tenant_id,room_id,tanggal_masuk,tanggal_keluar,harga_sewa,deposit,status\n";
foreach ($rows as $x) {
    $csv .= implode(',', [$x->id, $x->tenant_id, $x->room_id, $x->tanggal_masuk?->toDateString(), $x->tanggal_keluar?->toDateString() ?? '', $x->harga_sewa, $x->deposit, $x->status])."\n";
}
file_put_contents(__DIR__.'/storage/backups/tenancies_harga_backup.csv', $csv);

$csv2 = "nomor,tenancy_id,periode,tanggal,jumlah,status,metode\n";
foreach (RentPayment::orderBy('id')->get() as $x) {
    $csv2 .= implode(',', [$x->nomor, $x->tenancy_id, $x->periode, $x->tanggal->toDateString(), $x->jumlah, $x->status, $x->metode ?? ''])."\n";
}
file_put_contents(__DIR__.'/storage/backups/rent_payments_backup.csv', $csv2);

$csv3 = "id,nama,harga_bulanan\n";
foreach (RoomType::orderBy('id')->get() as $t) {
    $csv3 .= $t->id.',"'.$t->nama.'",'.$t->harga_bulanan."\n";
}
file_put_contents(__DIR__.'/storage/backups/room_types_harga_backup.csv', $csv3);

echo 'tenancies='.Tenancy::count().' rent='.RentPayment::count().' types='.RoomType::count().PHP_EOL;
