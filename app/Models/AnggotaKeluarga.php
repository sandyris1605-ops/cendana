<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnggotaKeluarga extends Model
{
    use HasFactory;
    protected $table = 'anggota_keluarga';
    protected $guarded = [];

    public function tenant() { return $this->belongsTo(Tenant::class); }
}
