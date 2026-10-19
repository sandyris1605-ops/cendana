<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $casts = ['tanggal_rencana' => 'date'];
    public function roomType() { return $this->belongsTo(RoomType::class); }
}
