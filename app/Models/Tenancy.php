<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tenancy extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $casts = ['tanggal_masuk' => 'date', 'tanggal_keluar' => 'date'];
    public function tenant() { return $this->belongsTo(Tenant::class); }
    public function room() { return $this->belongsTo(Room::class); }
}
