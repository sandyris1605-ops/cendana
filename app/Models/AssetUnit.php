<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetUnit extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $casts = ['tanggal_beli' => 'date', 'tanggal_ganti_rencana' => 'date'];
    public function item() { return $this->belongsTo(Item::class); }
    public function purchase() { return $this->belongsTo(Purchase::class); }
    public function placements() { return $this->hasMany(RoomAsset::class); }
    public function activePlacement() { return $this->hasOne(RoomAsset::class)->where('status','aktif'); }
    public function depreciations() { return $this->hasMany(Depreciation::class); }

    /**
     * Kata pertama nama barang untuk barcode — bersih, huruf awal kapital.
     * Contoh: "kasur link" → "Kasur"; "TV LED 32" → "TV"; "  ***  " → "BRG".
     */
    public static function kataBarcode(string $nama): string
    {
        $kata = trim((string) strtok(trim($nama), ' '));
        $kata = preg_replace('/[^0-9A-Za-z]/', '', $kata) ?? '';
        if ($kata === '') return 'BRG';
        if (preg_match('/^[a-z]/', $kata)) $kata = ucfirst($kata);
        return $kata;
    }
}
