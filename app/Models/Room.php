<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;
    protected $guarded = [];
    public function building() { return $this->belongsTo(Building::class); }
    public function type() { return $this->belongsTo(RoomType::class, 'room_type_id'); }
    public function roomType() { return $this->belongsTo(RoomType::class); }
    public function assets() { return $this->hasMany(RoomAsset::class); }
    public function activeAssets() { return $this->hasMany(RoomAsset::class)->where('status','aktif'); }
    public function tenancies() { return $this->hasMany(Tenancy::class); }
    public function activeTenancy() { return $this->hasOne(Tenancy::class)->where('status','aktif'); }
    public function maintenances() { return $this->hasMany(Maintenance::class); }
}
