<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomAsset extends Model
{
    use HasFactory;
    protected $guarded = [];
    public function room() { return $this->belongsTo(Room::class); }
    public function unit() { return $this->belongsTo(AssetUnit::class, 'asset_unit_id'); }
    public function assetUnit() { return $this->belongsTo(AssetUnit::class); }
}
