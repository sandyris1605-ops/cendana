<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Depreciation extends Model
{
    use HasFactory;
    protected $guarded = [];
    public function unit() { return $this->belongsTo(AssetUnit::class, 'asset_unit_id'); }
}
