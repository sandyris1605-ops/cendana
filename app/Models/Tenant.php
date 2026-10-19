<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    use HasFactory;
    protected $guarded = [];
    public function tenancies() { return $this->hasMany(Tenancy::class); }
    public function activeTenancy() { return $this->hasOne(Tenancy::class)->where('status','aktif'); }
    public function anggota() { return $this->hasMany(AnggotaKeluarga::class); }
}
