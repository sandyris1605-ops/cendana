<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomType extends Model
{
    use HasFactory;
    protected $guarded = [];
    public function rooms() { return $this->hasMany(Room::class); }
    public function bookings() { return $this->hasMany(Booking::class); }
}
