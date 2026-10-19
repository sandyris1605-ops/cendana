<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama',
        'kategori_id',
        'merek',
        'satuan',
        'harga_estimasi',
        'umur_pakai_bulan',
        'stok_minimum',
        'deskripsi',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class, 'kategori_id');
    }

    public function units()
    {
        return $this->hasMany(AssetUnit::class);
    }

    public function purchaseItems()
    {
        return $this->hasMany(PurchaseItem::class);
    }
}
